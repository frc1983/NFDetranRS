<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $content */

$this->registerCssFile('@web/css/app.css', ['depends' => [yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/app.js', ['depends' => [yii\web\JqueryAsset::class]]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title ? $this->title . ' · NFDetranRS' : 'NFDetranRS') ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="app-shell">
    <?= $this->render('_sidebar') ?>
    <div class="app-main">
        <header class="app-topbar">
            <button class="btn btn-icon d-lg-none" type="button" data-sidebar-toggle aria-label="Abrir menu" aria-controls="app-sidebar" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar-context">
                <span class="eyebrow">Operação fiscal</span>
                <strong>Notas Detran RS</strong>
            </div>
            <div class="topbar-actions">
                <div class="user-chip" aria-label="Usuário atual">
                    <span class="user-avatar"><?= Html::encode(mb_strtoupper(mb_substr((string) Yii::$app->user->identity->nome, 0, 2))) ?></span>
                    <span class="d-none d-sm-flex flex-column">
                        <strong><?= Html::encode(Yii::$app->user->identity->nome) ?></strong>
                        <small><?= Html::encode(Yii::$app->user->identity->email) ?></small>
                    </span>
                </div>
                <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('Sair', ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <?= Html::endForm() ?>
            </div>
        </header>
        <main class="app-content" id="main-content">
            <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
                <div class="alert alert-<?= Html::encode($type) ?>" role="alert"><?= Html::encode($message) ?></div>
            <?php endforeach; ?>
            <div class="demo-banner" role="status"><strong>Ambiente local</strong><span>Dados persistidos; integrações GID e SEFAZ ainda não configuradas.</span></div>
            <?= $content ?>
        </main>
        <footer class="app-footer">
            <span>NFDetranRS</span>
            <span>Integração segura com GID e SEFAZ</span>
        </footer>
    </div>
</div>
<div class="sidebar-backdrop" data-sidebar-close></div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
