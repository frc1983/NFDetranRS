<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$cookieValidationKey = (string) getenv('APP_COOKIE_VALIDATION_KEY');
if ($cookieValidationKey === '') {
    throw new RuntimeException('Variavel obrigatoria ausente: APP_COOKIE_VALIDATION_KEY');
}
$isProduction = getenv('APP_ENV') === 'production';
return [
    'id' => 'nfdetranrs',
    'name' => 'NF Detran RS',
    'basePath' => dirname(__DIR__),
    'language' => 'pt-BR',
    'timeZone' => 'America/Sao_Paulo',
    'bootstrap' => ['log'],
    'defaultRoute' => 'dashboard/index',
    'aliases' => ['@bower' => '@vendor/bower-asset', '@npm' => '@vendor/npm-asset'],
    'components' => [
        'request' => ['cookieValidationKey' => $cookieValidationKey, 'enableCsrfValidation' => true, 'csrfCookie' => ['httpOnly' => true, 'sameSite' => 'Lax', 'secure' => $isProduction]],
        'db' => require __DIR__ . '/db.php',
        'user' => ['identityClass' => app\models\User::class, 'enableAutoLogin' => true, 'enableSession' => true, 'authTimeout' => 1800, 'loginUrl' => ['/site/login'], 'identityCookie' => ['name' => '_nfdetranrs_identity', 'httpOnly' => true, 'sameSite' => 'Lax', 'secure' => $isProduction]],
        'session' => ['name' => '_nfdetranrs_session', 'cookieParams' => ['httpOnly' => true, 'sameSite' => 'Lax', 'secure' => $isProduction]],
        'errorHandler' => ['errorAction' => 'site/error'],
        'assetManager' => ['basePath' => dirname(__DIR__) . '/web/assets', 'baseUrl' => '@web/assets'],
        'authManager' => ['class' => yii\rbac\DbManager::class],
        'log' => ['traceLevel' => 0, 'targets' => [['class' => yii\log\FileTarget::class, 'levels' => ['error', 'warning'], 'logVars' => []]]],
        'urlManager' => ['enablePrettyUrl' => true, 'showScriptName' => false],
        'gidClient' => ['class' => app\services\integration\StubGidClient::class],
        'sefazClient' => ['class' => app\services\integration\StubSefazClient::class],
    ],
    'params' => require __DIR__ . '/params.php',
];
