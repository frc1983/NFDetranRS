<?php
declare(strict_types=1);
return ['class' => yii\db\Connection::class, 'dsn' => (string) getenv('DB_DSN'), 'username' => (string) getenv('DB_USERNAME'), 'password' => (string) (getenv('DB_PASSWORD') ?: ''), 'charset' => 'utf8mb4', 'enableSchemaCache' => false];
