<?php

use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Nova venda';
?>
<section class="page-head">
    <div><span class="page-kicker">Fluxo de venda</span><h1 class="page-title">Nova venda</h1><p class="page-subtitle">Selecione os itens; o sistema dividirá as NF-e automaticamente em lotes de até 100.</p></div>
</section>

<form class="card-surface form-card" method="post" action="" novalidate>
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
    <section class="form-section">
        <h2>Destinatário</h2><p>O destinatário é definido pela configuração operacional e não pode ser alterado nesta etapa.</p>
        <div class="row g-3">
            <div class="col-md-7"><label class="form-label" for="recipient-name">Razão social</label><input class="form-control" id="recipient-name" value="Destinatário fixo não configurado" readonly></div>
            <div class="col-md-5"><label class="form-label" for="recipient-doc">CNPJ</label><input class="form-control" id="recipient-doc" value="—" readonly></div>
        </div>
    </section>
    <section class="form-section">
        <h2>Itens da venda</h2><p>Os itens disponíveis serão carregados do estoque GID.</p>
        <div class="empty-state"><div class="empty-icon">+</div><h3>Nenhum item selecionado</h3><p>Sincronize o estoque e use a busca para adicionar itens à venda.</p><a class="btn btn-outline-primary" href="<?= Yii::$app->urlManager->createUrl(['/estoque/index']) ?>">Consultar estoque</a></div>
    </section>
    <section class="form-section">
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label" for="sale-note">Observação interna</label><textarea class="form-control" id="sale-note" name="note" rows="3" maxlength="500" placeholder="Opcional; não será enviada à SEFAZ"></textarea></div>
            <div class="col-md-4"><label class="form-label" for="sale-count">Resumo</label><input class="form-control" id="sale-count" value="0 itens · 0 NF-e previstas" readonly></div>
        </div>
    </section>
    <div class="form-actions"><a class="btn btn-light" href="<?= Yii::$app->urlManager->createUrl(['/venda/index']) ?>">Cancelar</a><button class="btn btn-primary" type="submit" disabled>Revisar venda</button></div>
</form>
