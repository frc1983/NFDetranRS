<?php
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var app\models\SetupAdminForm $model */
$this->title = 'Configuração inicial';
?>
<div class="auth-card">
    <span class="page-kicker">Primeiro acesso local</span>
    <h2>Criar administrador</h2>
    <p>Esta página deixa de ficar disponível assim que o primeiro usuário é criado.</p>
    <?php $form = ActiveForm::begin(['id' => 'setup-form']); ?>
        <?= $form->field($model, 'name')->textInput(['autofocus' => true, 'autocomplete' => 'name'])->label('Nome') ?>
        <?= $form->field($model, 'email')->input('email', ['autocomplete' => 'username'])->label('E-mail') ?>
        <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'new-password'])->label('Senha (mínimo de 12 caracteres)') ?>
        <?= $form->field($model, 'passwordConfirmation')->passwordInput(['autocomplete' => 'new-password'])->label('Confirmar senha') ?>
        <?= Html::submitButton('Criar administrador', ['class' => 'btn btn-primary w-100']) ?>
    <?php ActiveForm::end(); ?>
</div>
