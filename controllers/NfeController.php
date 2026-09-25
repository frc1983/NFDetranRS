<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\Invoice;
use app\services\fiscal\NfeConfigurationValidator;
use Yii;

final class NfeController extends BaseController
{
    protected string $permission = 'nfe.visualizar';

    public function actionIndex(): string
    {
        $documents = Invoice::find()->with('sale')->orderBy(['created_at' => SORT_DESC])->limit(100)->all();
        $counts = Invoice::find()->select(['status', 'total' => 'COUNT(*)'])->groupBy('status')->indexBy('status')->asArray()->all();
        $configuration = Yii::$app->params['nfe'];
        $configurationErrors = (new NfeConfigurationValidator())->errors(
            $configuration,
            $configuration['transport'] === 'sefaz'
        );
        return $this->render('index', [
            'documents' => $documents,
            'counts' => $counts,
            'nfe' => $configuration,
            'configurationErrors' => $configurationErrors,
        ]);
    }
}
