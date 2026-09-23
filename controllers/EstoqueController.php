<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\StockItem;
use Yii;

final class EstoqueController extends BaseController
{
    protected string $permission = 'estoque.visualizar';

    public function actionIndex(): string
    {
        $query = StockItem::find()->orderBy(['updated_at' => SORT_DESC]);
        $search = trim((string) Yii::$app->request->get('q', ''));
        $status = (string) Yii::$app->request->get('status', '');
        if ($search !== '') {
            $query->andWhere(['or', ['like', 'codigo', $search], ['like', 'descricao', $search], ['like', 'gid_id', $search]]);
        }
        if ($status === 'disponivel') {
            $query->andWhere(['>', 'quantidade_disponivel', 0]);
        } elseif ($status === 'indisponivel') {
            $query->andWhere(['<=', 'quantidade_disponivel', 0]);
        }
        return $this->render('index', ['items' => $query->limit(200)->all(), 'search' => $search, 'status' => $status]);
    }
}
