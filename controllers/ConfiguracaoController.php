<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\AuditEvent;
use app\models\Configuration;
use Yii;
use yii\web\Response;

final class ConfiguracaoController extends BaseController
{
    protected string $permission = 'configuracao.gerenciar';

    public function actionIndex(): Response|string
    {
        if (Yii::$app->request->isPost) {
            $name = trim((string) Yii::$app->request->post('recipient_name', ''));
            $document = preg_replace('/\D+/', '', (string) Yii::$app->request->post('recipient_document', '')) ?? '';
            if ($name === '' || strlen($document) !== 14) {
                Yii::$app->session->setFlash('danger', 'Informe a razao social e um CNPJ com 14 digitos.');
            } else {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    foreach (['recipient.name' => $name, 'recipient.document' => $document] as $key => $value) {
                        $setting = Configuration::findOne(['chave' => $key]);
                        if ($setting === null) {
                            throw new \RuntimeException("Configuracao ausente: {$key}");
                        }
                        $setting->valor = $value;
                        $setting->updated_by = Yii::$app->user->id;
                        $setting->save(false);
                    }
                    (new AuditEvent([
                        'usuario_id' => Yii::$app->user->id,
                        'acao' => 'DESTINATARIO_FIXO_ATUALIZADO',
                        'entidade' => 'configuracao',
                        'entidade_id' => 'recipient',
                        'dados_novos_hash' => hash('sha256', $document . '|' . $name),
                        'ip_hash' => hash('sha256', (string) Yii::$app->request->userIP),
                        'user_agent_hash' => hash('sha256', (string) Yii::$app->request->userAgent),
                        'correlation_id' => Yii::$app->security->generateRandomString(36),
                    ]))->save(false);
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Destinatario fixo atualizado.');
                    return $this->refresh();
                } catch (\Throwable $exception) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('danger', $exception->getMessage());
                }
            }
        }
        return $this->render('index', ['recipient' => ['name' => Configuration::value('recipient.name'), 'document' => Configuration::value('recipient.document')]]);
    }
}
