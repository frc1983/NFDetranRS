<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\StockItem $item */
$this->title = 'Classificação fiscal';
?>
<section class="page-head"><div><span class="page-kicker">NF-e</span><h1 class="page-title">Classificação fiscal</h1><p class="page-subtitle"><?= Html::encode($item->descricao) ?> — GID <?= Html::encode($item->gid_id) ?></p></div></section>
<section class="card-surface form-card">
    <div class="alert alert-warning">Informe somente códigos confirmados pelo contador. O sistema não presume tributação para peças usadas.</div>
    <?= Html::beginForm(['/estoque/fiscal', 'id' => $item->id], 'post') ?>
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="ncm">NCM</label><input class="form-control" id="ncm" name="ncm" maxlength="8" required value="<?= Html::encode($item->ncm) ?>"></div>
        <div class="col-md-4"><label class="form-label" for="cest">CEST, se aplicável</label><input class="form-control" id="cest" name="cest" maxlength="7" value="<?= Html::encode($item->cest) ?>"></div>
        <div class="col-md-4"><label class="form-label" for="cfop">CFOP</label><input class="form-control" id="cfop" name="cfop" maxlength="4" required value="<?= Html::encode($item->cfop) ?>"></div>
        <div class="col-md-3"><label class="form-label" for="unit">Unidade</label><input class="form-control" id="unit" name="unit" maxlength="6" required value="<?= Html::encode($item->unidade_comercial ?: 'UN') ?>"></div>
        <div class="col-md-3"><label class="form-label" for="origin">Origem</label><input class="form-control" id="origin" name="origin" maxlength="1" required value="<?= Html::encode($item->origem_mercadoria) ?>"></div>
        <div class="col-md-2"><label class="form-label" for="icms-cst">CST/CSOSN</label><input class="form-control" id="icms-cst" name="icms_cst" maxlength="3" required value="<?= Html::encode($item->icms_cst) ?>"></div>
        <div class="col-md-2"><label class="form-label" for="pis-cst">PIS CST</label><input class="form-control" id="pis-cst" name="pis_cst" maxlength="2" required value="<?= Html::encode($item->pis_cst) ?>"></div>
        <div class="col-md-2"><label class="form-label" for="cofins-cst">COFINS CST</label><input class="form-control" id="cofins-cst" name="cofins_cst" maxlength="2" required value="<?= Html::encode($item->cofins_cst) ?>"></div>
    </div>
    <div class="form-actions mt-4"><?= Html::submitButton('Salvar classificação', ['class' => 'btn btn-primary']) ?> <?= Html::a('Cancelar', ['/estoque/index'], ['class' => 'btn btn-outline-primary']) ?></div>
    <?= Html::endForm() ?>
</section>
