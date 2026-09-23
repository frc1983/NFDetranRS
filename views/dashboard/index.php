<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $metrics */
/** @var app\models\AuditEvent[] $activities */

$this->title = 'Dashboard';
$metrics = $metrics ?? [];
$activities = $activities ?? [];
$cards = $metrics;
?>
<section class="page-head">
    <div>
        <span class="page-kicker">Visão geral · dados persistidos</span>
        <h1 class="page-title">Bom trabalho, <?= Html::encode(Yii::$app->user->identity->nome) ?></h1>
        <p class="page-subtitle">Acompanhe o estoque, as vendas e a emissão fiscal em um só lugar.</p>
    </div>
    <a class="btn btn-primary" href="<?= Url::to(['/venda/create']) ?>">+ Iniciar nova venda</a>
</section>

<section class="metric-grid" aria-label="Indicadores principais">
    <?php foreach ($cards as $card): ?>
        <article class="card-surface metric-card">
            <span class="metric-label"><?= Html::encode($card['label'] ?? '') ?></span>
            <strong class="metric-value"><?= Html::encode($card['value'] ?? '—') ?></strong>
            <span class="metric-trend <?= Html::encode($card['class'] ?? '') ?>"><?= Html::encode($card['trend'] ?? '') ?></span>
        </article>
    <?php endforeach; ?>
</section>

<section class="content-grid">
    <article class="card-surface section-card">
        <header class="section-head">
            <div><h2>Atividade recente</h2><p>Eventos da operação serão exibidos após o primeiro uso.</p></div>
            <a class="btn btn-sm btn-outline-primary" href="<?= Url::to(['/auditoria/index']) ?>">Ver auditoria</a>
        </header>
        <div class="section-body">
            <?php if (!$activities): ?><div class="empty-state">
                <div class="empty-icon">↗</div>
                <h3>Nenhuma atividade registrada</h3>
                <p>Sincronizações, vendas e documentos fiscais aparecerão aqui em ordem cronológica.</p>
            </div><?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Ação</th><th>Usuário</th><th>Data</th></tr></thead><tbody><?php foreach ($activities as $activity): ?><tr><td><?= Html::encode($activity->acao) ?></td><td><?= Html::encode($activity->user?->nome ?? 'Sistema') ?></td><td><?= Html::encode($activity->created_at) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </div>
    </article>
    <aside class="card-surface section-card">
        <header class="section-head"><div><h2>Preparação do ambiente</h2><p>Etapas para iniciar a operação.</p></div></header>
        <div class="section-body timeline">
            <div class="timeline-item"><span class="timeline-dot"></span><span class="timeline-copy"><strong>Aplicação e banco</strong><small>Autenticação e persistência ativas</small></span><span class="badge-soft">Concluído</span></div>
            <div class="timeline-item"><span class="timeline-dot"></span><span class="timeline-copy"><strong>Credenciais GID</strong><small>Configure por variável segura</small></span><span class="badge-soft warning">Pendente</span></div>
            <div class="timeline-item"><span class="timeline-dot"></span><span class="timeline-copy"><strong>Certificado SEFAZ</strong><small>Armazene fora do código-fonte</small></span><span class="badge-soft warning">Pendente</span></div>
        </div>
    </aside>
</section>
