<?php
declare(strict_types=1);
namespace app\controllers;

use app\models\AuditEvent;
use app\models\LoginForm;
use app\models\SetupAdminForm;
use app\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

final class SiteController extends Controller
{
    public $layout = 'auth';

    public function behaviors(): array
    {
        return ['verbs' => ['class' => VerbFilter::class, 'actions' => ['logout' => ['POST']]]];
    }

    public function actions(): array
    {
        return ['error' => ['class' => \yii\web\ErrorAction::class]];
    }

    public function actionIndex(): Response
    {
        return $this->redirect(['/dashboard/index']);
    }

    public function actionLogin(): Response|string
    {
        if (User::find()->count() === 0) {
            return $this->redirect(['/site/setup']);
        }
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/dashboard/index']);
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack(['/dashboard/index']);
        }
        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }

    public function actionSetup(): Response|string
    {
        if (User::find()->count() > 0) {
            return $this->redirect(['/site/login']);
        }
        if (!in_array(Yii::$app->request->userIP, ['127.0.0.1', '::1'], true)) {
            throw new ForbiddenHttpException('A configuracao inicial so pode ser executada localmente.');
        }

        $model = new SetupAdminForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $user = new User(['nome' => trim($model->name), 'email' => mb_strtolower(trim($model->email)), 'status' => 'ativo']);
                $user->setPassword($model->password);
                $user->generateAuthKey();
                if (!$user->save()) {
                    throw new \RuntimeException('Nao foi possivel criar o administrador.');
                }
                $role = Yii::$app->authManager->getRole('administrador');
                if ($role === null) {
                    throw new \RuntimeException('Papel administrador nao encontrado. Execute as migrations.');
                }
                Yii::$app->authManager->assign($role, (string) $user->id);
                $audit = new AuditEvent([
                    'usuario_id' => $user->id,
                    'acao' => 'ADMINISTRADOR_INICIAL_CRIADO',
                    'entidade' => 'usuario',
                    'entidade_id' => (string) $user->id,
                    'dados_novos_hash' => hash('sha256', $user->email),
                    'ip_hash' => hash('sha256', (string) Yii::$app->request->userIP),
                    'user_agent_hash' => hash('sha256', (string) Yii::$app->request->userAgent),
                    'correlation_id' => Yii::$app->security->generateRandomString(36),
                ]);
                if (!$audit->save(false)) {
                    throw new \RuntimeException('Nao foi possivel registrar a auditoria inicial.');
                }
                $transaction->commit();
                Yii::$app->user->login($user);
                Yii::$app->session->setFlash('success', 'Administrador criado com sucesso.');
                return $this->redirect(['/dashboard/index']);
            } catch (\Throwable $exception) {
                $transaction->rollBack();
                $model->addError('email', $exception->getMessage());
            }
        }
        $model->password = $model->passwordConfirmation = '';
        return $this->render('setup', ['model' => $model]);
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->redirect(['/site/login']);
    }
}
