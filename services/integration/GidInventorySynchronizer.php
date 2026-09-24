<?php
declare(strict_types=1);

namespace app\services\integration;

use app\models\IntegrationLog;
use app\models\StockItem;
use Yii;

final class GidInventorySynchronizer
{
    public function __construct(private GidClientInterface $client)
    {
    }

    /** @return array{received:int,created:int,updated:int,disabled:int,correlationId:string} */
    public function synchronize(?string $correlationId = null): array
    {
        $correlationId ??= $this->uuidV4();
        $idempotencyKey = 'gid-stock-' . date('YmdHis') . '-' . bin2hex(random_bytes(8));
        $startedAt = microtime(true);
        $this->log('iniciada', $idempotencyKey, $correlationId, 1);
        $transaction = Yii::$app->db->beginTransaction();
        $received = $created = $updated = $disabled = 0;
        $seenIds = [];
        try {
            foreach ($this->client->fetchInventory($correlationId) as $payload) {
                $received++;
                $gidId = (string) $payload['gid_id'];
                $seenIds[] = $gidId;
                $item = StockItem::findOne(['gid_id' => $gidId]);
                $isNew = $item === null;
                $item ??= new StockItem(['gid_id' => $gidId, 'valor_unitario' => 0]);
                foreach (['codigo', 'descricao', 'nome_originario', 'marca', 'modelo', 'ano_modelo', 'tipo_veiculo', 'placa_veiculo', 'chassi_veiculo', 'grupo_gid', 'situacao_peca', 'peca_acoplada', 'codigo_nota', 'item_controlado', 'observacao', 'quantidade_disponivel'] as $attribute) {
                    $item->{$attribute} = $payload[$attribute] ?? null;
                }
                $item->payload_hash = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
                $item->sincronizado_em = date('Y-m-d H:i:s');
                if (!$item->save()) {
                    throw new \RuntimeException('Falha ao persistir item GID ' . $gidId . ': ' . json_encode($item->getFirstErrors()));
                }
                $isNew ? $created++ : $updated++;
            }

            $missingCondition = $seenIds === []
                ? ['>', 'quantidade_disponivel', 0]
                : ['and', ['not in', 'gid_id', $seenIds], ['>', 'quantidade_disponivel', 0]];
            $disabled = StockItem::updateAll(['quantidade_disponivel' => 0], $missingCondition);
            $transaction->commit();
            $this->log('sucesso', $idempotencyKey, $correlationId, 1, null, null, (int) round((microtime(true) - $startedAt) * 1000));
            return compact('received', 'created', 'updated', 'disabled', 'correlationId');
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            $this->log('erro', $idempotencyKey, $correlationId, 1, 'GID_SYNC_ERROR', mb_substr($exception->getMessage(), 0, 2000), (int) round((microtime(true) - $startedAt) * 1000));
            throw $exception;
        }
    }

    private function log(string $status, string $idempotencyKey, string $correlationId, int $attempt, ?string $errorCode = null, ?string $errorMessage = null, ?int $durationMs = null): void
    {
        (new IntegrationLog([
            'integracao' => 'gid',
            'operacao' => 'sincronizar_estoque',
            'status' => $status,
            'idempotency_key' => $idempotencyKey,
            'correlation_id' => $correlationId,
            'tentativa' => $attempt,
            'erro_codigo' => $errorCode,
            'erro_mensagem' => $errorMessage,
            'duracao_ms' => $durationMs,
        ]))->save(false);
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
