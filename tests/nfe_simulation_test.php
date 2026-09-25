<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'test');
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

new yii\console\Application(require dirname(__DIR__) . '/config/console.php');

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};
$configuration = Yii::$app->params['nfe'];
$configuration['issuer']['stateRegistration'] = '1234567890';
$configuration['issuer']['taxRegime'] = 1;
$storage = Yii::getAlias('@runtime/nfe-test-' . bin2hex(random_bytes(4)));
$files = [];
$transaction = Yii::$app->db->beginTransaction();
try {
    $user = new app\models\User([
        'nome' => 'Teste Fiscal', 'email' => 'fiscal-' . bin2hex(random_bytes(4)) . '@example.test',
        'password_hash' => Yii::$app->security->generatePasswordHash('Test-Password-123!'),
        'auth_key' => Yii::$app->security->generateRandomString(64), 'status' => 'ativo',
    ]);
    $user->save(false);
    $stock = new app\models\StockItem([
        'gid_id' => 'TEST-' . bin2hex(random_bytes(4)), 'codigo' => 'GID-TESTE',
        'descricao' => 'PECA USADA DE HOMOLOGACAO', 'ncm' => '87089990', 'cfop' => '5102',
        'unidade_comercial' => 'UN', 'origem_mercadoria' => '0', 'icms_cst' => '102',
        'pis_cst' => '49', 'cofins_cst' => '49', 'quantidade_disponivel' => 1,
        'valor_unitario' => 100, 'payload_hash' => hash('sha256', 'fiscal-test'),
        'sincronizado_em' => date('Y-m-d H:i:s'),
    ]);
    $stock->save(false);
    $sale = new app\models\Sale([
        'numero' => 'VEN-TEST-' . bin2hex(random_bytes(4)), 'status' => 'rascunho',
        'destinatario_documento' => '00000000191', 'destinatario_nome' => 'CONSUMIDOR DE HOMOLOGACAO',
        'destinatario_logradouro' => 'RUA DE TESTE', 'destinatario_numero' => '100',
        'destinatario_bairro' => 'CENTRO', 'destinatario_municipio_codigo' => 4314902,
        'destinatario_municipio' => 'PORTO ALEGRE', 'destinatario_uf' => 'RS',
        'destinatario_cep' => '90000000', 'valor_total' => 100,
        'idempotency_key' => Yii::$app->security->generateRandomString(64),
        'correlation_id' => '00000000-0000-4000-8000-000000000002', 'created_by' => $user->id,
    ]);
    $sale->save(false);
    (new app\models\SaleItem([
        'venda_id' => $sale->id, 'estoque_gid_id' => $stock->id,
        'quantidade' => 1, 'valor_unitario' => 100, 'valor_total' => 100,
    ]))->save(false);

    $service = new app\services\fiscal\NfeSimulationService($configuration, $storage);
    $documents = $service->simulate($sale, (int) $user->id);
    $assert(count($documents) === 1 && (bool) $documents[0]->simulada, 'Deve gerar uma NF-e explicitamente simulada.');
    $assert($documents[0]->status === 'autorizada', 'Documento simulado deve completar o fluxo local.');
    $assert(is_file($documents[0]->xml_caminho) && is_file($documents[0]->danfe_caminho), 'XML e DANFE devem existir.');
    $files = [$documents[0]->xml_caminho, $documents[0]->danfe_caminho];
    $again = $service->simulate($sale, (int) $user->id);
    $assert((int) $again[0]->id === (int) $documents[0]->id, 'Simulacao deve ser idempotente.');
    echo "nfe simulation tests: OK\n";
} finally {
    $transaction->rollBack();
    foreach ($files as $file) {
        @unlink($file);
    }
    if ($files !== []) {
        @rmdir(dirname($files[0]));
        @rmdir(dirname(dirname($files[0])));
    }
    @rmdir($storage);
}
