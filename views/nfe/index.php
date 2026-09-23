<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Invoice[] $documents */
/** @var array $counts */

$this->title = 'NF-e';
$documents = $documents ?? [];
$counts = $counts ?? [];
$count = static fn(string $status): int => (int) ($counts[$status]['total'] ?? 0);
?>
<section class="page-head"><div><span class="page-kicker">Documentos fiscais</span><h1 class="page-title">NF-e</h1><p class="page-subtitle">Monitore autorização, rejeições e disponibilidade de XML e DANFE.</p></div></section>
<section class="metric-grid">
    <article class="card-surface metric-card"><span class="metric-label">Autorizadas</span><strong class="metric-value"><?= $count('autorizada') ?></strong><span class="metric-trend">Documentos autorizados</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Processando</span><strong class="metric-value"><?= $count('pendente') + $count('processando') ?></strong><span class="metric-trend">Fila atual</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Rejeitadas</span><strong class="metric-value"><?= $count('rejeitada') ?></strong><span class="metric-trend">Exigem revisão</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Canceladas</span><strong class="metric-value"><?= $count('cancelada') ?></strong><span class="metric-trend warning">Documentos cancelados</span></article>
</section>
<section class="card-surface section-card">
    <header class="section-head"><div><h2>Documentos emitidos</h2><p><?= count($documents) ?> documento(s)</p></div><span class="badge-soft warning">SEFAZ não configurada</span></header>
    <?php if (!$documents): ?><div class="empty-state"><div class="empty-icon">≡</div><h3>Nenhuma NF-e emitida</h3><p>Os documentos serão gerados a partir das vendas confirmadas após a configuração segura do certificado.</p></div>
    <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>ID</th><th>Lote</th><th>Venda</th><th>Chave de acesso</th><th>Situação</th></tr></thead><tbody><?php foreach ($documents as $document): ?><tr><td><strong>#<?= Html::encode($document->id) ?></strong></td><td><?= Html::encode($document->lote) ?></td><td><?= Html::encode($document->sale?->numero ?? '—') ?></td><td><?= Html::encode($document->chave_acesso ?: '—') ?></td><td><span class="badge-soft"><?= Html::encode($document->status) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
