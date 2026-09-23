<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $items */

$this->title = 'Estoque GID';
$items = $items ?? [];
?>
<section class="page-head">
    <div><span class="page-kicker">Integração GID</span><h1 class="page-title">Estoque</h1><p class="page-subtitle">Consulte e selecione itens disponíveis para comercialização.</p></div>
    <button class="btn btn-primary" type="button" disabled title="Disponível após configurar o GID">Sincronizar GID</button>
</section>

<form class="card-surface filter-bar" method="get" role="search">
    <?= Html::hiddenInput('r', Yii::$app->controller->route) ?>
    <div class="filter-grow"><label class="visually-hidden" for="stock-search">Buscar item</label><input class="form-control" id="stock-search" name="q" placeholder="Buscar por código, placa ou descrição"></div>
    <div><label class="visually-hidden" for="stock-status">Situação</label><select class="form-select" id="stock-status" name="status"><option value="">Todas as situações</option><option>Disponível</option><option>Reservado</option><option>Vendido</option></select></div>
    <button class="btn btn-outline-primary" type="button" disabled title="Filtro previsto para a proxima etapa">Filtrar (demo)</button>
</form>

<section class="card-surface section-card">
    <header class="section-head"><div><h2>Itens sincronizados</h2><p><?= count($items) ?> item(ns) encontrado(s)</p></div><span class="badge-soft warning">Integração pendente</span></header>
    <?php if (!$items): ?>
        <div class="empty-state"><div class="empty-icon">◇</div><h3>Estoque ainda não sincronizado</h3><p>Configure as credenciais do GID para importar os itens. Nenhum dado demonstrativo será confundido com estoque real.</p></div>
    <?php else: ?>
        <div class="table-responsive"><table class="table"><thead><tr><th>Código</th><th>Descrição</th><th>Identificador</th><th>Situação</th><th>Atualizado em</th></tr></thead><tbody>
        <?php foreach ($items as $item): ?><tr><td><strong><?= Html::encode($item['code'] ?? '—') ?></strong></td><td><?= Html::encode($item['description'] ?? '—') ?></td><td><?= Html::encode($item['identifier'] ?? '—') ?></td><td><span class="badge-soft"><?= Html::encode($item['status'] ?? 'Disponível') ?></span></td><td><?= Html::encode($item['updatedAt'] ?? '—') ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
