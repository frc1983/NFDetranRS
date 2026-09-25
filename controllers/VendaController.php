<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\Configuration;
use app\models\AuditEvent;
use app\models\Sale;
use app\models\SaleItem;
use app\models\StockItem;
use Yii;

final class VendaController extends BaseController
{
    protected string $permission = 'venda.visualizar';

    protected function permissionForAction(string $actionId): string
    {
        return $actionId === 'create' ? 'venda.criar' : $this->permission;
    }

    public function actionIndex(): string
    {
        $query = Sale::find()->with(['items', 'invoices'])->orderBy(['created_at' => SORT_DESC]);
        $search = trim((string) Yii::$app->request->get('q', ''));
        if ($search !== '') {
            $query->andWhere(['like', 'numero', $search]);
        }
        return $this->render('index', ['sales' => $query->limit(100)->all(), 'search' => $search]);
    }

    public function actionCreate(): \yii\web\Response|string
    {
        $recipient = [
            'name' => Configuration::value('recipient.name'), 'document' => Configuration::value('recipient.document'),
            'state_registration' => Configuration::value('recipient.state_registration'),
            'street' => Configuration::value('recipient.street'), 'number' => Configuration::value('recipient.number'),
            'complement' => Configuration::value('recipient.complement'), 'district' => Configuration::value('recipient.district'),
            'city_code' => Configuration::value('recipient.city_code'), 'city' => Configuration::value('recipient.city'),
            'state' => Configuration::value('recipient.state'), 'postal_code' => Configuration::value('recipient.postal_code'),
            'email' => Configuration::value('recipient.email'), 'phone' => Configuration::value('recipient.phone'),
        ];
        $items = StockItem::find()->where(['>', 'quantidade_disponivel', 0])->orderBy(['descricao' => SORT_ASC])->limit(200)->all();

        if (Yii::$app->request->isPost) {
            $ids = array_values(array_unique(array_filter(array_map('intval', (array) Yii::$app->request->post('items', [])))));
            if (!$recipient['name'] || !$recipient['document'] || !$recipient['street'] || !$recipient['city_code']) {
                Yii::$app->session->setFlash('danger', 'Configure o destinatario fiscal completo antes de criar uma venda.');
            } elseif (!$ids) {
                Yii::$app->session->setFlash('danger', 'Selecione pelo menos um item disponivel.');
            } else {
                $selected = StockItem::find()->where(['id' => $ids])->andWhere(['>', 'quantidade_disponivel', 0])->all();
                if (count($selected) !== count($ids)) {
                    Yii::$app->session->setFlash('danger', 'Um ou mais itens nao estao mais disponiveis. Atualize a pagina.');
                } else {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $correlationId = $this->uuidV4();
                        $totalCents = array_sum(array_map(static fn(StockItem $item): int => (int) round((float) $item->valor_unitario * 100), $selected));
                        $sale = new Sale([
                            'numero' => 'VEN-' . date('Ymd-His') . '-' . random_int(1000, 9999),
                            'status' => 'rascunho',
                            'destinatario_documento' => $recipient['document'],
                            'destinatario_nome' => $recipient['name'],
                            'destinatario_ie' => $recipient['state_registration'],
                            'destinatario_logradouro' => $recipient['street'],
                            'destinatario_numero' => $recipient['number'],
                            'destinatario_complemento' => $recipient['complement'],
                            'destinatario_bairro' => $recipient['district'],
                            'destinatario_municipio_codigo' => $recipient['city_code'],
                            'destinatario_municipio' => $recipient['city'],
                            'destinatario_uf' => $recipient['state'],
                            'destinatario_cep' => $recipient['postal_code'],
                            'destinatario_email' => $recipient['email'],
                            'destinatario_telefone' => $recipient['phone'],
                            'valor_total' => number_format($totalCents / 100, 2, '.', ''),
                            'idempotency_key' => Yii::$app->security->generateRandomString(64),
                            'correlation_id' => $correlationId,
                            'created_by' => Yii::$app->user->id,
                        ]);
                        if (!$sale->save(false)) {
                            throw new \RuntimeException('Nao foi possivel salvar a venda.');
                        }
                        foreach ($selected as $item) {
                            (new SaleItem([
                                'venda_id' => $sale->id,
                                'estoque_gid_id' => $item->id,
                                'quantidade' => 1,
                                'valor_unitario' => $item->valor_unitario,
                                'valor_total' => $item->valor_unitario,
                            ]))->save(false);
                        }
                        (new AuditEvent([
                            'usuario_id' => Yii::$app->user->id,
                            'acao' => 'VENDA_CRIADA',
                            'entidade' => 'venda',
                            'entidade_id' => (string) $sale->id,
                            'dados_novos_hash' => hash('sha256', $sale->numero . '|' . implode(',', $ids)),
                            'ip_hash' => hash('sha256', (string) Yii::$app->request->userIP),
                            'user_agent_hash' => hash('sha256', (string) Yii::$app->request->userAgent),
                            'correlation_id' => $correlationId,
                        ]))->save(false);
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', "Venda {$sale->numero} criada em rascunho.");
                        return $this->redirect(['/venda/index']);
                    } catch (\Throwable $exception) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('danger', $exception->getMessage());
                    }
                }
            }
        }

        return $this->render('create', ['recipient' => $recipient, 'items' => $items]);
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
