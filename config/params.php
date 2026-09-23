<?php
declare(strict_types=1);

$environmentValue = static function (string $name, string $default = ''): string {
    $value = getenv($name);
    return $value === false || trim((string) $value) === '' ? $default : trim((string) $value);
};

$isProduction = getenv('APP_ENV') === 'production';
$gidEnvironment = $environmentValue('GID_ENVIRONMENT', $isProduction ? 'production' : 'homologation');
if (!in_array($gidEnvironment, ['homologation', 'production'], true)) {
    throw new RuntimeException('GID_ENVIRONMENT deve ser homologation ou production.');
}

$gidDefaults = $gidEnvironment === 'production'
    ? [
        'code' => 1,
        'aliasUrl' => 'http://desmanches.detran.rs.gov.br/integracaonfe',
        'wsdlUrl' => 'https://secweb.procergs.com.br/cdv/IntegracaoGidSoap?wsdl',
        'serviceUrl' => 'https://secweb.procergs.com.br/cdv/IntegracaoGidSoap',
    ]
    : [
        'code' => 2,
        'aliasUrl' => 'http://desmanches.hml.detran.rs.gov.br/integracaonfe',
        'wsdlUrl' => 'https://secweb.hml.intra.rs.gov.br/cdv/IntegracaoGidSoap?wsdl',
        'serviceUrl' => 'https://secweb.hml.intra.rs.gov.br/cdv/IntegracaoGidSoap',
    ];

return [
    'sale.maxItemsPerInvoice' => 100,
    'integration.maxAttempts' => 5,
    'recipient.documentConfigKey' => 'recipient.document',
    'recipient.nameConfigKey' => 'recipient.name',
    'company' => [
        'cdvCode' => $environmentValue('COMPANY_CDV_CODE', 'CDV00027'),
        'cnpj' => preg_replace('/\D+/', '', $environmentValue('COMPANY_CNPJ', '05034500000168')),
        'legalName' => $environmentValue('COMPANY_LEGAL_NAME', 'OFICINA DA MOTO COMERCIO DE PECAS LTDA'),
        'tradeName' => $environmentValue('COMPANY_TRADE_NAME', 'OFICINA DA MOTO'),
        'address' => $environmentValue('COMPANY_ADDRESS', 'RUA DOUTOR JOAO INACIO, 218 - NAVEGANTES - PORTO ALEGRE/RS - 90230-180'),
    ],
    'gid' => [
        'environment' => $gidEnvironment,
        'environmentCode' => (int) $environmentValue('GID_ENVIRONMENT_CODE', (string) $gidDefaults['code']),
        'aliasUrl' => $environmentValue('GID_ALIAS_URL', $gidDefaults['aliasUrl']),
        'wsdlUrl' => $environmentValue('GID_WSDL_URL', $gidDefaults['wsdlUrl']),
        'serviceUrl' => $environmentValue('GID_SERVICE_URL', $gidDefaults['serviceUrl']),
        'certificatePath' => $environmentValue('GID_CERT_PATH'),
        'certificatePassword' => $environmentValue('GID_CERT_PASSWORD'),
        'connectionTimeout' => max(1, (int) $environmentValue('GID_CONNECTION_TIMEOUT', '30')),
        'verifyPeer' => filter_var($environmentValue('GID_VERIFY_PEER', '1'), FILTER_VALIDATE_BOOL),
    ],
];
