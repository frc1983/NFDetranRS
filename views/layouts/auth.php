<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $content */

$this->registerCssFile('@web/css/app.css', ['depends' => [yii\bootstrap5\BootstrapAsset::class]]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title . ' · NFDetranRS') ?></title>
    <?php $this->head() ?>
</head>
<body class="auth-body">
<?php $this->beginBody() ?>
<main class="auth-shell">
    <section class="auth-brand-panel">
        <span class="brand-mark">NF</span>
        <div><span class="page-kicker">Operação fiscal</span><h1>NFDetranRS</h1><p>Estoque GID, vendas, documentos fiscais e auditoria em uma operação segura.</p></div>
    </section>
    <section class="auth-form-panel">
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
            <div class="alert alert-<?= Html::encode($type) ?>"><?= Html::encode($message) ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </section>
</main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
