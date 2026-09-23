<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $recipient */
$this->title = 'Configurações';
$configured = !empty($recipient['name']) && !empty($recipient['document']);
?>
<section class="page-head"><div><span class="page-kicker">Administração</span><h1 class="page-title">Configurações</h1><p class="page-subtitle">Parâmetros operacionais persistidos no banco local.</p></div></section>
<section class="content-grid">
    <article class="card-surface form-card">
        <header class="mb-4"><h2 class="h5">Destinatário fixo</h2><p class="text-muted small">Esses dados serão copiados para todas as novas vendas.</p></header>
        <?= Html::beginForm(['/configuracao/index'], 'post') ?>
        <div class="mb-3"><label class="form-label" for="recipient-name">Razão social</label><input class="form-control" id="recipient-name" name="recipient_name" maxlength="190" required value="<?= Html::encode($recipient['name'] ?? '') ?>"></div>
        <div class="mb-3"><label class="form-label" for="recipient-document">CNPJ</label><input class="form-control" id="recipient-document" name="recipient_document" maxlength="18" inputmode="numeric" required value="<?= Html::encode($recipient['document'] ?? '') ?>"></div>
        <div class="form-actions"><?= Html::submitButton('Salvar configuração', ['class' => 'btn btn-primary']) ?></div>
        <?= Html::endForm() ?>
    </article>
    <aside class="card-surface section-card">
        <header class="section-head"><div><h2>Estado do ambiente</h2><p>Integrações e segurança.</p></div></header>
        <div class="section-body settings-list">
            <div class="settings-item"><div><h3>Destinatário fixo</h3><p>Dados persistidos e auditados.</p></div><span class="badge-soft <?= $configured ? '' : 'warning' ?>"><?= $configured ? 'Configurado' : 'Pendente' ?></span></div>
            <div class="settings-item"><div><h3>Integração GID</h3><p>Cliente ainda opera em modo stub.</p></div><span class="badge-soft warning">Não configurada</span></div>
            <div class="settings-item"><div><h3>SEFAZ RS</h3><p>Certificado e homologação pendentes.</p></div><span class="badge-soft warning">Não configurada</span></div>
            <div class="settings-item"><div><h3>Auditoria</h3><p>Eventos protegidos por triggers imutáveis.</p></div><span class="badge-soft">Ativa</span></div>
        </div>
    </aside>
</section>
