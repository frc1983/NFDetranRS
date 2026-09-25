<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\StockItem[] $items */

$this->title = 'Estoque GID';
$items = $items ?? [];
$gidCertificateConfigured = !empty(Yii::$app->params['gid']['certificatePath']) && is_file(Yii::$app->params['gid']['certificatePath']);
?>
<section class="page-head">
    <div><span class="page-kicker">Integração GID</span><h1 class="page-title">Estoque</h1><p class="page-subtitle">Consulte e selecione itens disponíveis para comercialização.</p></div>
    <?php if (Yii::$app->user->can('estoque.sincronizar')): ?>
        <?= Html::beginForm(['/estoque/sync'], 'post', ['class' => 'd-inline']) ?>
        <?= Html::submitButton('Sincronizar GID', ['class' => 'btn btn-primary', 'data' => ['confirm' => 'Sincronizar agora o estoque da Oficina da Moto com o GID?']]) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>
</section>

<form class="card-surface filter-bar" method="get" role="search">
    <?= Html::hiddenInput('r', Yii::$app->controller->route) ?>
    <div class="filter-grow"><label class="visually-hidden" for="stock-search">Buscar item</label><input class="form-control" id="stock-search" name="q" value="<?= Html::encode($search ?? '') ?>" placeholder="Buscar por código, identificador ou descrição"></div>
    <div><label class="visually-hidden" for="stock-status">Situação</label><select class="form-select" id="stock-status" name="status"><option value="">Todas as situações</option><option value="disponivel" <?= ($status ?? '') === 'disponivel' ? 'selected' : '' ?>>Disponível</option><option value="indisponivel" <?= ($status ?? '') === 'indisponivel' ? 'selected' : '' ?>>Indisponível</option></select></div>
    <button class="btn btn-outline-primary" type="submit">Filtrar</button>
</form>

<section class="card-surface section-card">
    <header class="section-head"><div><h2>Itens sincronizados</h2><p><?= count($items) ?> item(ns) encontrado(s)</p></div><span class="badge-soft <?= $gidCertificateConfigured ? '' : 'warning' ?>"><?= $gidCertificateConfigured ? 'GID configurado' : 'Aguardando certificado' ?></span></header>
    <?php if (!$items): ?>
        <div class="empty-state"><div class="empty-icon">◇</div><h3>Estoque ainda não sincronizado</h3><p>Configure as credenciais do GID para importar os itens. Nenhum dado demonstrativo será confundido com estoque real.</p></div>
    <?php else: ?>
        <div class="table-responsive"><table class="table"><thead><tr><th>Código</th><th>Peça</th><th>Veículo</th><th>Placa</th><th>Situação</th><th>Fiscal</th><th>Ações</th></tr></thead><tbody>
        <?php foreach ($items as $item): ?><?php $fiscalReady = $item->ncm && $item->cfop && $item->unidade_comercial && $item->origem_mercadoria !== null && $item->icms_cst && $item->pis_cst && $item->cofins_cst; ?><tr><td><strong><?= Html::encode($item->gid_id) ?></strong></td><td><?= Html::encode($item->descricao) ?></td><td><?= Html::encode(trim((string) $item->marca . ' ' . (string) $item->modelo . ' ' . (string) $item->ano_modelo)) ?></td><td><?= Html::encode($item->placa_veiculo ?: '—') ?></td><td><span class="badge-soft <?= (float) $item->quantidade_disponivel > 0 ? '' : 'warning' ?>"><?= (float) $item->quantidade_disponivel > 0 ? 'Disponível' : 'Indisponível' ?></span></td><td><span class="badge-soft <?= $fiscalReady ? '' : 'warning' ?>"><?= $fiscalReady ? 'Configurado' : 'Pendente' ?></span></td><td><?php if (Yii::$app->user->can('estoque.sincronizar')): ?><?= Html::a('Classificar', ['/estoque/fiscal', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?><?php endif; ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
