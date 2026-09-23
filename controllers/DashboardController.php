<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\AuditEvent;
use app\models\IntegrationLog;
use app\models\Invoice;
use app\models\Sale;
use app\models\StockItem;

final class DashboardController extends BaseController
{
    protected string $permission = 'dashboard.visualizar';

    public function actionIndex(): string
    {
        $today = date('Y-m-d 00:00:00');
        $metrics = [
            ['label' => 'Itens disponíveis', 'value' => (string) StockItem::find()->where(['>', 'quantidade_disponivel', 0])->count(), 'trend' => 'Estoque persistido no GID', 'class' => ''],
            ['label' => 'Vendas hoje', 'value' => (string) Sale::find()->where(['>=', 'created_at', $today])->count(), 'trend' => 'Registros criados hoje', 'class' => ''],
            ['label' => 'NF-e em processamento', 'value' => (string) Invoice::find()->where(['status' => ['pendente', 'processando']])->count(), 'trend' => 'Documentos na fila', 'class' => ''],
            ['label' => 'Falhas de integração', 'value' => (string) IntegrationLog::find()->where(['status' => 'erro'])->count(), 'trend' => 'Eventos que exigem atenção', 'class' => 'warning'],
        ];
        $activities = AuditEvent::find()->with('user')->orderBy(['created_at' => SORT_DESC])->limit(8)->all();
        return $this->render('index', ['metrics' => $metrics, 'activities' => $activities]);
    }
}
