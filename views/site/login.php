<?php
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var app\models\LoginForm $model */
$this->title = 'Entrar';
?>
<div class="auth-card">
    <span class="page-kicker">Acesso protegido</span>
    <h2>Entrar no sistema</h2>
    <p>Use as credenciais cadastradas pelo administrador.</p>
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <?= $form->field($model, 'email')->textInput(['autofocus' => true, 'autocomplete' => 'username'])->label('E-mail') ?>
        <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'current-password'])->label('Senha') ?>
        <?= $form->field($model, 'rememberMe')->checkbox()->label('Manter conectado por 7 dias') ?>
        <?= Html::submitButton('Entrar', ['class' => 'btn btn-primary w-100']) ?>
    <?php ActiveForm::end(); ?>
</div>
