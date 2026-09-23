<?php
declare(strict_types=1);
namespace app\services\integration;
final class IntegrationResult
{
    public function __construct(public bool $success, public ?string $externalId = null, public ?string $code = null, public ?string $message = null, public array $payload = []) {}
}
