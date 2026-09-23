<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $events */

$this->title = 'Auditoria';
$events = $events ?? [];
$rows = $events;
?>
<section class="page-head">
    <div><span class="page-kicker">Rastreabilidade</span><h1 class="page-title">Auditoria</h1><p class="page-subtitle">Consulte eventos de usuários, integrações e documentos sem alterar o histórico.</p></div>
    <button class="btn btn-outline-primary" type="button" disabled>Exportar relatório</button>
</section>
<section class="metric-grid">
    <article class="card-surface metric-card"><span class="metric-label">Eventos hoje</span><strong class="metric-value"><?= count($events) ?></strong><span class="metric-trend">Registros reais recebidos</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Falhas de integração</span><strong class="metric-value"><?= count(array_filter($events, static fn(array $event): bool => $event['status'] === 'erro')) ?></strong><span class="metric-trend">Eventos persistidos</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Ações de usuários</span><strong class="metric-value"><?= count(array_filter($events, static fn(array $event): bool => $event['module'] !== 'GID' && $event['module'] !== 'SEFAZ')) ?></strong><span class="metric-trend">Registros persistidos</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Retenção</span><strong class="metric-value">Imutável</strong><span class="metric-trend">Política prevista no sistema</span></article>
</section>
<form class="card-surface filter-bar" method="get" role="search">
    <?= Html::hiddenInput('r', Yii::$app->controller->route) ?>
    <div class="filter-grow"><label class="visually-hidden" for="audit-search">Buscar evento</label><input class="form-control" id="audit-search" name="q" value="<?= Html::encode($search ?? '') ?>" placeholder="Buscar por ação ou usuário"></div>
    <div><label class="visually-hidden" for="audit-module">Módulo</label><select class="form-select" id="audit-module" name="module"><option value="">Todos os módulos</option><?php foreach (['GID', 'SEFAZ', 'VENDA', 'USUARIO', 'CONFIGURACAO'] as $option): ?><option value="<?= $option ?>" <?= ($module ?? '') === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select></div>
    <div><label class="visually-hidden" for="audit-period">Período</label><select class="form-select" id="audit-period" name="period"><?php foreach (['Hoje', 'Últimos 7 dias', 'Últimos 30 dias'] as $option): ?><option <?= ($period ?? 'Hoje') === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select></div>
    <button class="btn btn-outline-primary" type="submit">Aplicar</button>
</form>
<section class="card-surface section-card">
    <header class="section-head"><div><h2>Eventos registrados</h2><p>Clique em um evento para inspecionar os detalhes.</p></div></header>
    <div class="table-responsive"><table class="table"><thead><tr><th>Evento</th><th>Usuário</th><th>Módulo</th><th>Data/hora</th><th>Tipo</th></tr></thead><tbody>
    <?php if (!$rows): ?><tr><td colspan="5" class="text-center text-muted py-5">Nenhum evento registrado.</td></tr><?php endif; ?>
    <?php foreach ($rows as $event): ?><tr class="audit-row" tabindex="0" role="button" data-audit-detail data-event="<?= Html::encode($event['event'] ?? '') ?>" data-user="<?= Html::encode($event['user'] ?? '') ?>" data-date="<?= Html::encode($event['date'] ?? '') ?>"><td><strong><?= Html::encode($event['event'] ?? '—') ?></strong></td><td><?= Html::encode($event['user'] ?? '—') ?></td><td><?= Html::encode($event['module'] ?? '—') ?></td><td><?= Html::encode($event['date'] ?? '—') ?></td><td><span class="badge-soft info"><?= Html::encode($event['status'] ?? 'Informativo') ?></span></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>

<aside class="audit-drawer" data-audit-drawer aria-hidden="true" aria-label="Detalhes do evento">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><span class="page-kicker">Evento de auditoria</span><h2 class="h5 mb-0">Detalhes</h2></div><button class="btn btn-icon" type="button" data-drawer-close aria-label="Fechar detalhes">×</button></div>
    <div class="drawer-field"><small>Evento</small><strong data-field="event">—</strong></div>
    <div class="drawer-field"><small>Usuário ou origem</small><span data-field="user">—</span></div>
    <div class="drawer-field"><small>Data e hora</small><span data-field="date">—</span></div>
    <div class="drawer-field"><small>Integridade</small><span>Registro somente leitura</span></div>
    <p class="text-muted small mt-4">A carga útil técnica completa será apresentada quando o backend de auditoria estiver conectado.</p>
</aside>
<div class="drawer-backdrop" data-drawer-backdrop data-drawer-close></div>
