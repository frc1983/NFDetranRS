<?php
declare(strict_types=1);

$envFile = dirname(__DIR__) . '/.env';
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
foreach (['DB_DSN', 'DB_USERNAME'] as $name) {
    if ((string) getenv($name) === '') {
        throw new RuntimeException("Variavel obrigatoria ausente: {$name}");
    }
}
