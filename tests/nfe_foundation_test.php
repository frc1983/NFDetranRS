<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

use app\services\fiscal\NfeAccessKey;
use app\services\fiscal\NfeConfigurationValidator;
use app\services\fiscal\NfeDraftValidator;
use app\services\integration\MockSefazClient;
use NFePHP\Common\Keys;

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$configuration = [
    'environment' => 'homologation', 'environmentCode' => 2, 'transport' => 'mock',
    'series' => 1, 'schemaPackage' => 'PL_010_V1.30', 'layoutVersion' => '4.00',
    'issuer' => [
        'cnpj' => '05034500000168', 'legalName' => 'OFICINA DA MOTO COMERCIO DE PECAS LTDA',
        'stateRegistration' => '1234567890', 'taxRegime' => 1, 'cityCode' => 4314902,
        'city' => 'PORTO ALEGRE', 'street' => 'RUA TESTE', 'number' => '218',
        'district' => 'NAVEGANTES', 'postalCode' => '90230180',
    ],
];
$assert((new NfeConfigurationValidator())->errors($configuration, false) === [], 'Configuracao fiscal de teste deveria ser valida.');

$invoice = [
    'stateCode' => 43, 'issuedAt' => '2026-09-25T10:00:00-03:00', 'series' => 1, 'number' => 1,
    'issuer' => $configuration['issuer'],
    'recipient' => ['document' => '00000000191', 'name' => 'CONSUMIDOR DE HOMOLOGACAO'],
    'items' => [[
        'code' => 'GID-1', 'description' => 'PECA USADA PARA TESTE', 'ncm' => '87089990',
        'cfop' => '5102', 'unit' => 'UN', 'quantity' => 1, 'unitPrice' => 100.00, 'total' => 100.00,
        'origin' => '0', 'csosn' => '102', 'pisCst' => '49', 'cofinsCst' => '49',
    ]],
    'total' => 100.00,
];
(new NfeDraftValidator())->assertValid($invoice);

$key = NfeAccessKey::generate(43, new DateTimeImmutable($invoice['issuedAt']), '05034500000168', 55, 1, 1, 1, '12345678');
$assert(strlen($key) === 44 && Keys::isValid($key), 'Chave NF-e gerada deve possuir DV valido.');

$result = (new MockSefazClient(['configuration' => $configuration]))->authorize(
    $invoice,
    'nfe-test-1',
    '00000000-0000-4000-8000-000000000001'
);
$assert($result->success && ($result->payload['simulated'] ?? false) === true, 'Simulador deveria autorizar como documento sem valor fiscal.');
$assert(Keys::isValid((string) $result->payload['accessKey']), 'Simulador deve retornar chave valida.');

try {
    $invalid = $invoice;
    $invalid['items'][0]['ncm'] = '';
    (new NfeDraftValidator())->assertValid($invalid);
    throw new RuntimeException('Rascunho sem NCM nao foi bloqueado.');
} catch (InvalidArgumentException $expected) {
}

echo "nfe foundation tests: OK\n";
