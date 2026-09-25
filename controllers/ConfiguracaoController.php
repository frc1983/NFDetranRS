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
            $values = [
                'recipient.name' => $name,
                'recipient.document' => $document,
                'recipient.state_registration' => preg_replace('/\D+/', '', (string) Yii::$app->request->post('recipient_state_registration', '')) ?? '',
                'recipient.street' => trim((string) Yii::$app->request->post('recipient_street', '')),
                'recipient.number' => trim((string) Yii::$app->request->post('recipient_number', '')),
                'recipient.complement' => trim((string) Yii::$app->request->post('recipient_complement', '')),
                'recipient.district' => trim((string) Yii::$app->request->post('recipient_district', '')),
                'recipient.city_code' => preg_replace('/\D+/', '', (string) Yii::$app->request->post('recipient_city_code', '')) ?? '',
                'recipient.city' => trim((string) Yii::$app->request->post('recipient_city', '')),
                'recipient.state' => strtoupper(trim((string) Yii::$app->request->post('recipient_state', 'RS'))),
                'recipient.postal_code' => preg_replace('/\D+/', '', (string) Yii::$app->request->post('recipient_postal_code', '')) ?? '',
                'recipient.email' => trim((string) Yii::$app->request->post('recipient_email', '')),
                'recipient.phone' => preg_replace('/\D+/', '', (string) Yii::$app->request->post('recipient_phone', '')) ?? '',
            ];
            if ($name === '' || !in_array(strlen($document), [11, 14], true)
                || $values['recipient.street'] === '' || $values['recipient.number'] === ''
                || $values['recipient.district'] === '' || strlen($values['recipient.city_code']) !== 7
                || $values['recipient.city'] === '' || !preg_match('/^[A-Z]{2}$/', $values['recipient.state'])
                || strlen($values['recipient.postal_code']) !== 8) {
                Yii::$app->session->setFlash('danger', 'Preencha CPF/CNPJ e o endereco fiscal completo do destinatario.');
            } else {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    foreach ($values as $key => $value) {
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
        return $this->render('index', [
            'recipient' => [
                'name' => Configuration::value('recipient.name'), 'document' => Configuration::value('recipient.document'),
                'state_registration' => Configuration::value('recipient.state_registration'),
                'street' => Configuration::value('recipient.street'), 'number' => Configuration::value('recipient.number'),
                'complement' => Configuration::value('recipient.complement'), 'district' => Configuration::value('recipient.district'),
                'city_code' => Configuration::value('recipient.city_code'), 'city' => Configuration::value('recipient.city'),
                'state' => Configuration::value('recipient.state'), 'postal_code' => Configuration::value('recipient.postal_code'),
                'email' => Configuration::value('recipient.email'), 'phone' => Configuration::value('recipient.phone'),
            ],
            'company' => Yii::$app->params['company'],
            'gid' => Yii::$app->params['gid'],
            'nfe' => Yii::$app->params['nfe'],
        ]);
    }
}
