<?php
declare(strict_types=1);
namespace app\controllers;
final class VendaController extends BaseController { public function actionIndex(): string { return $this->render('index', ['sales' => []]); } public function actionCreate(): string { return $this->render('create', ['sale' => null, 'items' => []]); } }
