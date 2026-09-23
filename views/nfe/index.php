<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $documents */

$this->title = 'NF-e';
$documents = $documents ?? [];
?>
<section class="page-head"><div><span class="page-kicker">Documentos fiscais</span><h1 class="page-title">NF-e</h1><p class="page-subtitle">Monitore autorização, rejeições e disponibilidade de XML e DANFE.</p></div></section>
<section class="metric-grid">
    <article class="card-surface metric-card"><span class="metric-label">Autorizadas</span><strong class="metric-value">0</strong><span class="metric-trend">Nenhum documento</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Processando</span><strong class="metric-value">0</strong><span class="metric-trend">Fila vazia</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Rejeitadas</span><strong class="metric-value">0</strong><span class="metric-trend">Sem ocorrências</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Contingência</span><strong class="metric-value">Inativa</strong><span class="metric-trend warning">Ambiente demonstrativo</span></article>
</section>
<section class="card-surface section-card">
    <header class="section-head"><div><h2>Documentos emitidos</h2><p><?= count($documents) ?> documento(s)</p></div><span class="badge-soft warning">SEFAZ não configurada</span></header>
    <?php if (!$documents): ?><div class="empty-state"><div class="empty-icon">≡</div><h3>Nenhuma NF-e emitida</h3><p>Os documentos serão gerados a partir das vendas confirmadas após a configuração segura do certificado.</p></div>
    <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Número</th><th>Série</th><th>Venda</th><th>Chave de acesso</th><th>Situação</th></tr></thead><tbody><?php foreach ($documents as $document): ?><tr><td><strong><?= Html::encode($document['number'] ?? '—') ?></strong></td><td><?= Html::encode($document['series'] ?? '—') ?></td><td><?= Html::encode($document['sale'] ?? '—') ?></td><td><?= Html::encode($document['accessKey'] ?? '—') ?></td><td><span class="badge-soft"><?= Html::encode($document['status'] ?? 'Processando') ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
