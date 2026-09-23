<?php
declare(strict_types=1);
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

abstract class BaseController extends Controller
{
    protected string $permission = '';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $permission = $this->permissionForAction($action->id);
        if ($permission !== '' && !Yii::$app->user->can($permission)) {
            throw new ForbiddenHttpException('Seu perfil nao possui permissao para esta operacao.');
        }

        return true;
    }

    protected function permissionForAction(string $actionId): string
    {
        return $this->permission;
    }
}
