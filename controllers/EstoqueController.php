<?php
declare(strict_types=1);
namespace app\controllers;
final class EstoqueController extends BaseController { public function actionIndex(): string { return $this->render('index', ['items' => []]); } }
