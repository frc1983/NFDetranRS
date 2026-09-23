<?php
declare(strict_types=1);
require dirname(__DIR__) . '/domain/InvoiceBatchSplitter.php';
require dirname(__DIR__) . '/domain/SaleStatus.php';
require dirname(__DIR__) . '/domain/InvoiceStatus.php';
require dirname(__DIR__) . '/domain/FixedRecipient.php';

use app\domain\InvoiceBatchSplitter;
use app\domain\SaleStatus;
use app\domain\InvoiceStatus;
use app\domain\FixedRecipient;

function check(bool $condition, string $message): void
{
    if (!$condition) { throw new RuntimeException($message); }
}
function rejects(callable $fn, string $exception): void
{
    try { $fn(); } catch (Throwable $e) {
        if ($e instanceof $exception) { return; }
        throw $e;
    }
    throw new RuntimeException('Operacao invalida foi aceita: ' . $exception);
}
$splitter = new InvoiceBatchSplitter();
foreach ([0 => [], 1 => [1], 100 => [100], 101 => [100, 1], 201 => [100, 100, 1]] as $count => $expected) {
    $items = $count === 0 ? [] : range(1, $count);
    $batches = $splitter->split($items);
    check(array_map('count', $batches) === $expected, 'Quantidade incorreta: ' . $count);
    check(array_merge([], ...$batches) === $items, 'Itens perdidos ou reordenados');
}
foreach ([0, -1, 101] as $limit) { rejects(fn() => $splitter->split([1], $limit), InvalidArgumentException::class); }
SaleStatus::assertTransition(SaleStatus::DRAFT, SaleStatus::CONFIRMED);
SaleStatus::assertTransition(SaleStatus::CONFIRMED, SaleStatus::INVOICING);
SaleStatus::assertTransition(SaleStatus::INVOICING, SaleStatus::COMPLETED);
rejects(fn() => SaleStatus::assertTransition(SaleStatus::COMPLETED, SaleStatus::DRAFT), DomainException::class);
InvoiceStatus::assertTransition(InvoiceStatus::PENDING, InvoiceStatus::PROCESSING);
InvoiceStatus::assertTransition(InvoiceStatus::PROCESSING, InvoiceStatus::AUTHORIZED);
rejects(fn() => InvoiceStatus::assertTransition(InvoiceStatus::PENDING, InvoiceStatus::AUTHORIZED), DomainException::class);
rejects(fn() => FixedRecipient::fromConfig(fn($key) => null), RuntimeException::class);
check(FixedRecipient::fromConfig(fn($key) => ['recipient.document' => 'configured', 'recipient.name' => 'Destinatario fixo'][$key])['name'] === 'Destinatario fixo', 'Destinatario incorreto');
echo "domain tests: OK\n";
