<?php
use yii\helpers\Html;

/** @var string $name */
/** @var string $message */
$this->title = $name;
?>
<div class="auth-card"><span class="page-kicker">Erro</span><h2><?= Html::encode($name) ?></h2><p><?= nl2br(Html::encode($message)) ?></p></div>
