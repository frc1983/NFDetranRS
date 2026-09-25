<?php
declare(strict_types=1);
namespace app\services\fiscal;

final class NfeAccessKey
{
    public static function generate(
        int $stateCode,
        \DateTimeInterface $issuedAt,
        string $issuerCnpj,
        int $model,
        int $series,
        int $number,
        int $emissionType,
        string $numericCode
    ): string {
        $cnpj = preg_replace('/\D+/', '', $issuerCnpj);
        $code = preg_replace('/\D+/', '', $numericCode);
        if (strlen($cnpj) !== 14 || strlen($code) > 8 || $model !== 55 || $series < 0 || $series > 999 || $number < 1 || $number > 999999999) {
            throw new \InvalidArgumentException('Dados invalidos para gerar a chave de acesso da NF-e.');
        }

        $base = str_pad((string) $stateCode, 2, '0', STR_PAD_LEFT)
            . $issuedAt->format('ym')
            . $cnpj
            . str_pad((string) $model, 2, '0', STR_PAD_LEFT)
            . str_pad((string) $series, 3, '0', STR_PAD_LEFT)
            . str_pad((string) $number, 9, '0', STR_PAD_LEFT)
            . $emissionType
            . str_pad($code, 8, '0', STR_PAD_LEFT);

        if (strlen($base) !== 43) {
            throw new \LogicException('A base da chave de acesso deve possuir 43 digitos.');
        }
        return $base . self::checkDigit($base);
    }

    public static function checkDigit(string $base): int
    {
        if (!preg_match('/^\d{43}$/', $base)) {
            throw new \InvalidArgumentException('A base da chave deve possuir 43 digitos.');
        }
        $sum = 0;
        $weight = 2;
        for ($index = 42; $index >= 0; $index--) {
            $sum += ((int) $base[$index]) * $weight;
            $weight = $weight === 9 ? 2 : $weight + 1;
        }
        $digit = 11 - ($sum % 11);
        return $digit >= 10 ? 0 : $digit;
    }
}
