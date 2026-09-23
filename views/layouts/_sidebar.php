<?php

use yii\helpers\Html;
use yii\helpers\Url;

$currentRoute = Yii::$app->controller->route;
$items = [
    ['label' => 'Dashboard', 'route' => 'dashboard/index', 'url' => ['/dashboard/index'], 'icon' => '▦'],
    ['label' => 'Estoque GID', 'route' => 'estoque/index', 'url' => ['/estoque/index'], 'icon' => '◇'],
    ['label' => 'Nova venda', 'route' => 'venda/create', 'url' => ['/venda/create'], 'icon' => '+'],
    ['label' => 'Vendas', 'route' => 'venda/index', 'url' => ['/venda/index'], 'icon' => '↗'],
    ['label' => 'NF-e', 'route' => 'nfe/index', 'url' => ['/nfe/index'], 'icon' => '≡'],
    ['label' => 'Auditoria', 'route' => 'auditoria/index', 'url' => ['/auditoria/index'], 'icon' => '◎'],
    ['label' => 'Configurações', 'route' => 'configuracao/index', 'url' => ['/configuracao/index'], 'icon' => '⚙'],
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
        <span><strong>Integrações pendentes</strong><small>Ambiente demonstrativo</small></span>
    </div>
</aside>
