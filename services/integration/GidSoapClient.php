<?php
declare(strict_types=1);

namespace app\services\integration;

use yii\base\Component;

final class GidSoapClient extends Component implements GidClientInterface
{
    public array $configuration = [];
    public array $company = [];

    private ?GidCertificate $certificate = null;

    public function fetchInventory(string $correlationId): iterable
    {
        $seen = [];
        foreach ($this->listPartNames() as $partName) {
            $cursor = 0;
            do {
                $items = $this->searchInventory($partName, $cursor);
                foreach ($items as $item) {
                    $id = (string) ($item['gid_id'] ?? '');
                    if ($id !== '' && !isset($seen[$id])) {
                        $seen[$id] = true;
                        yield $item;
                    }
                }
                $nextCursor = $items === [] ? $cursor : (int) end($items)['gid_id'];
                if ($nextCursor <= $cursor) {
                    break;
                }
                $cursor = $nextCursor;
            } while (count($items) === 50);
        }
    }

    /** @return list<string> */
    public function listPartNames(): array
    {
        $response = $this->call('listarPecaGid', 'LISTAR_PECA', [
            'infListarPeca' => ['CDV' => $this->header()],
        ]);
        $names = [];
        foreach ($this->xpath($response, '//*[local-name()="NOME_PECA"]') as $node) {
            $name = trim($node->textContent);
            if ($name !== '') {
                $names[$name] = true;
            }
        }
        return array_keys($names);
    }

    /** @return list<array<string,mixed>> */
    public function searchInventory(string $partName, int $cursor = 0): array
    {
        $partName = trim($partName);
        if (mb_strlen($partName) < 3) {
            throw new \InvalidArgumentException('A pesquisa GID exige um nome de peca com pelo menos 3 caracteres.');
        }
        $response = $this->call('pesquisarEstoqueGid', 'PESQUISAR_ESTOQUE', [
            'infPesquisarEstoque' => [
                'CDV' => $this->header(),
                'NOME_PECA' => $partName,
                'NOME_MARCA' => '',
                'NOME_MODELO' => '',
                'ANO_MODELO' => '',
                'ULTIMO_ITEM_PESQUISADO' => max(0, $cursor),
                'PLACA_VEICULO' => '',
                'COD_NOTA' => '',
                'CHASSI_VEICULO' => '',
                'PESQUISA_EXATA_PECA' => 'S',
            ],
        ]);

        $items = [];
        foreach ($this->xpath($response, '//*[local-name()="ESTOQUE"]') as $stock) {
            $value = static function (\DOMElement $parent, string $name): string {
                foreach ($parent->childNodes as $child) {
                    if ($child instanceof \DOMElement && $child->localName === $name) {
                        return trim($child->textContent);
                    }
                }
                return '';
            };
            $gidId = $value($stock, 'COD_ESTOQUE');
            if ($gidId === '') {
                continue;
            }
            $items[] = [
                'gid_id' => $gidId,
                'codigo' => $gidId,
                'descricao' => $value($stock, 'NOME_PECA'),
                'nome_originario' => $value($stock, 'NOME_ORIGINARIO'),
                'marca' => $value($stock, 'NOME_MARCA'),
                'modelo' => $value($stock, 'NOME_MODELO'),
                'ano_modelo' => $value($stock, 'ANO_MODELO'),
                'tipo_veiculo' => $value($stock, 'TXT_TIPO_VEICULO'),
                'placa_veiculo' => $value($stock, 'PLACA_VEICULO'),
                'chassi_veiculo' => $value($stock, 'CHASSI_VEICULO'),
                'grupo_gid' => $value($stock, 'NRO_GRUPO'),
                'situacao_peca' => $value($stock, 'SITUACAO_PECA'),
                'peca_acoplada' => $value($stock, 'PECA_ACOPLADA'),
                'codigo_nota' => $value($stock, 'COD_NOTA'),
                'item_controlado' => $value($stock, 'ITEM_CONTROLADO'),
                'observacao' => $value($stock, 'OBSERVACAO'),
                'quantidade_disponivel' => 1,
            ];
        }
        usort($items, static fn(array $a, array $b): int => (int) $a['gid_id'] <=> (int) $b['gid_id']);
        return $items;
    }

    public function certificate(): GidCertificate
    {
        return $this->certificate ??= GidCertificate::fromPkcs12(
            (string) ($this->configuration['certificatePath'] ?? ''),
            (string) ($this->configuration['certificatePassword'] ?? ''),
            (string) ($this->company['cnpj'] ?? '')
        );
    }

    public function assertWsdlAvailable(): void
    {
        $client = new \SoapClient((string) $this->configuration['wsdlUrl'], [
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => (int) ($this->configuration['connectionTimeout'] ?? 30),
            'exceptions' => true,
        ]);
        $functions = implode("\n", $client->__getFunctions() ?: []);
        foreach (['listarPecaGid', 'pesquisarEstoqueGid'] as $operation) {
            if (!str_contains($functions, $operation)) {
                throw new \RuntimeException("Operacao obrigatoria ausente no WSDL do GID: {$operation}");
            }
        }
    }

    /** @param array<string,mixed> $payload */
    private function call(string $operation, string $rootName, array $payload): \DOMDocument
    {
        $id = (string) ($this->company['cdvCode'] ?? '');
        if (!preg_match('/^CDV\d{5}$/', $id)) {
            throw new \RuntimeException('Codigo CDV invalido.');
        }
        $payload['Signature'] = $this->signaturePlaceholder();
        $payload['Id'] = $id;

        $capture = new GidSoapRequestCapture((string) $this->configuration['wsdlUrl'], [
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => (int) ($this->configuration['connectionTimeout'] ?? 30),
            'exceptions' => true,
        ]);
        try {
            $capture->__soapCall($operation, [[$rootName => $payload]]);
        } catch (\SoapFault) {
            // The transport is intentionally intercepted; only the serialized request is used.
        }
        if ($capture->capturedRequest === '') {
            throw new \RuntimeException('Nao foi possivel serializar a requisicao SOAP do GID.');
        }

        $signedRequest = (new GidXmlSigner())->signSoapRequest($capture->capturedRequest, $this->certificate());
        $handle = curl_init((string) $this->configuration['serviceUrl']);
        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $signedRequest,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => (int) ($this->configuration['connectionTimeout'] ?? 30),
            CURLOPT_TIMEOUT => max(30, (int) ($this->configuration['connectionTimeout'] ?? 30) * 2),
            CURLOPT_SSL_VERIFYPEER => (bool) ($this->configuration['verifyPeer'] ?? true),
            CURLOPT_SSL_VERIFYHOST => (bool) ($this->configuration['verifyPeer'] ?? true) ? 2 : 0,
            CURLOPT_HTTPHEADER => ['Content-Type: text/xml; charset=UTF-8', 'SOAPAction: "' . $capture->capturedAction . '"'],
        ]);
        $response = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);
        if (!is_string($response) || $response === '' || $status < 200 || $status >= 300) {
            throw new \RuntimeException("Falha HTTP no GID ({$status}): " . ($error !== '' ? $error : 'resposta vazia ou invalida.'));
        }

        $document = new \DOMDocument();
        if (!$document->loadXML($response, LIBXML_NONET | LIBXML_NOBLANKS)) {
            throw new \RuntimeException('O GID retornou uma resposta que nao e XML valido.');
        }
        $faults = $this->xpath($document, '//*[local-name()="Fault"]');
        if ($faults !== []) {
            throw new \RuntimeException('SOAP Fault do GID: ' . trim($faults[0]->textContent));
        }
        $statusNodes = $this->xpath($document, '//*[local-name()="STATUS"]/*[local-name()="COD_STATUS"]');
        if ($statusNodes !== [] && trim($statusNodes[0]->textContent) !== '1') {
            $errors = $this->xpath($document, '//*[local-name()="ERROS"]');
            throw new \RuntimeException('Regra de negocio do GID: ' . ($errors !== [] ? trim($errors[0]->textContent) : 'status diferente de sucesso.'));
        }
        return $document;
    }

    /** @return array<string,mixed> */
    private function header(): array
    {
        $header = [
            'COD_CREDENCIADO' => (string) $this->company['cdvCode'],
            'SENHA_CREDENCIADO' => '',
            'CNPJ_CREDENCIADO' => (string) $this->company['cnpj'],
            'COD_AMBIENTE' => (int) $this->configuration['environmentCode'],
            'VERSAO_LEIAUTE' => (string) $this->configuration['layoutVersion'],
            'NOME_EMISSOR_NFE' => (string) $this->configuration['issuerName'],
            'CNPJ_EMISSOR_NFE' => (string) $this->configuration['issuerCnpj'],
        ];
        if (($this->configuration['operatorRegistration'] ?? '') !== '') {
            $header['MATR_OPER'] = (int) $this->configuration['operatorRegistration'];
        }
        if (($this->configuration['homologationOperation'] ?? '') !== '') {
            $header['OPER_HOMOLOGACAO'] = (int) $this->configuration['homologationOperation'];
        }
        return $header;
    }

    /** @return array<string,mixed> */
    private function signaturePlaceholder(): array
    {
        return [
            'SignedInfo' => [
                'CanonicalizationMethod' => ['Algorithm' => 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315'],
                'SignatureMethod' => ['Algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1'],
                'Reference' => [
                    'Transforms' => ['Transform' => [
                        ['Algorithm' => 'http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
                        ['Algorithm' => 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315'],
                    ]],
                    'DigestMethod' => ['Algorithm' => 'http://www.w3.org/2000/09/xmldsig#sha1'],
                    'DigestValue' => 'AA==',
                    'URI' => '#' . (string) $this->company['cdvCode'],
                ],
            ],
            'SignatureValue' => 'AA==',
            'KeyInfo' => ['X509Data' => ['X509Certificate' => 'AA==']],
        ];
    }

    /** @return list<\DOMElement> */
    private function xpath(\DOMDocument $document, string $expression): array
    {
        $nodes = (new \DOMXPath($document))->query($expression);
        if ($nodes === false) {
            return [];
        }
        return array_values(array_filter(iterator_to_array($nodes), static fn($node): bool => $node instanceof \DOMElement));
    }
}
