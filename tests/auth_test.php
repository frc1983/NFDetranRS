<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'test');
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

new yii\console\Application(require dirname(__DIR__) . '/config/console.php');

$transaction = Yii::$app->db->beginTransaction();
try {
    $password = 'Temporary-auth-test-2026!';
    $user = new app\models\User([
        'nome' => 'Teste temporario',
        'email' => 'auth-test-' . bin2hex(random_bytes(4)) . '@local.invalid',
        'status' => 'ativo',
    ]);
    $user->setPassword($password);
    $user->generateAuthKey();
    if (!$user->save() || !$user->validatePassword($password)) {
        throw new RuntimeException('Falha ao persistir ou validar a senha do usuario temporario.');
    }
    $role = Yii::$app->authManager->getRole('administrador');
    if ($role === null) {
        throw new RuntimeException('Papel administrador ausente.');
    }
    Yii::$app->authManager->assign($role, (string) $user->id);
    foreach (['dashboard.visualizar', 'estoque.visualizar', 'venda.criar', 'venda.visualizar', 'nfe.visualizar', 'auditoria.visualizar', 'configuracao.gerenciar'] as $permission) {
        if (!Yii::$app->authManager->checkAccess((string) $user->id, $permission)) {
            throw new RuntimeException("Permissao ausente para administrador: {$permission}");
        }
    }
    echo "auth tests: OK\n";
} finally {
    $transaction->rollBack();
}
