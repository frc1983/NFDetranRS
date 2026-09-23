<?php
declare(strict_types=1);
namespace app\controllers;

use yii\web\Controller;
use yii\web\ForbiddenHttpException;

abstract class BaseController extends Controller
{
    public function beforeAction($action)
    {
        $request = \Yii::$app->request;
        $demo = getenv('APP_DEMO') === '1';
        $local = in_array($request->userIP, ['127.0.0.1', '::1'], true);
        if (!$demo || !$local || !$request->isGet) {
            throw new ForbiddenHttpException('Estrutura inicial: demonstracao local somente leitura. Autenticacao e operacoes persistentes ainda pendentes.');
        }
        return parent::beforeAction($action);
    }
}
