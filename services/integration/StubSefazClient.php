<?php
declare(strict_types=1);
namespace app\services\integration;
final class StubSefazClient implements SefazClientInterface
{
    public function authorize(array $invoice, string $idempotencyKey, string $correlationId): IntegrationResult
    {
        throw new \RuntimeException('Integracao SEFAZ nao configurada.');
    }
    public function cancel(string $accessKey, string $reason, string $idempotencyKey, string $correlationId): IntegrationResult
    {
        throw new \RuntimeException('Integracao SEFAZ nao configurada.');
    }
}
