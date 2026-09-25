<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $recipient */
/** @var array $company */
/** @var array $gid */
/** @var array $nfe */
$this->title = 'Configurações';
$configured = !empty($recipient['name']) && !empty($recipient['document']) && !empty($recipient['street']) && !empty($recipient['city_code']);
$gidCertificateConfigured = !empty($gid['certificatePath']) && is_file($gid['certificatePath']);
$gidEnvironmentLabel = $gid['environment'] === 'production' ? 'Produção' : 'Homologação';
?>
<section class="page-head"><div><span class="page-kicker">Administração</span><h1 class="page-title">Configurações</h1><p class="page-subtitle">Parâmetros operacionais persistidos no banco local.</p></div></section>
<section class="content-grid">
    <article class="card-surface form-card">
        <header class="mb-4"><h2 class="h5">Destinatário fixo</h2><p class="text-muted small">Esses dados serão copiados para todas as novas vendas.</p></header>
        <?= Html::beginForm(['/configuracao/index'], 'post') ?>
        <div class="mb-3"><label class="form-label" for="recipient-name">Razão social</label><input class="form-control" id="recipient-name" name="recipient_name" maxlength="190" required value="<?= Html::encode($recipient['name'] ?? '') ?>"></div>
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label" for="recipient-document">CPF/CNPJ</label><input class="form-control" id="recipient-document" name="recipient_document" maxlength="18" inputmode="numeric" required value="<?= Html::encode($recipient['document'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label" for="recipient-ie">Inscrição Estadual</label><input class="form-control" id="recipient-ie" name="recipient_state_registration" maxlength="14" value="<?= Html::encode($recipient['state_registration'] ?? '') ?>"></div>
            <div class="col-md-8"><label class="form-label" for="recipient-street">Logradouro</label><input class="form-control" id="recipient-street" name="recipient_street" maxlength="190" required value="<?= Html::encode($recipient['street'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label" for="recipient-number">Número</label><input class="form-control" id="recipient-number" name="recipient_number" maxlength="20" required value="<?= Html::encode($recipient['number'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="recipient-complement">Complemento</label><input class="form-control" id="recipient-complement" name="recipient_complement" maxlength="100" value="<?= Html::encode($recipient['complement'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label" for="recipient-district">Bairro</label><input class="form-control" id="recipient-district" name="recipient_district" maxlength="100" required value="<?= Html::encode($recipient['district'] ?? '') ?>"></div>
            <div class="col-md-3"><label class="form-label" for="recipient-city-code">Código IBGE</label><input class="form-control" id="recipient-city-code" name="recipient_city_code" maxlength="7" required value="<?= Html::encode($recipient['city_code'] ?? '') ?>"></div>
            <div class="col-md-5"><label class="form-label" for="recipient-city">Município</label><input class="form-control" id="recipient-city" name="recipient_city" maxlength="100" required value="<?= Html::encode($recipient['city'] ?? '') ?>"></div>
            <div class="col-md-2"><label class="form-label" for="recipient-state">UF</label><input class="form-control" id="recipient-state" name="recipient_state" maxlength="2" required value="<?= Html::encode($recipient['state'] ?? 'RS') ?>"></div>
            <div class="col-md-2"><label class="form-label" for="recipient-postal-code">CEP</label><input class="form-control" id="recipient-postal-code" name="recipient_postal_code" maxlength="8" required value="<?= Html::encode($recipient['postal_code'] ?? '') ?>"></div>
            <div class="col-md-7"><label class="form-label" for="recipient-email">E-mail</label><input class="form-control" type="email" id="recipient-email" name="recipient_email" maxlength="190" value="<?= Html::encode($recipient['email'] ?? '') ?>"></div>
            <div class="col-md-5"><label class="form-label" for="recipient-phone">Telefone</label><input class="form-control" id="recipient-phone" name="recipient_phone" maxlength="20" value="<?= Html::encode($recipient['phone'] ?? '') ?>"></div>
        </div>
        <div class="form-actions"><?= Html::submitButton('Salvar configuração', ['class' => 'btn btn-primary']) ?></div>
        <?= Html::endForm() ?>
    </article>
    <aside class="card-surface section-card">
        <header class="section-head"><div><h2>Empresa emitente</h2><p>Cadastro utilizado na integração com o GID-CDV.</p></div></header>
        <div class="section-body settings-list">
            <div class="settings-item"><div><h3><?= Html::encode($company['tradeName']) ?></h3><p><?= Html::encode($company['legalName']) ?></p></div><span class="badge-soft">Ativa</span></div>
            <div class="settings-item"><div><h3>Código CDV</h3><p><?= Html::encode($company['cdvCode']) ?></p></div></div>
            <div class="settings-item"><div><h3>CNPJ</h3><p><?= Html::encode($company['cnpj']) ?></p></div></div>
            <div class="settings-item"><div><h3>Endereço</h3><p><?= Html::encode($company['address']) ?></p></div></div>
        </div>
    </aside>
    <aside class="card-surface section-card">
        <header class="section-head"><div><h2>Estado do ambiente</h2><p>Integrações e segurança.</p></div></header>
        <div class="section-body settings-list">
            <div class="settings-item"><div><h3>Destinatário fixo</h3><p>Dados persistidos e auditados.</p></div><span class="badge-soft <?= $configured ? '' : 'warning' ?>"><?= $configured ? 'Configurado' : 'Pendente' ?></span></div>
            <div class="settings-item"><div><h3>GID <?= Html::encode($gidEnvironmentLabel) ?></h3><p><?= Html::encode($gid['serviceUrl']) ?></p></div><span class="badge-soft <?= $gidCertificateConfigured ? '' : 'warning' ?>"><?= $gidCertificateConfigured ? 'Certificado disponível' : 'Aguardando certificado' ?></span></div>
            <div class="settings-item"><div><h3>NF-e <?= ($nfe['environment'] ?? '') === 'production' ? 'Produção' : 'Homologação' ?></h3><p><?= Html::encode($nfe['transport'] === 'mock' ? 'Simulação local; nenhum envio à SEFAZ' : $nfe['authorizationUrl']) ?></p></div><span class="badge-soft <?= $nfe['transport'] === 'mock' ? 'warning' : '' ?>"><?= $nfe['transport'] === 'mock' ? 'Simulador' : 'SEFAZ' ?></span></div>
            <div class="settings-item"><div><h3>Auditoria</h3><p>Eventos protegidos por triggers imutáveis.</p></div><span class="badge-soft">Ativa</span></div>
        </div>
    </aside>
</section>
