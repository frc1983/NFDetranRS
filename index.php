<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/bootstrap.php';
defined('YII_DEBUG') or define('YII_DEBUG', filter_var(getenv('YII_DEBUG') ?: false, FILTER_VALIDATE_BOOL));
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'prod');
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

(new yii\web\Application(require __DIR__ . '/config/web.php'))->run();
