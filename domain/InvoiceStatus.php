<?php
declare(strict_types=1);
namespace app\domain;
final class InvoiceStatus
{
    public const PENDING = 'pendente', PROCESSING = 'processando', AUTHORIZED = 'autorizada', REJECTED = 'rejeitada', CANCELLED = 'cancelada';
    private const TRANSITIONS = [self::PENDING => [self::PROCESSING, self::CANCELLED], self::PROCESSING => [self::AUTHORIZED, self::REJECTED], self::REJECTED => [self::PENDING, self::CANCELLED], self::AUTHORIZED => [self::CANCELLED], self::CANCELLED => []];
    public static function assertTransition(string $from, string $to): void
    {
        if (!in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            throw new \DomainException("Transicao de NF-e invalida: {$from} -> {$to}");
        }
    }
}
