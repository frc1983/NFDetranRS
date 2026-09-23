<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\AuditEvent;
use app\models\IntegrationLog;
use Yii;

final class AuditoriaController extends BaseController
{
    protected string $permission = 'auditoria.visualizar';

    public function actionIndex(): string
    {
        $events = [];
        foreach (AuditEvent::find()->with('user')->orderBy(['created_at' => SORT_DESC])->limit(100)->all() as $audit) {
            $events[] = ['event' => $audit->acao, 'user' => $audit->user?->nome ?? 'Sistema', 'module' => $audit->entidade, 'date' => $audit->created_at, 'status' => 'Informativo', 'timestamp' => strtotime((string) $audit->created_at)];
        }
        foreach (IntegrationLog::find()->orderBy(['created_at' => SORT_DESC])->limit(100)->all() as $log) {
            $events[] = ['event' => $log->operacao, 'user' => strtoupper((string) $log->integracao), 'module' => strtoupper((string) $log->integracao), 'date' => $log->created_at, 'status' => $log->status, 'timestamp' => strtotime((string) $log->created_at)];
        }
        $search = mb_strtolower(trim((string) Yii::$app->request->get('q', '')));
        $module = strtoupper(trim((string) Yii::$app->request->get('module', '')));
        $period = (string) Yii::$app->request->get('period', 'Hoje');
        $days = ['Hoje' => 0, 'Últimos 7 dias' => 7, 'Últimos 30 dias' => 30][$period] ?? 0;
        $cutoff = $days === 0 ? strtotime('today') : strtotime("-{$days} days");
        $events = array_values(array_filter($events, static function (array $event) use ($search, $module, $cutoff): bool {
            $matchesSearch = $search === '' || str_contains(mb_strtolower($event['event'] . ' ' . $event['user']), $search);
            $matchesModule = $module === '' || strtoupper((string) $event['module']) === $module;
            return $matchesSearch && $matchesModule && $event['timestamp'] >= $cutoff;
        }));
        usort($events, static fn(array $a, array $b): int => $b['timestamp'] <=> $a['timestamp']);
        return $this->render('index', ['events' => array_slice($events, 0, 100), 'search' => $search, 'module' => $module, 'period' => $period]);
    }
}
