<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $metrics */
/** @var array $alerts */

$this->title = 'Dashboard';
$metrics = $metrics ?? [];
$alerts = $alerts ?? [];
$demoMetrics = [
    ['label' => 'Itens disponíveis', 'value' => '—', 'trend' => 'Aguardando sincronização GID', 'class' => ''],
    ['label' => 'Vendas hoje', 'value' => '0', 'trend' => 'Nenhuma venda registrada', 'class' => ''],
    ['label' => 'NF-e em processamento', 'value' => '0', 'trend' => 'Fila vazia', 'class' => ''],
    ['label' => 'Alertas operacionais', 'value' => '2', 'trend' => 'Configuração inicial pendente', 'class' => 'warning'],
];
$cards = $metrics ?: $demoMetrics;
?>
<section class="page-head">
    <div>
        <span class="page-kicker">Visão geral · ambiente demonstrativo</span>
        <h1 class="page-title">Bom trabalho, Administrador</h1>
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
            <div class="empty-state">
                <div class="empty-icon">↗</div>
                <h3>Nenhuma atividade registrada</h3>
                <p>Sincronizações, vendas e documentos fiscais aparecerão aqui em ordem cronológica.</p>
            </div>
        </div>
    </article>
    <aside class="card-surface section-card">
        <header class="section-head"><div><h2>Preparação do ambiente</h2><p>Etapas para iniciar a operação.</p></div></header>
        <div class="section-body timeline">
            <div class="timeline-item"><span class="timeline-dot"></span><span class="timeline-copy"><strong>Estrutura do sistema</strong><small>Interface inicial disponível</small></span><span class="badge-soft">Concluído</span></div>
            <div class="timeline-item"><span class="timeline-dot"></span><span class="timeline-copy"><strong>Credenciais GID</strong><small>Configure por variável segura</small></span><span class="badge-soft warning">Pendente</span></div>
            <div class="timeline-item"><span class="timeline-dot"></span><span class="timeline-copy"><strong>Certificado SEFAZ</strong><small>Armazene fora do código-fonte</small></span><span class="badge-soft warning">Pendente</span></div>
        </div>
    </aside>
</section>
