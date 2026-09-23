<?php
declare(strict_types=1);
namespace app\services\integration;
interface SefazClientInterface
{
    public function authorize(array $invoice, string $idempotencyKey, string $correlationId): IntegrationResult;
    public function cancel(string $accessKey, string $reason, string $idempotencyKey, string $correlationId): IntegrationResult;
}
