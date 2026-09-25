<?php
declare(strict_types=1);
namespace app\services\integration;
final class StubSefazClient extends \yii\base\Component implements SefazClientInterface
{
    public array $configuration = [];
    public function authorize(array $invoice, string $idempotencyKey, string $correlationId): IntegrationResult
    {
        throw new \RuntimeException('Integracao SEFAZ nao configurada.');
    }
    public function cancel(string $accessKey, string $reason, string $idempotencyKey, string $correlationId): IntegrationResult
    {
        throw new \RuntimeException('Integracao SEFAZ nao configurada.');
    }
}
