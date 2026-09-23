<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$appEnvironment = (string) (getenv('APP_ENV') ?: 'local');
if (!in_array($appEnvironment, ['local', 'production'], true)) {
    throw new RuntimeException('APP_ENV deve ser local ou production.');
}

$envFile = (string) (getenv('APP_ENV_FILE') ?: $projectRoot . '/.env.' . $appEnvironment);
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = array_map('trim', explode('=', $line, 2));
        if (getenv($name) === false) {
            putenv($name . '=' . trim($value, "\"'"));
        }
    }
}
putenv('APP_ENV=' . $appEnvironment);
foreach (['DB_DSN', 'DB_USERNAME'] as $name) {
    if ((string) getenv($name) === '') {
        throw new RuntimeException("Variavel obrigatoria ausente: {$name}");
    }
}
