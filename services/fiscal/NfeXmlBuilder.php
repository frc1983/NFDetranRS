<?php
declare(strict_types=1);
namespace app\services\fiscal;

use NFePHP\Common\Signer;
use NFePHP\Common\Validator;
use NFePHP\Common\Keys;
use NFePHP\NFe\Make;

final class NfeXmlBuilder
{
    public function __construct(private array $configuration)
    {
    }

    /** @return array{xml:string,accessKey:string,hash:string} */
    public function build(array $invoice): array
    {
        (new NfeConfigurationValidator())->assertValid($this->configuration, false);
        (new NfeDraftValidator())->assertValid($invoice);

        $schema = (string) $this->configuration['schemaPackage'];
        $make = new Make($schema);
        $make->setOnlyAscii(false);
        $make->setCheckGtin(true);
        $make->taginfNFe((object) ['Id' => null, 'versao' => '4.00']);

        $issuedAt = new \DateTimeImmutable((string) $invoice['issuedAt']);
        $numericCode = str_pad((string) ((int) preg_replace('/\D+/', '', (string) $invoice['numericCode'])), 8, '0', STR_PAD_LEFT);
        while (!Keys::cNFIsValid($numericCode) || (int) $numericCode === (int) $invoice['number']) {
            $numericCode = str_pad((string) (((int) $numericCode + 104729) % 100000000), 8, '0', STR_PAD_LEFT);
        }
        $make->tagide((object) [
            'cUF' => (int) $invoice['stateCode'], 'cNF' => $numericCode,
            'natOp' => (string) ($invoice['natureOperation'] ?? 'VENDA DE PECA USADA'),
            'mod' => 55, 'serie' => (int) $invoice['series'], 'nNF' => (int) $invoice['number'],
            'dhEmi' => $issuedAt->format(DATE_ATOM), 'dhSaiEnt' => $issuedAt->format(DATE_ATOM),
            'tpNF' => 1, 'idDest' => (int) ($invoice['destinationType'] ?? 1),
            'cMunFG' => (int) $invoice['issuer']['cityCode'], 'cMunFGIBS' => null,
            'tpImp' => 1, 'tpEmis' => 1, 'cDV' => null,
            'tpAmb' => (int) $this->configuration['environmentCode'], 'finNFe' => 1,
            'indFinal' => (int) ($invoice['finalConsumer'] ?? 1),
            'indPres' => (int) ($invoice['presenceIndicator'] ?? 1),
            'indIntermed' => 0, 'procEmi' => 0, 'verProc' => 'NFDetranRS 0.1',
        ]);

        $issuer = $invoice['issuer'];
        $make->tagEmit((object) [
            'xNome' => $issuer['legalName'], 'xFant' => $issuer['tradeName'] ?? null,
            'IE' => $issuer['stateRegistration'], 'CRT' => (int) $issuer['taxRegime'],
            'CNPJ' => $issuer['cnpj'],
        ]);
        $make->tagenderEmit((object) [
            'xLgr' => $issuer['street'], 'nro' => $issuer['number'], 'xCpl' => $issuer['complement'] ?? null,
            'xBairro' => $issuer['district'], 'cMun' => $issuer['cityCode'], 'xMun' => $issuer['city'],
            'UF' => $issuer['state'] ?? 'RS', 'CEP' => $issuer['postalCode'],
            'cPais' => 1058, 'xPais' => 'BRASIL', 'fone' => $issuer['phone'] ?? null,
        ]);

        $recipient = $invoice['recipient'];
        $document = preg_replace('/\D+/', '', (string) $recipient['document']);
        $make->tagdest((object) [
            'xNome' => $recipient['name'],
            'CNPJ' => strlen($document) === 14 ? $document : null,
            'CPF' => strlen($document) === 11 ? $document : null,
            'indIEDest' => !empty($recipient['stateRegistration']) ? 1 : 9,
            'IE' => $recipient['stateRegistration'] ?? null,
            'email' => $recipient['email'] ?? null,
        ]);
        if (!empty($recipient['street'])) {
            $make->tagenderDest((object) [
                'xLgr' => $recipient['street'], 'nro' => $recipient['number'] ?? 'S/N',
                'xCpl' => $recipient['complement'] ?? null, 'xBairro' => $recipient['district'],
                'cMun' => $recipient['cityCode'], 'xMun' => $recipient['city'], 'UF' => $recipient['state'],
                'CEP' => $recipient['postalCode'] ?? null, 'cPais' => 1058, 'xPais' => 'BRASIL',
                'fone' => $recipient['phone'] ?? null,
            ]);
        }

        foreach ($invoice['items'] as $offset => $item) {
            $number = $offset + 1;
            $make->tagprod((object) [
                'item' => $number, 'cProd' => $item['code'], 'cEAN' => 'SEM GTIN',
                'xProd' => $item['description'], 'NCM' => $item['ncm'], 'CEST' => $item['cest'] ?? null,
                'cBenef' => $item['taxBenefitCode'] ?? null, 'CFOP' => $item['cfop'],
                'uCom' => $item['unit'], 'qCom' => $item['quantity'], 'vUnCom' => $item['unitPrice'],
                'vProd' => $item['total'], 'cEANTrib' => 'SEM GTIN', 'uTrib' => $item['unit'],
                'qTrib' => $item['quantity'], 'vUnTrib' => $item['unitPrice'], 'indTot' => 1,
            ]);
            $additional = trim('PECA USADA CDV; GID: ' . ($item['gidCode'] ?? $item['code']) . '; ' . ($item['traceability'] ?? ''));
            $make->taginfAdProd((object) ['item' => $number, 'infAdProd' => mb_substr($additional, 0, 500)]);
            $make->tagimposto((object) ['item' => $number, 'vTotTrib' => $item['estimatedTaxes'] ?? null]);

            if (isset($item['csosn'])) {
                $make->tagICMSSN((object) [
                    'item' => $number, 'orig' => $item['origin'], 'CSOSN' => $item['csosn'],
                    'modBC' => $item['icmsBaseMode'] ?? null, 'vBC' => $item['icmsBase'] ?? null,
                    'pICMS' => $item['icmsRate'] ?? null, 'vICMS' => $item['icmsAmount'] ?? null,
                ]);
            } else {
                $make->tagICMS((object) [
                    'item' => $number, 'orig' => $item['origin'], 'CST' => $item['icmsCst'],
                    'modBC' => $item['icmsBaseMode'] ?? 3, 'vBC' => $item['icmsBase'] ?? 0,
                    'pICMS' => $item['icmsRate'] ?? 0, 'vICMS' => $item['icmsAmount'] ?? 0,
                ]);
            }
            $make->tagPIS((object) [
                'item' => $number, 'CST' => $item['pisCst'], 'vBC' => $item['pisBase'] ?? 0,
                'pPIS' => $item['pisRate'] ?? 0, 'vPIS' => $item['pisAmount'] ?? 0,
            ]);
            $make->tagCOFINS((object) [
                'item' => $number, 'CST' => $item['cofinsCst'], 'vBC' => $item['cofinsBase'] ?? 0,
                'pCOFINS' => $item['cofinsRate'] ?? 0, 'vCOFINS' => $item['cofinsAmount'] ?? 0,
            ]);
        }

        $make->tagtransp((object) ['modFrete' => 9]);
        $make->tagpag((object) ['vTroco' => null]);
        $make->tagdetPag((object) ['indPag' => 0, 'tPag' => '90', 'vPag' => 0]);
        $make->taginfadic((object) [
            'infAdFisco' => null,
            'infCpl' => 'DOCUMENTO GERADO PELO SIMULADOR LOCAL NFDETRANRS - SEM VALOR FISCAL - NAO TRANSMITIDO A SEFAZ.',
        ]);

        $xml = $make->render();
        $errors = $make->getErrors();
        if ($xml === '' || $errors !== []) {
            throw new \RuntimeException('Falha ao montar XML NF-e: ' . implode(' ', $errors));
        }
        $xml = Signer::sign(NfeMockCertificate::create((string) $issuer['cnpj']), $xml, 'infNFe', 'Id');
        $xsd = dirname(__DIR__, 2) . '/vendor/nfephp-org/sped-nfe/schemes/' . $schema . '/nfe_v4.00.xsd';
        if (!is_file($xsd)) {
            throw new \RuntimeException("Schema NF-e nao encontrado: {$schema}.");
        }
        Validator::isValid($xml, $xsd);
        return ['xml' => $xml, 'accessKey' => $make->getChave(), 'hash' => hash('sha256', $xml)];
    }
}
