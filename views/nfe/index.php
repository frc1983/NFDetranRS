<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Invoice[] $documents */
/** @var array $counts */
/** @var array $nfe */
/** @var string[] $configurationErrors */

$this->title = 'NF-e';
$documents = $documents ?? [];
$counts = $counts ?? [];
$count = static fn(string $status): int => (int) ($counts[$status]['total'] ?? 0);
$configurationErrors = $configurationErrors ?? [];
$simulation = ($nfe['transport'] ?? 'mock') === 'mock';
?>
<section class="page-head"><div><span class="page-kicker">Documentos fiscais</span><h1 class="page-title">NF-e</h1><p class="page-subtitle">Monitore autorização, rejeições e disponibilidade de XML e DANFE.</p></div></section>
<section class="metric-grid">
    <article class="card-surface metric-card"><span class="metric-label">Autorizadas</span><strong class="metric-value"><?= $count('autorizada') ?></strong><span class="metric-trend">Documentos autorizados</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Processando</span><strong class="metric-value"><?= $count('pendente') + $count('processando') ?></strong><span class="metric-trend">Fila atual</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Rejeitadas</span><strong class="metric-value"><?= $count('rejeitada') ?></strong><span class="metric-trend">Exigem revisão</span></article>
    <article class="card-surface metric-card"><span class="metric-label">Canceladas</span><strong class="metric-value"><?= $count('cancelada') ?></strong><span class="metric-trend warning">Documentos cancelados</span></article>
</section>
<section class="card-surface section-card">
    <header class="section-head"><div><h2>Documentos emitidos</h2><p><?= count($documents) ?> documento(s)</p></div><span class="badge-soft <?= $configurationErrors === [] ? '' : 'warning' ?>"><?= $simulation ? 'Simulador local' : ($configurationErrors === [] ? 'SEFAZ configurada' : 'SEFAZ pendente') ?></span></header>
    <?php if ($configurationErrors !== []): ?><div class="alert alert-warning"><strong>Configuração fiscal incompleta.</strong><ul class="mb-0 mt-2"><?php foreach ($configurationErrors as $error): ?><li><?= Html::encode($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if (!$documents): ?><div class="empty-state"><div class="empty-icon">≡</div><h3>Nenhuma NF-e emitida</h3><p>Os documentos serão gerados a partir das vendas confirmadas após a configuração segura do certificado.</p></div>
    <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Número</th><th>Lote</th><th>Venda</th><th>Chave de acesso</th><th>Situação</th><th>Arquivos</th></tr></thead><tbody><?php foreach ($documents as $document): ?><tr><td><strong><?= Html::encode(($document->serie ?? 0) . '/' . ($document->numero ?? $document->id)) ?></strong></td><td><?= Html::encode($document->lote) ?></td><td><?= Html::encode($document->sale?->numero ?? '—') ?></td><td><small><?= Html::encode($document->chave_acesso ?: '—') ?></small></td><td><span class="badge-soft <?= $document->simulada ? 'warning' : '' ?>"><?= $document->simulada ? 'simulada — sem valor fiscal' : Html::encode($document->status) ?></span></td><td><?php if ($document->xml_caminho): ?><?= Html::a('XML', ['/nfe/download', 'id' => $document->id, 'type' => 'xml'], ['class' => 'btn btn-sm btn-outline-primary']) ?><?php endif; ?> <?php if ($document->danfe_caminho): ?><?= Html::a('DANFE', ['/nfe/download', 'id' => $document->id, 'type' => 'danfe'], ['class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener']) ?><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
