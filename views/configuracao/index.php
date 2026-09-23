<?php

/** @var yii\web\View $this */
/** @var array $sections */

$this->title = 'Configurações';
$sections = $sections ?? [];
?>
<section class="page-head"><div><span class="page-kicker">Administração</span><h1 class="page-title">Configurações</h1><p class="page-subtitle">Prepare integrações, parâmetros fiscais e segurança do ambiente.</p></div></section>
<section class="content-grid">
    <article class="card-surface section-card">
        <header class="section-head"><div><h2>Integrações e emissão</h2><p>Dados sensíveis devem permanecer fora do código-fonte.</p></div></header>
        <div class="section-body settings-list">
            <div class="settings-item"><div><h3>Integração GID</h3><p>URL e credenciais fornecidas por configuração segura do ambiente.</p></div><span class="badge-soft warning">Não configurada</span></div>
            <div class="settings-item"><div><h3>SEFAZ RS</h3><p>Homologação/produção e parâmetros de emissão fiscal.</p></div><span class="badge-soft warning">Não configurada</span></div>
            <div class="settings-item"><div><h3>Certificado digital</h3><p>Referência a arquivo protegido; o certificado não é salvo no repositório.</p></div><span class="badge-soft warning">Ausente</span></div>
            <div class="settings-item"><div><h3>Destinatário fixo</h3><p>Razão social, CNPJ e endereço usados nas vendas.</p></div><span class="badge-soft warning">Pendente</span></div>
        </div>
    </article>
    <aside class="card-surface section-card">
        <header class="section-head"><div><h2>Segurança</h2><p>Princípios aplicados ao ambiente.</p></div></header>
        <div class="section-body timeline">
            <div class="timeline-item"><span class="timeline-dot"></span><span class="timeline-copy"><strong>Segredos externos</strong><small>Variáveis de ambiente ou cofre seguro</small></span></div>
            <div class="timeline-item"><span class="timeline-dot"></span><span class="timeline-copy"><strong>Auditoria imutável</strong><small>Eventos sem edição ou exclusão pela interface</small></span></div>
            <div class="timeline-item"><span class="timeline-dot"></span><span class="timeline-copy"><strong>Privilégio mínimo</strong><small>Acessos separados por responsabilidade</small></span></div>
        </div>
    </aside>
</section>
