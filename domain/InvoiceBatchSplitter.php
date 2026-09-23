<?php
declare(strict_types=1);
namespace app\domain;
final class InvoiceBatchSplitter
{
    public function split(array $items, int $maxItems = 100): array
    {
        if ($maxItems < 1 || $maxItems > 100) {
            throw new \InvalidArgumentException('maxItems deve estar entre 1 e 100.');
        }
        return array_chunk(array_values($items), $maxItems);
    }
}
