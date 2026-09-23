<?php
declare(strict_types=1);
namespace app\domain;
final class SaleStatus
{
    public const DRAFT = 'rascunho', CONFIRMED = 'confirmada', INVOICING = 'faturando', COMPLETED = 'concluida', CANCELLED = 'cancelada';
    private const TRANSITIONS = [self::DRAFT => [self::CONFIRMED, self::CANCELLED], self::CONFIRMED => [self::INVOICING, self::CANCELLED], self::INVOICING => [self::COMPLETED], self::COMPLETED => [], self::CANCELLED => []];
    public static function assertTransition(string $from, string $to): void
    {
        if (!in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            throw new \DomainException("Transicao de venda invalida: {$from} -> {$to}");
        }
    }
}
