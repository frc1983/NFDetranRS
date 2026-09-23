<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\Invoice;

final class NfeController extends BaseController
{
    protected string $permission = 'nfe.visualizar';

    public function actionIndex(): string
    {
        $documents = Invoice::find()->with('sale')->orderBy(['created_at' => SORT_DESC])->limit(100)->all();
        $counts = Invoice::find()->select(['status', 'total' => 'COUNT(*)'])->groupBy('status')->indexBy('status')->asArray()->all();
        return $this->render('index', ['documents' => $documents, 'counts' => $counts]);
    }
}
