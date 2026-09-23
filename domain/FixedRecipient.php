<?php
declare(strict_types=1);
namespace app\domain;
final class FixedRecipient
{
    public static function fromConfig(callable $get): array
    {
        $document = trim((string) $get('recipient.document'));
        $name = trim((string) $get('recipient.name'));
        if ($document === '' || $name === '') {
            throw new \RuntimeException('Destinatario fixo nao configurado.');
        }
        return ['document' => $document, 'name' => $name];
    }
}
