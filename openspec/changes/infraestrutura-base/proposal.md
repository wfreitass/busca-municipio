# Proposal

## Why

O avaliador vai clonar o repositório numa máquina que só tem Docker e rodar **um único comando**. Se qualquer passo manual for necessário (instalar dependência, migration, copiar `.env`), o requisito falha — independentemente da qualidade das telas. Por isso a fundação (monorepo, esqueletos Laravel/Angular e Docker Compose) vem primeiro e é validada antes de qualquer funcionalidade.

## What Changes

- Estrutura de monorepo: `backend/` (Laravel 12), `frontend/` (Angular 20+), `censo.sqlite` na raiz, `docker-compose.yml` na raiz.
- Imagem do back-end (`php:8.3-apache`) com dependências Composer instaladas no build e `APP_KEY` gerada no build.
- Imagem do front-end multi-stage (`node:22-alpine` → `nginx:alpine`) servindo o build do Angular e fazendo proxy de `/api/` para o back-end.
- Endpoint `GET /api/health` para _healthcheck_ e para o front-end aguardar o back-end.
- Configuração do Laravel adequada a uma API sem estado e somente leitura (sem session/cache/queue em banco).
- Convenções transversais da API: prefixo `/api/v1`, erros em Problem Details (RFC 9457) e cache HTTP (`Cache-Control` + `ETag`/`304`) para respostas imutáveis.
- `.gitignore`/`.dockerignore` coerentes (sem `vendor/`, `node_modules/`, `.env`).

## Capabilities

### New Capabilities
- `infraestrutura`: subida da aplicação completa com um comando, roteamento front ↔ API na mesma origem e verificação de saúde.
- `convencoes-api`: versionamento, formato de erro e cache HTTP comuns a todas as rotas de negócio.

### Modified Capabilities
- Nenhuma.

## Impact

- Novos diretórios `backend/` e `frontend/`, `docker-compose.yml`, `backend/Dockerfile`, `frontend/Dockerfile`, `frontend/nginx.conf`.
- Porta exposta no host: `8080` (front + proxy da API). Porta `8000` opcional para depuração da API.
- Fora de escopo: dados do censo (change `preparacao-dados-censo`), telas funcionais, HTTPS, CI.
