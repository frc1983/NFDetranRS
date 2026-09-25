# NFDetranRS — estrutura inicial

PHP 8.0+, Yii2, Bootstrap 5 e MySQL. Modulos: Dashboard, Estoque GID, Nova venda, Vendas, NF-e, Auditoria e Configuracoes.

Esta entrega ainda nao emite documentos fiscais reais. Autenticacao, RBAC, persistencia de vendas e sincronizacao inicial do estoque GID estao preparados. A NF-e possui configuracao HOM/PROD, base de dados fiscal, chave de acesso, validadores e simulador local; XML completo, assinatura, DANFE e transmissao SEFAZ continuam pendentes. Nao implantar em producao.

## Preparar localmente

1. Execute `composer install` na raiz. O Composer resolvera dependencias e criara `composer.lock`; revise e versione esse lock quando disponivel.
2. Copie `.env.local.example` para `.env.local`, configure o banco e gere uma chave forte para `APP_COOKIE_VALIDATION_KEY` (`php -r "echo bin2hex(random_bytes(32));"`). O arquivo local real nao e versionado.
3. No XAMPP, acesse `http://localhost/NFDetranRS`. O `.htaccess` encaminha URLs amigaveis ao front controller e serve somente os assets publicos de `web/`.
4. No primeiro acesso local, cadastre o administrador em `/site/setup`. Depois da criacao do primeiro usuario, essa rota redireciona para o login.

## Ambientes

- `local` e o padrao e carrega `.env.local`.
- `production` carrega `.env.production` quando o servidor define `APP_ENV=production`.
- `APP_ENV_FILE` pode apontar explicitamente para outro arquivo seguro fora do repositorio.
- Apenas `.env.local.example` e `.env.production.example` sao versionados. Nunca copie segredos reais de producao para o Git.
- No ambiente local, o GID-CDV aponta para homologacao; com `APP_ENV=production`, aponta para producao. Consulte `docs/gid-integration.md` para endpoints e pendencias de certificado.
- A NF-e usa simulador somente no ambiente local e bloqueia dados fiscais incompletos. Consulte `docs/nfe-integration.md` e execute `php yii nfe/doctor`.

Para criar ou atualizar o esquema, siga `database/README.md`. O acesso a Dashboard, Estoque, Vendas, NF-e, Auditoria e Configuracoes exige login e permissao RBAC.

## Validacao

`php tests/domain_test.php` testa os lotes 0/1/100/101/201, conservacao de itens, limites invalidos, transicoes e destinatario fixo. `composer validate --no-check-publish` verifica o manifesto. Dependencias e aplicacao HTTP precisam ser validadas apos `composer install`.

`php tests/gid_config_test.php` valida a selecao de HOM/PROD. `php tests/gid_signature_test.php` valida a leitura PKCS#12 e a assinatura XML Digital Signature. Com o certificado configurado, use `php yii gid/doctor` antes de `php yii gid/sync`.

`php tests/nfe_foundation_test.php` valida a configuracao fiscal, os rascunhos, a chave de acesso e o simulador SEFAZ local.

## Regras preparadas

- Destinatario fixo obtido da configuracao (documento/nome), sem entrada livre na venda.
- Ate 100 itens por NF-e; valores monetarios em DECIMAL no esquema.
- Interfaces GID e SEFAZ falham explicitamente ate configuracao de conectores reais.
- Schema com correlation_id, idempotencia, tentativas e auditoria append-only.
- Segredos e certificados fora do repositorio. Integracoes reais exigem validacao dos contratos e homologacao.

## Git

Fluxo solicitado: `master` como padrao, desenvolvimento em `dev`. A entrega em staging nao modifica branches: confira o estado do repositorio alvo antes de aplicar ou publicar arquivos. A remocao de `main` deve ocorrer somente depois de confirmar `master` publicada e definida como padrao.
