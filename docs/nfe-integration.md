# Integração NF-e para CDV

## Escopo

O NFDetranRS emitirá NF-e modelo 55 para documentar entrada e saída de peças usadas do CDV. A autorização fiscal ocorre na SEFAZ/RS; a reserva, confirmação da venda e baixa da peça continuam sendo operações distintas no GID-CDV.

## Ambientes e endpoints

O ambiente local usa homologação e o transporte `mock` por padrão. O simulador nunca é aceito quando `NFE_ENVIRONMENT=production`.

- Homologação: `https://nfe-homologacao.sefazrs.rs.gov.br/ws`
- Produção: `https://nfe.sefazrs.rs.gov.br/ws`
- Leiaute: NF-e 4.00
- Pacote de schemas inicial: `PL_010_V1.30`

Os serviços configurados são autorização, retorno da autorização, status, consulta de protocolo e recepção de eventos. A relação oficial deve ser conferida antes de cada implantação.

## Estado atual

Implementado nesta primeira etapa:

- configuração HOM/PROD separada do GID;
- bloqueio do simulador em produção;
- biblioteca `nfephp-org/sped-nfe` instalada;
- validação de prontidão do emitente;
- validação estrutural do rascunho fiscal e limite de 100 itens;
- geração e validação do dígito da chave de acesso;
- simulador de autorização/cancelamento, sempre marcado como sem valor fiscal;
- campos NCM, CEST, CFOP, unidade, origem, ICMS, PIS e COFINS no estoque;
- numeração independente por ambiente/modelo/série;
- comando de diagnóstico `php yii nfe/doctor`;
- migration `m260925_000004_prepare_nfe_emission` aplicada no banco local.

Ainda não implementado:

- geração completa e assinatura do XML;
- transmissão mTLS e tratamento dos retornos da SEFAZ;
- DANFE;
- eventos reais de cancelamento e inutilização;
- worker de emissão e retentativas;
- vínculo transacional entre autorização SEFAZ e confirmação/baixa no GID;
- regras IBS/CBS definitivas para a operação do CDV.

## Dados pendentes

Antes mesmo da simulação da emissão a aplicação exige:

- `NFE_COMPANY_IE`: Inscrição Estadual;
- `NFE_COMPANY_CRT`: regime tributário (1, 2, 3 ou 4);
- perfil fiscal de cada peça: NCM, eventual CEST, CFOP, origem, CST/CSOSN, PIS e COFINS.

Para comunicação real também serão necessários `NFE_CERT_PATH` e `NFE_CERT_PASSWORD`. O arquivo PFX e a senha não podem ser versionados.

Execute o diagnóstico:

```sh
K:\xampp\php\php.exe yii nfe/doctor
```

Execute os testes locais:

```sh
K:\xampp\php\php.exe tests/nfe_foundation_test.php
```

Resultados do simulador não são documentos fiscais, não podem ser entregues ao comprador e não devem provocar baixa real no GID.
