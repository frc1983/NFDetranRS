<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\StockItem;
use app\services\integration\GidInventorySynchronizer;
use Yii;
use yii\filters\VerbFilter;

final class EstoqueController extends BaseController
{
    protected string $permission = 'estoque.visualizar';

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['sync' => ['POST']]],
        ]);
    }

    protected function permissionForAction(string $actionId): string
    {
        return $actionId === 'sync' ? 'estoque.sincronizar' : $this->permission;
    }

    public function actionIndex(): string
    {
        $query = StockItem::find()->orderBy(['updated_at' => SORT_DESC]);
        $search = trim((string) Yii::$app->request->get('q', ''));
        $status = (string) Yii::$app->request->get('status', '');
        if ($search !== '') {
            $query->andWhere(['or', ['like', 'codigo', $search], ['like', 'descricao', $search], ['like', 'gid_id', $search], ['like', 'marca', $search], ['like', 'modelo', $search], ['like', 'placa_veiculo', $search]]);
        }
        if ($status === 'disponivel') {
            $query->andWhere(['>', 'quantidade_disponivel', 0]);
        } elseif ($status === 'indisponivel') {
            $query->andWhere(['<=', 'quantidade_disponivel', 0]);
        }
        return $this->render('index', ['items' => $query->limit(200)->all(), 'search' => $search, 'status' => $status]);
    }

    public function actionSync(): \yii\web\Response
    {
        try {
            $result = (new GidInventorySynchronizer(Yii::$app->gidClient))->synchronize();
            Yii::$app->session->setFlash('success', "GID sincronizado: {$result['received']} recebidos, {$result['created']} novos e {$result['updated']} atualizados.");
        } catch (\Throwable $exception) {
            Yii::$app->session->setFlash('danger', $exception->getMessage());
        }
        return $this->redirect(['/estoque/index']);
    }
}
