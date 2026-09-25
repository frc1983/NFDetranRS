<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\Invoice;
use app\models\Sale;
use app\services\fiscal\NfeConfigurationValidator;
use app\services\fiscal\NfeSimulationService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class NfeController extends BaseController
{
    protected string $permission = 'nfe.visualizar';

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = ['class' => VerbFilter::class, 'actions' => ['simulate' => ['POST']]];
        return $behaviors;
    }

    protected function permissionForAction(string $actionId): string
    {
        return $actionId === 'simulate' ? 'nfe.emitir' : $this->permission;
    }

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

    public function actionSimulate(int $saleId): Response
    {
        $sale = Sale::findOne($saleId);
        if ($sale === null) {
            throw new NotFoundHttpException('Venda nao encontrada.');
        }
        try {
            $documents = (new NfeSimulationService(Yii::$app->params['nfe'], Yii::getAlias('@runtime/nfe')))
                ->simulate($sale, (int) Yii::$app->user->id);
            Yii::$app->session->setFlash('success', count($documents) . ' NF-e simulada(s) gerada(s), sem envio a SEFAZ e sem baixa no GID.');
        } catch (\Throwable $exception) {
            Yii::error($exception, __METHOD__);
            Yii::$app->session->setFlash('danger', $exception->getMessage());
        }
        return $this->redirect(['/nfe/index']);
    }

    public function actionDownload(int $id, string $type): Response
    {
        $invoice = Invoice::findOne($id);
        if ($invoice === null || !in_array($type, ['xml', 'danfe'], true)) {
            throw new NotFoundHttpException('Documento fiscal nao encontrado.');
        }
        $path = $type === 'xml' ? (string) $invoice->xml_caminho : (string) $invoice->danfe_caminho;
        $runtimeRoot = realpath(Yii::getAlias('@runtime/nfe'));
        $resolved = $path !== '' ? realpath($path) : false;
        if ($runtimeRoot === false || $resolved === false || !str_starts_with($resolved, $runtimeRoot . DIRECTORY_SEPARATOR)) {
            throw new NotFoundHttpException('Artefato fiscal indisponivel.');
        }
        $extension = $type === 'xml' ? 'xml' : 'pdf';
        return Yii::$app->response->sendFile($resolved, "NFe-SIMULADA-{$invoice->chave_acesso}.{$extension}", ['inline' => $type === 'danfe']);
    }
}
