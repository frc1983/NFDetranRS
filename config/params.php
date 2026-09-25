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

$nfeEnvironment = $environmentValue('NFE_ENVIRONMENT', $isProduction ? 'production' : 'homologation');
if (!in_array($nfeEnvironment, ['homologation', 'production'], true)) {
    throw new RuntimeException('NFE_ENVIRONMENT deve ser homologation ou production.');
}
$nfeIsProduction = $nfeEnvironment === 'production';
$nfeBaseUrl = $nfeIsProduction
    ? 'https://nfe.sefazrs.rs.gov.br/ws'
    : 'https://nfe-homologacao.sefazrs.rs.gov.br/ws';
$nfeTransport = $environmentValue('NFE_TRANSPORT', $nfeIsProduction ? 'sefaz' : 'mock');
if (!in_array($nfeTransport, ['mock', 'sefaz'], true)) {
    throw new RuntimeException('NFE_TRANSPORT deve ser mock ou sefaz.');
}
if ($nfeIsProduction && $nfeTransport === 'mock') {
    throw new RuntimeException('O simulador de NF-e nao pode ser usado em producao.');
}

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
        'layoutVersion' => $environmentValue('GID_LAYOUT_VERSION', '1'),
        'operatorRegistration' => $environmentValue('GID_OPERATOR_REGISTRATION', ''),
        'homologationOperation' => $environmentValue('GID_HOMOLOGATION_OPERATION', ''),
        'issuerName' => $environmentValue('GID_ISSUER_NAME', 'NFDETRANRS'),
        'issuerCnpj' => preg_replace('/\D+/', '', $environmentValue('GID_ISSUER_CNPJ', '05034500000168')),
    ],
    'nfe' => [
        'environment' => $nfeEnvironment,
        'environmentCode' => $nfeIsProduction ? 1 : 2,
        'transport' => $nfeTransport,
        'layoutVersion' => '4.00',
        'schemaPackage' => $environmentValue('NFE_SCHEMA_PACKAGE', 'PL_010_V1.30'),
        'model' => 55,
        'series' => (int) $environmentValue('NFE_SERIES', '1'),
        'certificatePath' => $environmentValue('NFE_CERT_PATH'),
        'certificatePassword' => $environmentValue('NFE_CERT_PASSWORD'),
        'authorizationUrl' => $nfeBaseUrl . '/NfeAutorizacao/NFeAutorizacao4.asmx',
        'authorizationReturnUrl' => $nfeBaseUrl . '/NfeRetAutorizacao/NFeRetAutorizacao4.asmx',
        'statusUrl' => $nfeBaseUrl . '/NFeStatusServico/NFeStatusServico4.asmx',
        'protocolUrl' => $nfeBaseUrl . '/NfeConsulta/NFeConsulta4.asmx',
        'eventUrl' => $nfeBaseUrl . '/recepcaoevento/recepcaoevento4.asmx',
        'issuer' => [
            'cnpj' => preg_replace('/\D+/', '', $environmentValue('COMPANY_CNPJ', '05034500000168')),
            'legalName' => $environmentValue('COMPANY_LEGAL_NAME', 'OFICINA DA MOTO COMERCIO DE PECAS LTDA'),
            'tradeName' => $environmentValue('COMPANY_TRADE_NAME', 'OFICINA DA MOTO'),
            'stateRegistration' => preg_replace('/\D+/', '', $environmentValue('NFE_COMPANY_IE')),
            'taxRegime' => (int) $environmentValue('NFE_COMPANY_CRT', '0'),
            'stateCode' => 43,
            'state' => 'RS',
            'cityCode' => (int) $environmentValue('NFE_COMPANY_CITY_CODE', '4314902'),
            'city' => $environmentValue('NFE_COMPANY_CITY', 'PORTO ALEGRE'),
            'street' => $environmentValue('NFE_COMPANY_STREET', 'RUA DOUTOR JOAO INACIO'),
            'number' => $environmentValue('NFE_COMPANY_NUMBER', '218'),
            'complement' => $environmentValue('NFE_COMPANY_COMPLEMENT'),
            'district' => $environmentValue('NFE_COMPANY_DISTRICT', 'NAVEGANTES'),
            'postalCode' => preg_replace('/\D+/', '', $environmentValue('NFE_COMPANY_POSTAL_CODE', '90230180')),
            'phone' => preg_replace('/\D+/', '', $environmentValue('NFE_COMPANY_PHONE')),
        ],
    ],
];
