# Integração GID-CDV

## Empresa emitente

- Nome fantasia: OFICINA DA MOTO
- Razão social: OFICINA DA MOTO COMERCIO DE PECAS LTDA
- Código CDV: `CDV00027`
- CNPJ: `05034500000168`
- Endereço: RUA DOUTOR JOAO INACIO, 218 - NAVEGANTES - PORTO ALEGRE/RS - 90230-180

O emitente é configurado por variáveis de ambiente. O destinatário da venda continua sendo uma configuração separada e nunca deve ser confundido com o CDV vendedor.

## Endpoints confirmados

| Ambiente | Código | Alias publicado pelo DetranRS | WSDL SOAP atual | Serviço SOAP atual |
|---|---:|---|---|---|
| Homologação | 2 | `http://desmanches.hml.detran.rs.gov.br/integracaonfe` | `https://secweb.hml.intra.rs.gov.br/cdv/IntegracaoGidSoap?wsdl` | `https://secweb.hml.intra.rs.gov.br/cdv/IntegracaoGidSoap` |
| Produção | 1 | `http://desmanches.detran.rs.gov.br/integracaonfe` | `https://secweb.procergs.com.br/cdv/IntegracaoGidSoap?wsdl` | `https://secweb.procergs.com.br/cdv/IntegracaoGidSoap` |

O alias de produção foi verificado e redireciona para o WSDL HTTPS indicado. O alias de homologação redireciona para a rede `intra.rs.gov.br`; o acesso pode depender de túnel/VPN ou liberação da PROCERGS.

Apesar do nome legado `integracaonfe`, este é o serviço SOAP do GID-CDV para estoque e fluxo de venda. A autorização fiscal da NF-e na SEFAZ é uma integração separada.

O manual de integração versão 1.4 está indexado em:

- https://ptdocz.com/doc/420248/manual-de-integra%C3%A7%C3%A3o-do-sistema-gid-desmanches-e

A página oficial atual de suporte aos CDVs é:

- https://www.detran.rs.gov.br/cdv

Antes de uso operacional, confirmar os endpoints e a versão do contrato com `divdes-cst@detran.rs.gov.br` ou com o Help Desk PROCERGS `(51) 3210-3995`.

## Operações expostas no WSDL de produção

- `listarGrupoSubgrupoGid`
- `consultarEstoqueGid`
- `pesquisarEstoqueGid`
- `incluirReservaEstoqueGID`
- `cancelarReservaEstoqueGid`
- `confirmarVendaGID`
- `cancelarVendaGid`
- `validarEstoqueGid`
- `listarPecaGid`
- `verificarDisponibilidadeGID`
- `cadastrarNotaFiscalEntradaGid`
- `cancelarNotaFiscalEntradaGid`
- `devolverNotaFiscalEntradaGid`
- `emissaoEtiquetasGID`
- `listarPatiosGID`
- `movimentarEstoqueGID`
- `movimentarEstoqueAlfaGID`
- `venderContingenciaGID`

## Configuração por ambiente

O ambiente local usa homologação por padrão. Produção é selecionada automaticamente quando `APP_ENV=production`. As URLs podem ser substituídas pelas variáveis `GID_WSDL_URL` e `GID_SERVICE_URL` sem alterar código.

O certificado deve ficar fora do repositório:

```dotenv
GID_CERT_PATH=K:/caminho-seguro/certificado.pfx
GID_CERT_PASSWORD=senha-fornecida-fora-do-git
```

Nunca versionar `.pfx`, `.p12`, `.pem`, `.key` ou a senha do certificado.

## Pendências antes da primeira sincronização

1. Obter o certificado digital e a senha para a homologação.
2. Solicitar/liberar o acesso ao host de homologação na PROCERGS.
3. Confirmar o cabeçalho SOAP, assinatura XML e versão atual do manual.
4. Implementar o cliente SOAP real; o componente atual ainda é um stub seguro.
5. Mapear o retorno do GID para `estoque_gid` sem permitir alterações manuais no estoque de origem.
6. Implementar paginação por `ULTIMO_ITEM_PESQUISADO`, com até 50 itens por resposta conforme o manual 1.4.
7. Registrar cada chamada em `integracao_log`, sem armazenar certificado, senha ou payload sensível.
8. Homologar consulta, reserva, confirmação, cancelamento e contingência antes de habilitar produção.

## Comandos preparados

Depois de configurar `GID_CERT_PATH` e `GID_CERT_PASSWORD`, valide o arquivo sem chamar o serviço:

```powershell
K:\xampp\php\php.exe yii gid/doctor
```

Após a liberação da homologação, sincronize o estoque:

```powershell
K:\xampp\php\php.exe yii gid/sync
```

O sincronizador consulta o catálogo de nomes de peças e pesquisa cada nome de forma exata, paginando os resultados em blocos de até 50 itens. A operação é transacional: em caso de falha, o estoque local não fica parcialmente atualizado. O botão **Sincronizar GID** executa o mesmo fluxo para administradores.
