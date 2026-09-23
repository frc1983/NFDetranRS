# Banco de dados

Migrations preparadas para MySQL 8.0.16+ e validadas localmente no MariaDB distribuido pelo XAMPP. Antes de producao, valide novamente na versao exata do servidor de destino.

O ambiente local usa o banco `nfdetranrs` e o arquivo `.env.local`. Depois de instalar as dependencias:

```sh
php yii migrate --migrationPath=@app/database/migrations
```

A primeira migration cria usuarios, RBAC, configuracao, estoque, vendas, NF-e, itens, logs e auditoria. A segunda define papeis operador/fiscal/auditor/administrador. Nenhum usuario ou senha padrao e criado. Autenticacao e a ligacao de permissoes aos endpoints ainda devem ser implementadas antes de habilitar operacoes reais.

DDL MySQL faz commit implicito: erro durante criacao pode deixar schema parcial. Revise o banco antes de reexecutar. Reversao automatica foi desativada para preservar registros fiscais e de auditoria.

Credenciais/certificados GID e SEFAZ nunca entram no Git. `configuracao.valor_cifrado` e somente uma reserva de armazenamento; o servico de criptografia ainda deve ser implementado com chave externa. Logs foram desenhados para hashes e metadados; nao gravar payload fiscal bruto, senha, token ou certificado em mensagens de erro.

Triggers bloqueiam UPDATE/DELETE diretos em auditoria e logs. A conta de execucao deve ter privilegios minimos e nao pode possuir ALTER, DROP, TRIGGER ou TRUNCATE. DBA ainda pode remover protecoes; exportacao assinada/WORM e uma etapa futura. A FK da auditoria usa RESTRICT para que exclusao de usuario nao altere historico via cascata.

O limite de NF-e e aplicado no dominio (ate 100) e no esquema (ordem unica entre 1 e 100). Cada item de venda so pode integrar uma NF-e nesta estrutura inicial. Idempotencia, correlation_id e tentativas estao previstos no schema/interfaces; o worker transacional e a politica de repeticao ainda devem ser implementados.
