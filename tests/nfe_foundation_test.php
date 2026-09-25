<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

use app\services\fiscal\NfeAccessKey;
use app\services\fiscal\NfeConfigurationValidator;
use app\services\fiscal\NfeDraftValidator;
use app\services\fiscal\NfeXmlBuilder;
use app\services\fiscal\NfeArtifactStorage;
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
    'numericCode' => '12345678',
    'issuer' => $configuration['issuer'],
    'recipient' => [
        'document' => '00000000191', 'name' => 'CONSUMIDOR DE HOMOLOGACAO',
        'street' => 'RUA DE TESTE', 'number' => '100', 'district' => 'CENTRO',
        'cityCode' => 4314902, 'city' => 'PORTO ALEGRE', 'state' => 'RS', 'postalCode' => '90000000',
    ],
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

$built = (new NfeXmlBuilder($configuration))->build($invoice);
$assert(Keys::isValid($built['accessKey']), 'XML deve conter chave valida.');
$assert(hash('sha256', $built['xml']) === $built['hash'], 'Hash do XML deve ser reproduzivel.');
$assert(str_contains($built['xml'], 'SEM VALOR FISCAL'), 'XML simulado deve possuir aviso explicito.');
$artifactDirectory = sys_get_temp_dir() . '/nfdetranrs-nfe-artifacts-' . bin2hex(random_bytes(4));
$paths = (new NfeArtifactStorage($artifactDirectory))->store($built['xml'], $built['accessKey'], new DateTimeImmutable($invoice['issuedAt']));
try {
    $assert(is_file($paths['xml']) && filesize($paths['xml']) > 0, 'XML deve ser armazenado.');
    $assert(is_file($paths['pdf']) && filesize($paths['pdf']) > 1000, 'DANFE deve ser gerado em PDF.');
} finally {
    @unlink($paths['xml']);
    @unlink($paths['pdf']);
    @rmdir(dirname($paths['xml']));
    @rmdir(dirname(dirname($paths['xml'])));
    @rmdir($artifactDirectory);
}

try {
    $invalid = $invoice;
    $invalid['items'][0]['ncm'] = '';
    (new NfeDraftValidator())->assertValid($invalid);
    throw new RuntimeException('Rascunho sem NCM nao foi bloqueado.');
} catch (InvalidArgumentException $expected) {
}

echo "nfe foundation tests: OK\n";
