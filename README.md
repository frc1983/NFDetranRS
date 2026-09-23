# NFDetranRS — estrutura inicial

PHP 8.0+, Yii2, Bootstrap 5 e MySQL. Modulos: Dashboard, Estoque GID, Nova venda, Vendas, NF-e, Auditoria e Configuracoes.

Esta entrega e uma base inicial: telas demonstrativas, dominio de divisao de NF-e, transicoes de estado, interfaces de integracao e migrations. Nao emite documentos fiscais nem persiste vendas. Autenticacao, aplicacao de RBAC, workers/retries, escrita da auditoria e conectores GID/SEFAZ ainda devem ser implementados. Nao implantar em producao.

## Preparar localmente

1. Execute `composer install` na raiz. O Composer resolvera dependencias e criara `composer.lock`; revise e versione esse lock quando disponivel.
2. Copie `.env.example` para `.env`, configure banco e gere uma chave forte para `APP_COOKIE_VALIDATION_KEY` (`php -r "echo bin2hex(random_bytes(32));"`).
3. Execute `php -S 127.0.0.1:8080 -t web` e acesse `http://127.0.0.1:8080`. O modo `APP_DEMO=1` aceita somente GET de localhost; fora desse modo o scaffold bloqueia as rotas.
4. Para Apache/XAMPP, use VirtualHost com DocumentRoot apontando exclusivamente para `K:/xampp/htdocs/NFDetranRS/web`. Nunca exponha a raiz do projeto, `.env` ou `vendor`.

Banco nao e necessario para visualizar o modo demonstrativo. Para criar o esquema posteriormente, siga `database/README.md`. Nenhuma migration foi executada nesta entrega.

## Validacao

`php tests/domain_test.php` testa os lotes 0/1/100/101/201, conservacao de itens, limites invalidos, transicoes e destinatario fixo. `composer validate --no-check-publish` verifica o manifesto. Dependencias e aplicacao HTTP precisam ser validadas apos `composer install`.

## Regras preparadas

- Destinatario fixo obtido da configuracao (documento/nome), sem entrada livre na venda.
- Ate 100 itens por NF-e; valores monetarios em DECIMAL no esquema.
- Interfaces GID e SEFAZ falham explicitamente ate configuracao de conectores reais.
- Schema com correlation_id, idempotencia, tentativas e auditoria append-only.
- Segredos e certificados fora do repositorio. Integracoes reais exigem validacao dos contratos e homologacao.

## Git

Fluxo solicitado: `master` como padrao, desenvolvimento em `dev`. A entrega em staging nao modifica branches: confira o estado do repositorio alvo antes de aplicar ou publicar arquivos. A remocao de `main` deve ocorrer somente depois de confirmar `master` publicada e definida como padrao.
