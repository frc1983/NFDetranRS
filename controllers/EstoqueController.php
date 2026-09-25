<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\StockItem;
use app\models\AuditEvent;
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
        return in_array($actionId, ['sync', 'fiscal'], true) ? 'estoque.sincronizar' : $this->permission;
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

    public function actionFiscal(int $id): \yii\web\Response|string
    {
        $item = StockItem::findOne($id);
        if ($item === null) {
            throw new \yii\web\NotFoundHttpException('Peca nao encontrada.');
        }
        if (Yii::$app->request->isPost) {
            $values = [
                'ncm' => preg_replace('/\D+/', '', (string) Yii::$app->request->post('ncm', '')) ?? '',
                'cest' => preg_replace('/\D+/', '', (string) Yii::$app->request->post('cest', '')) ?? '',
                'cfop' => preg_replace('/\D+/', '', (string) Yii::$app->request->post('cfop', '')) ?? '',
                'unidade_comercial' => strtoupper(trim((string) Yii::$app->request->post('unit', ''))),
                'origem_mercadoria' => trim((string) Yii::$app->request->post('origin', '')),
                'icms_cst' => preg_replace('/\D+/', '', (string) Yii::$app->request->post('icms_cst', '')) ?? '',
                'pis_cst' => preg_replace('/\D+/', '', (string) Yii::$app->request->post('pis_cst', '')) ?? '',
                'cofins_cst' => preg_replace('/\D+/', '', (string) Yii::$app->request->post('cofins_cst', '')) ?? '',
            ];
            if (strlen($values['ncm']) !== 8 || ($values['cest'] !== '' && strlen($values['cest']) !== 7)
                || strlen($values['cfop']) !== 4 || !preg_match('/^[A-Z0-9]{1,6}$/', $values['unidade_comercial'])
                || !preg_match('/^[0-8]$/', $values['origem_mercadoria']) || strlen($values['icms_cst']) !== 3
                || strlen($values['pis_cst']) !== 2 || strlen($values['cofins_cst']) !== 2) {
                Yii::$app->session->setFlash('danger', 'Revise NCM, CEST, CFOP, unidade, origem e CSTs. Nenhuma regra tributaria sera presumida.');
            } else {
                foreach ($values as $attribute => $value) {
                    $item->{$attribute} = $value === '' ? null : $value;
                }
                $item->save(false);
                (new AuditEvent([
                    'usuario_id' => Yii::$app->user->id, 'acao' => 'CLASSIFICACAO_FISCAL_ATUALIZADA',
                    'entidade' => 'estoque_gid', 'entidade_id' => (string) $item->id,
                    'dados_novos_hash' => hash('sha256', json_encode($values) ?: ''),
                    'correlation_id' => Yii::$app->security->generateRandomString(36),
                ]))->save(false);
                Yii::$app->session->setFlash('success', 'Classificacao fiscal salva. Valide os codigos com o contador antes de uso real.');
                return $this->redirect(['/estoque/index']);
            }
        }
        return $this->render('fiscal', ['item' => $item]);
    }
}
