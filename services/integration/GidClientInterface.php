<?php
declare(strict_types=1);
namespace app\services\integration;
interface GidClientInterface
{
    /** @return iterable<array<string,mixed>> */
    public function fetchInventory(string $correlationId): iterable;
}
