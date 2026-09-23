<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$original = [];
foreach (['APP_ENV', 'GID_ENVIRONMENT', 'GID_ENVIRONMENT_CODE', 'GID_ALIAS_URL', 'GID_WSDL_URL', 'GID_SERVICE_URL'] as $name) {
    $original[$name] = getenv($name);
    putenv($name);
}

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

try {
    putenv('APP_ENV=local');
    $local = require dirname(__DIR__) . '/config/params.php';
    $assert($local['company']['cdvCode'] === 'CDV00027', 'Codigo CDV local incorreto.');
    $assert($local['company']['cnpj'] === '05034500000168', 'CNPJ local incorreto.');
    $assert($local['gid']['environment'] === 'homologation', 'Local deve usar homologacao.');
    $assert($local['gid']['environmentCode'] === 2, 'Codigo de homologacao deve ser 2.');
    $assert(str_contains($local['gid']['wsdlUrl'], 'secweb.hml.intra.rs.gov.br'), 'WSDL de homologacao incorreto.');

    putenv('APP_ENV=production');
    $production = require dirname(__DIR__) . '/config/params.php';
    $assert($production['gid']['environment'] === 'production', 'APP_ENV production deve usar GID de producao.');
    $assert($production['gid']['environmentCode'] === 1, 'Codigo de producao deve ser 1.');
    $assert(str_contains($production['gid']['wsdlUrl'], 'secweb.procergs.com.br'), 'WSDL de producao incorreto.');

    echo "gid config tests: OK\n";
} finally {
    foreach ($original as $name => $value) {
        $value === false ? putenv($name) : putenv($name . '=' . $value);
    }
}
