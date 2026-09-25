<?php
declare(strict_types=1);
namespace app\services\fiscal;

use app\models\AuditEvent;
use app\models\IntegrationLog;
use app\models\Invoice;
use app\models\InvoiceItem;
use app\models\Sale;
use app\models\SaleItem;
use Yii;

final class NfeSimulationService
{
    public function __construct(private array $configuration, private string $storagePath)
    {
    }

    /** @return Invoice[] */
    public function simulate(Sale $sale, int $userId): array
    {
        if ($this->configuration['environment'] !== 'homologation' || $this->configuration['transport'] !== 'mock') {
            throw new \RuntimeException('A geracao local exige NFE_ENVIRONMENT=homologation e NFE_TRANSPORT=mock.');
        }
        (new NfeConfigurationValidator())->assertValid($this->configuration, false);

        $existing = Invoice::find()->where(['venda_id' => $sale->id, 'simulada' => 1])->orderBy(['lote' => SORT_ASC])->all();
        if ($existing !== []) {
            return $existing;
        }
        $saleItems = SaleItem::find()->with('stockItem')->where(['venda_id' => $sale->id])->orderBy(['id' => SORT_ASC])->all();
        if ($saleItems === []) {
            throw new \RuntimeException('A venda nao possui itens para emissao simulada.');
        }
        $recipient = $this->recipientFromSale($sale);
        foreach (['street', 'number', 'district', 'cityCode', 'city', 'state', 'postalCode'] as $field) {
            if (empty($recipient[$field])) {
                throw new \RuntimeException("Destinatario da venda sem {$field}; crie uma nova venda apos completar a configuracao fiscal.");
            }
        }

        $transaction = Yii::$app->db->beginTransaction();
        $createdFiles = [];
        try {
            $documents = [];
            foreach (array_chunk($saleItems, 100) as $batchOffset => $batch) {
                $batchNumber = $batchOffset + 1;
                $number = $this->nextNumber();
                $issuedAt = new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
                $idempotencyKey = "nfe-sim-sale-{$sale->id}-batch-{$batchNumber}";
                $payload = $this->payload($batch, $recipient, $number, $issuedAt, $idempotencyKey);
                $built = (new NfeXmlBuilder($this->configuration))->build($payload);
                $paths = (new NfeArtifactStorage($this->storagePath))->store($built['xml'], $built['accessKey'], $issuedAt);
                $createdFiles[] = $paths['xml'];
                $createdFiles[] = $paths['pdf'];

                $invoice = new Invoice([
                    'venda_id' => $sale->id, 'lote' => $batchNumber,
                    'ambiente' => 'homologation', 'modelo' => 55,
                    'serie' => (int) $this->configuration['series'], 'numero' => $number,
                    'status' => 'autorizada', 'simulada' => 1,
                    'chave_acesso' => $built['accessKey'],
                    'protocolo' => 'SIM' . substr(hash('sha256', $idempotencyKey), 0, 12),
                    'xml_caminho' => $paths['xml'], 'danfe_caminho' => $paths['pdf'], 'xml_hash' => $built['hash'],
                    'valor_total' => number_format((float) $payload['total'], 2, '.', ''),
                    'idempotency_key' => $idempotencyKey, 'correlation_id' => $sale->correlation_id,
                    'tentativas' => 1,
                ]);
                if (!$invoice->save(false)) {
                    throw new \RuntimeException('Nao foi possivel persistir a NF-e simulada.');
                }
                foreach ($batch as $offset => $saleItem) {
                    (new InvoiceItem([
                        'nfe_id' => $invoice->id,
                        'venda_item_id' => $saleItem->id,
                        'ordem' => $offset + 1,
                    ]))->save(false);
                }
                (new IntegrationLog([
                    'integracao' => 'sefaz', 'operacao' => 'SIMULAR_NFE', 'status' => 'sucesso',
                    'idempotency_key' => $idempotencyKey, 'correlation_id' => $sale->correlation_id,
                    'tentativa' => 1, 'request_hash' => hash('sha256', json_encode($payload) ?: ''),
                    'response_hash' => $built['hash'], 'erro_codigo' => '100-SIMULADO',
                    'erro_mensagem' => 'Documento local sem valor fiscal; nenhum envio realizado.',
                    'duracao_ms' => 0,
                ]))->save(false);
                $documents[] = $invoice;
            }
            (new AuditEvent([
                'usuario_id' => $userId, 'acao' => 'NFE_SIMULADA_GERADA', 'entidade' => 'venda',
                'entidade_id' => (string) $sale->id,
                'dados_novos_hash' => hash('sha256', implode('|', array_map(static fn(Invoice $invoice): string => (string) $invoice->chave_acesso, $documents))),
                'correlation_id' => $sale->correlation_id,
            ]))->save(false);
            $transaction->commit();
            return $documents;
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            foreach ($createdFiles as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            throw $exception;
        }
    }

    private function nextNumber(): int
    {
        $db = Yii::$app->db;
        $condition = ['ambiente' => 'homologation', 'modelo' => 55, 'serie' => (int) $this->configuration['series']];
        $table = $db->quoteSql('{{%nfe_numeracao}}');
        $row = (new \yii\db\Query())->from('{{%nfe_numeracao}}')->where($condition)->one($db);
        if ($row === false) {
            $db->createCommand()->insert('{{%nfe_numeracao}}', $condition + ['ultimo_numero' => 0])->execute();
        }
        $row = $db->createCommand("SELECT * FROM {$table} WHERE ambiente=:ambiente AND modelo=:modelo AND serie=:serie FOR UPDATE", [
            ':ambiente' => 'homologation', ':modelo' => 55, ':serie' => (int) $this->configuration['series'],
        ])->queryOne();
        if ($row === false) {
            throw new \RuntimeException('Controle de numeracao NF-e nao encontrado.');
        }
        $number = (int) $row['ultimo_numero'] + 1;
        $db->createCommand()->update('{{%nfe_numeracao}}', ['ultimo_numero' => $number], ['id' => $row['id']])->execute();
        return $number;
    }

    /** @param SaleItem[] $items */
    private function payload(array $items, array $recipient, int $number, \DateTimeImmutable $issuedAt, string $idempotencyKey): array
    {
        $fiscalItems = [];
        $totalCents = 0;
        $simpleNational = in_array((int) $this->configuration['issuer']['taxRegime'], [1, 2, 4], true);
        foreach ($items as $saleItem) {
            $stock = $saleItem->stockItem;
            if ($stock === null) {
                throw new \RuntimeException("Item de venda {$saleItem->id} sem estoque GID relacionado.");
            }
            $totalCents += (int) round((float) $saleItem->valor_total * 100);
            $fiscal = [
                'code' => (string) $stock->codigo, 'gidCode' => (string) $stock->gid_id,
                'description' => (string) $stock->descricao, 'ncm' => (string) $stock->ncm,
                'cest' => $stock->cest ?: null, 'cfop' => (string) $stock->cfop,
                'unit' => (string) $stock->unidade_comercial,
                'quantity' => (float) $saleItem->quantidade, 'unitPrice' => (float) $saleItem->valor_unitario,
                'total' => (float) $saleItem->valor_total, 'origin' => (string) $stock->origem_mercadoria,
                'pisCst' => (string) $stock->pis_cst, 'cofinsCst' => (string) $stock->cofins_cst,
                'traceability' => trim('PLACA ' . (string) $stock->placa_veiculo . ' CHASSI ' . (string) $stock->chassi_veiculo),
            ];
            $fiscal[$simpleNational ? 'csosn' : 'icmsCst'] = (string) $stock->icms_cst;
            $fiscalItems[] = $fiscal;
        }
        $numeric = str_pad((string) (hexdec(substr(hash('sha256', $idempotencyKey), 0, 8)) % 100000000), 8, '0', STR_PAD_LEFT);
        return [
            'stateCode' => 43, 'issuedAt' => $issuedAt->format(DATE_ATOM),
            'series' => (int) $this->configuration['series'], 'number' => $number, 'numericCode' => $numeric,
            'issuer' => $this->configuration['issuer'], 'recipient' => $recipient,
            'destinationType' => ($recipient['state'] ?? 'RS') === 'RS' ? 1 : 2,
            'items' => $fiscalItems, 'total' => $totalCents / 100,
        ];
    }

    private function recipientFromSale(Sale $sale): array
    {
        return [
            'document' => $sale->destinatario_documento, 'name' => $sale->destinatario_nome,
            'stateRegistration' => $sale->destinatario_ie, 'street' => $sale->destinatario_logradouro,
            'number' => $sale->destinatario_numero, 'complement' => $sale->destinatario_complemento,
            'district' => $sale->destinatario_bairro, 'cityCode' => $sale->destinatario_municipio_codigo,
            'city' => $sale->destinatario_municipio, 'state' => $sale->destinatario_uf,
            'postalCode' => $sale->destinatario_cep, 'email' => $sale->destinatario_email,
            'phone' => $sale->destinatario_telefone,
        ];
    }
}
