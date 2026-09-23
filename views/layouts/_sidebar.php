<?php

use yii\helpers\Html;
use yii\helpers\Url;

$currentRoute = Yii::$app->controller->route;
$items = [
    ['label' => 'Dashboard', 'route' => 'dashboard/index', 'url' => ['/dashboard/index'], 'icon' => '▦', 'permission' => 'dashboard.visualizar'],
    ['label' => 'Estoque GID', 'route' => 'estoque/index', 'url' => ['/estoque/index'], 'icon' => '◇', 'permission' => 'estoque.visualizar'],
    ['label' => 'Nova venda', 'route' => 'venda/create', 'url' => ['/venda/create'], 'icon' => '+', 'permission' => 'venda.criar'],
    ['label' => 'Vendas', 'route' => 'venda/index', 'url' => ['/venda/index'], 'icon' => '↗', 'permission' => 'venda.visualizar'],
    ['label' => 'NF-e', 'route' => 'nfe/index', 'url' => ['/nfe/index'], 'icon' => '≡', 'permission' => 'nfe.visualizar'],
    ['label' => 'Auditoria', 'route' => 'auditoria/index', 'url' => ['/auditoria/index'], 'icon' => '◎', 'permission' => 'auditoria.visualizar'],
    ['label' => 'Configurações', 'route' => 'configuracao/index', 'url' => ['/configuracao/index'], 'icon' => '⚙', 'permission' => 'configuracao.gerenciar'],
];
?>
<aside class="app-sidebar" id="app-sidebar" aria-label="Navegação principal">
    <div class="sidebar-brand">
        <a href="<?= Url::to(['/dashboard/index']) ?>" class="brand-link" aria-label="NFDetranRS - início">
            <span class="brand-mark">NF</span>
            <span><strong>NFDetran</strong><small>Rio Grande do Sul</small></span>
        </a>
        <button class="btn btn-icon d-lg-none sidebar-close" type="button" data-sidebar-close aria-label="Fechar menu">×</button>
    </div>
    <nav class="sidebar-nav">
        <span class="sidebar-label">Principal</span>
        <?php foreach ($items as $index => $item): ?>
            <?php if (!Yii::$app->user->can($item['permission'])) continue; ?>
            <?php if ($index === 5): ?><span class="sidebar-label sidebar-label-spaced">Administração</span><?php endif; ?>
            <a class="nav-item <?= $currentRoute === $item['route'] ? 'active' : '' ?>"
               href="<?= Url::to($item['url']) ?>"
               <?= $currentRoute === $item['route'] ? 'aria-current="page"' : '' ?>>
                <span class="nav-icon" aria-hidden="true"><?= Html::encode($item['icon']) ?></span>
                <span><?= Html::encode($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-status">
        <span class="status-indicator status-indicator-pending"></span>
        <span><strong>Integrações pendentes</strong><small>GID e SEFAZ não configurados</small></span>
    </div>
</aside>
