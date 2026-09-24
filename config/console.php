<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$params = require __DIR__ . '/params.php';
return [
    'id' => 'nfdetranrs-console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'app\\commands',
    'components' => [
        'db' => require __DIR__ . '/db.php',
        'authManager' => ['class' => yii\rbac\DbManager::class],
        'gidClient' => [
            'class' => app\services\integration\GidSoapClient::class,
            'configuration' => $params['gid'],
            'company' => $params['company'],
        ],
    ],
    'params' => $params,
];
