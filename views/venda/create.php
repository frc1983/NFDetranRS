<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $recipient */
/** @var app\models\StockItem[] $items */

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
            <div class="col-md-7"><label class="form-label" for="recipient-name">Razão social</label><input class="form-control" id="recipient-name" value="<?= Html::encode($recipient['name'] ?: 'Destinatário fixo não configurado') ?>" readonly></div>
            <div class="col-md-5"><label class="form-label" for="recipient-doc">CNPJ</label><input class="form-control" id="recipient-doc" value="<?= Html::encode($recipient['document'] ?: '—') ?>" readonly></div>
        </div>
    </section>
    <section class="form-section">
        <h2>Itens da venda</h2><p>Os itens disponíveis serão carregados do estoque GID.</p>
        <?php if (!$items): ?><div class="empty-state"><div class="empty-icon">+</div><h3>Nenhum item disponível</h3><p>Sincronize o estoque GID antes de preparar uma venda.</p><a class="btn btn-outline-primary" href="<?= Yii::$app->urlManager->createUrl(['/estoque/index']) ?>">Consultar estoque</a></div>
        <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th></th><th>Código</th><th>Descrição</th><th>Quantidade</th><th>Valor</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><input type="checkbox" name="items[]" value="<?= Html::encode($item->id) ?>" data-sale-item aria-label="Selecionar <?= Html::encode($item->codigo) ?>"></td><td><?= Html::encode($item->codigo) ?></td><td><?= Html::encode($item->descricao) ?></td><td><?= Html::encode($item->quantidade_disponivel) ?></td><td>R$ <?= number_format((float) $item->valor_unitario, 2, ',', '.') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    </section>
    <section class="form-section">
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label" for="sale-note">Observação interna</label><textarea class="form-control" id="sale-note" name="note" rows="3" maxlength="500" placeholder="Opcional; não será enviada à SEFAZ"></textarea></div>
            <div class="col-md-4"><label class="form-label" for="sale-count">Resumo</label><input class="form-control" id="sale-count" data-sale-summary value="0 itens · 0 NF-e previstas" readonly></div>
        </div>
    </section>
    <div class="form-actions"><a class="btn btn-light" href="<?= Yii::$app->urlManager->createUrl(['/venda/index']) ?>">Cancelar</a><button class="btn btn-primary" type="submit" <?= (!$items || empty($recipient['name']) || empty($recipient['document'])) ? 'disabled' : '' ?>>Criar rascunho</button></div>
</form>
