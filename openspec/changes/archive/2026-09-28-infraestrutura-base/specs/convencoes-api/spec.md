# Spec Delta

## Purpose

Definir as convenções transversais de toda a API (versionamento, formato de erro e cache HTTP) para que os endpoints de negócio sejam previsíveis para qualquer consumidor e possam evoluir sem quebrar clientes existentes.

## ADDED Requirements

### Requirement: Versionamento por prefixo
Todas as rotas de negócio SHALL ficar sob o prefixo `/api/v1/`. Rotas operacionais (como `/api/health`) SHALL ficar fora do versionamento. Uma mudança incompatível de contrato MUST ser publicada num novo prefixo (`/api/v2/`), mantendo o anterior.

#### Scenario: Rota de negócio versionada
- **WHEN** o cliente chama `GET /api/v1/ufs`
- **THEN** recebe `200`

#### Scenario: Rota sem versão
- **WHEN** o cliente chama `GET /api/ufs`
- **THEN** recebe `404` no formato de erro padrão

### Requirement: Erros no formato Problem Details
Toda resposta de erro sob `/api/` SHALL usar o formato Problem Details (RFC 9457), com `Content-Type: application/problem+json` e os campos `type`, `title`, `status` e `detail`. Erros de validação (`422`) SHALL incluir o campo de extensão `errors`, mapeando cada parâmetro às suas mensagens. Erros `500` MUST NOT expor stack trace, SQL ou caminho de arquivo.

```json
{
  "type": "about:blank",
  "title": "Parâmetros inválidos",
  "status": 422,
  "detail": "O campo q deve ter pelo menos 2 caracteres.",
  "errors": { "q": ["O campo q deve ter pelo menos 2 caracteres."] }
}
```

#### Scenario: Recurso inexistente
- **WHEN** o cliente chama `GET /api/v1/municipios/9999999`
- **THEN** recebe `404` com `Content-Type: application/problem+json`
- **AND** o corpo contém `status: 404` e `detail: "Município não encontrado."`

#### Scenario: Validação
- **WHEN** o cliente chama `GET /api/v1/municipios?q=s`
- **THEN** recebe `422` com `Content-Type: application/problem+json` e `errors.q` preenchido

#### Scenario: Erro inesperado
- **WHEN** ocorre uma exceção não tratada numa rota da API
- **THEN** o cliente recebe `500` em Problem Details com `detail` genérico e sem detalhes internos

### Requirement: Cache HTTP de respostas imutáveis
Como o dado do censo não muda durante a vida da imagem, respostas `200` de rotas `GET` sob `/api/v1/` SHALL incluir `Cache-Control: public, max-age=86400` e um `ETag`. Uma requisição com `If-None-Match` igual ao `ETag` atual SHALL receber `304 Not Modified` sem corpo. Respostas de erro MUST NOT ser cacheáveis publicamente.

#### Scenario: Primeira requisição
- **WHEN** o cliente chama `GET /api/v1/ufs/SP`
- **THEN** a resposta `200` contém `Cache-Control` com `public` e `max-age=86400`, e um cabeçalho `ETag`

#### Scenario: Revalidação
- **WHEN** o cliente repete a chamada enviando `If-None-Match` com o `ETag` recebido
- **THEN** recebe `304` sem corpo

#### Scenario: Erro não é cacheado
- **WHEN** o cliente recebe `404` ou `422`
- **THEN** a resposta não contém `Cache-Control: public`
