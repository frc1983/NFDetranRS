<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'test');
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

new yii\console\Application(require dirname(__DIR__) . '/config/console.php');

$fake = new class implements app\services\integration\GidClientInterface {
    public function fetchInventory(string $correlationId): iterable
    {
        foreach ([101 => 'Motor de partida', 102 => 'Farol dianteiro'] as $id => $description) {
            yield [
                'gid_id' => (string) $id,
                'codigo' => (string) $id,
                'descricao' => $description,
                'nome_originario' => $description,
                'marca' => 'HONDA',
                'modelo' => 'CG 160',
                'ano_modelo' => '2024',
                'tipo_veiculo' => 'MOTOCICLETA',
                'placa_veiculo' => 'ABC1D23',
                'chassi_veiculo' => 'TESTECHASSI123456789',
                'grupo_gid' => '1',
                'situacao_peca' => '1',
                'peca_acoplada' => 'N',
                'codigo_nota' => '1',
                'item_controlado' => 'N',
                'observacao' => '',
                'quantidade_disponivel' => 1,
            ];
        }
    }
};

$transaction = Yii::$app->db->beginTransaction();
try {
    $result = (new app\services\integration\GidInventorySynchronizer($fake))->synchronize('00000000-0000-4000-8000-000000000001');
    if ($result['received'] !== 2 || $result['created'] !== 2) {
        throw new RuntimeException('Contadores da sincronizacao GID incorretos.');
    }
    if (app\models\StockItem::find()->where(['gid_id' => ['101', '102']])->count() !== '2') {
        throw new RuntimeException('Itens GID nao foram persistidos.');
    }
    echo "gid sync tests: OK\n";
} finally {
    $transaction->rollBack();
}
