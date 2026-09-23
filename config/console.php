<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
return ['id' => 'nfdetranrs-console', 'basePath' => dirname(__DIR__), 'controllerNamespace' => 'app\\commands', 'components' => ['db' => require __DIR__ . '/db.php', 'authManager' => ['class' => yii\rbac\DbManager::class]]];
