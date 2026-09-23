<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $sales */

$this->title = 'Vendas';
$sales = $sales ?? [];
?>
<section class="page-head">
    <div><span class="page-kicker">Operação comercial</span><h1 class="page-title">Vendas</h1><p class="page-subtitle">Acompanhe o processamento das vendas e seus documentos fiscais.</p></div>
    <a class="btn btn-primary" href="<?= Url::to(['/venda/create']) ?>">+ Nova venda</a>
</section>
<form class="card-surface filter-bar" method="get" role="search">
    <?= Html::hiddenInput('r', Yii::$app->controller->route) ?>
    <div class="filter-grow"><label class="visually-hidden" for="sale-search">Buscar venda</label><input class="form-control" id="sale-search" name="q" placeholder="Buscar por número ou protocolo"></div>
    <div><label class="visually-hidden" for="sale-period">Período</label><select class="form-select" id="sale-period" name="period"><option>Últimos 30 dias</option><option>Hoje</option><option>Últimos 7 dias</option></select></div>
    <button class="btn btn-outline-primary" type="button" disabled title="Filtro previsto para a proxima etapa">Filtrar (demo)</button>
</form>
<section class="card-surface section-card">
    <header class="section-head"><div><h2>Histórico de vendas</h2><p><?= count($sales) ?> registro(s)</p></div></header>
    <?php if (!$sales): ?><div class="empty-state"><div class="empty-icon">↗</div><h3>Nenhuma venda registrada</h3><p>Quando a primeira venda for criada, seu andamento aparecerá aqui.</p><a class="btn btn-primary" href="<?= Url::to(['/venda/create']) ?>">Criar primeira venda</a></div>
    <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Venda</th><th>Data</th><th>Itens</th><th>NF-e</th><th>Situação</th></tr></thead><tbody><?php foreach ($sales as $sale): ?><tr><td><strong><?= Html::encode($sale['number'] ?? '—') ?></strong></td><td><?= Html::encode($sale['date'] ?? '—') ?></td><td><?= Html::encode($sale['itemCount'] ?? 0) ?></td><td><?= Html::encode($sale['invoiceCount'] ?? 0) ?></td><td><span class="badge-soft <?= Html::encode($sale['statusClass'] ?? '') ?>"><?= Html::encode($sale['status'] ?? 'Em processamento') ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
