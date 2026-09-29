# Design

## Context

Repositório vazio + `censo.sqlite` (35,2 MB). Máquina do avaliador tem apenas Docker. Ver `docs/ARQUITETURA.md` §1 e §4 para a visão geral; aqui ficam as decisões operacionais.

## Goals / Non-Goals

**Goals:**
- Um comando (`docker compose up --build`) do clone até a tela.
- Builds reprodutíveis e cacheáveis (camadas de dependência antes do código).
- Mesma origem para SPA e API (zero CORS).

**Non-Goals:**
- Ambiente de desenvolvimento com hot-reload dentro do Docker (dev roda local com `php artisan serve` + `ng serve` com proxy).
- HTTPS, CI/CD, observabilidade.

## Decisions

### D1. Contexto de build do back-end é a raiz do repositório
`docker-compose.yml` usa `build: { context: ., dockerfile: backend/Dockerfile }` para que o `Dockerfile` consiga `COPY censo.sqlite` (usado na change `preparacao-dados-censo`).
- Alternativa descartada: montar o sqlite como volume em runtime — exigiria preparar a base no _entrypoint_ a cada subida e volume gravável.
- Consequência: `.dockerignore` na raiz MUST excluir `frontend/`, `**/node_modules`, `backend/vendor`, `.git`, `openspec/`, `docs/`.

### D2. `php:8.3-apache` em vez de `php artisan serve` ou php-fpm + nginx
Apache com `mod_rewrite` e `DocumentRoot=/var/www/html/public` é um único processo, estável e multi-request. `artisan serve` é _single-threaded_ e não recomendado; php-fpm exigiria um segundo servidor HTTP.
- Composer via stage `composer:2` (`composer install --no-dev --optimize-autoloader --no-interaction`); o stage de testes pode usar `--dev` (ver D6).
- Extensões: `pdo_sqlite` já vem na imagem oficial; instalar só `curl` para o healthcheck se ausente.

### D3. Nginx do front-end faz o proxy de `/api/`
```nginx
location /api/ { proxy_pass http://backend:80; }
location /     { try_files $uri $uri/ /index.html; }
```
- Alternativa descartada: CORS no Laravel com front em outra porta — mais configuração, mais pontos de falha.
- `ng serve` local usa `proxy.conf.json` apontando `/api` para `http://localhost:8000`, mantendo o mesmo `baseUrl: '/api'` em todos os ambientes.

### D4. Configuração do Laravel para API somente leitura
- Rotas: `bootstrap/app.php` → `->withRouting(api: __DIR__.'/../routes/api.php', …)`. **Não** rodar `php artisan install:api` (traz Sanctum + migrations desnecessárias).
- `.env.example`: `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_DRIVER=array`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, `LOG_CHANNEL=stderr`, `DB_CONNECTION=sqlite`, `DB_DATABASE=/var/www/html/database/censo.sqlite`.
- No build: `cp .env.example .env && php artisan key:generate --force`.
- Exceções sob `/api/*` renderizadas como JSON: `->withExceptions(fn ($e) => $e->shouldRenderJsonWhen(fn ($r) => $r->is('api/*')))`.
- `storage/` e `bootstrap/cache/` com dono `www-data`.

### D5. Healthcheck e ordem de subida
```yaml
backend:
  healthcheck: { test: ["CMD", "curl", "-fs", "http://localhost/api/health"], interval: 5s, retries: 20 }
frontend:
  depends_on: { backend: { condition: service_healthy } }
  ports: ["8080:80"]
```

### D6. Testes do back-end rodam na mesma imagem
Um _target_ `test` no `backend/Dockerfile` (com `composer install` incluindo dev) permite `docker compose run --rm backend-test` ou, mais simples, `docker compose run --rm backend php artisan test` se o stage final mantiver as dev-deps. Decisão: **manter dev-deps no stage final** (imagem ~15 MB maior, zero complexidade extra). Alternativa descartada: stage separado — complexidade desnecessária para um teste técnico.

### D7. Versionamento `/api/v1` por grupo de rotas
`Route::prefix('v1')->middleware('cache.headers:public;max_age=86400;etag')->group(...)` em `routes/api.php`; `/api/health` fica fora do grupo. O front usa `apiBaseUrl = '/api/v1'`.
- Alternativa descartada: versão por header (`Accept: application/vnd…`) — invisível no navegador e no curl, pior para depurar e para cache de proxy.

### D8. Problem Details (RFC 9457) centralizado
Um único `render` em `bootstrap/app.php` (`withExceptions`) para `api/*` converte `ValidationException` → 422 com `errors`, `NotFoundHttpException`/`ModelNotFound` → 404, `HttpException` → seu status, e `Throwable` → 500 genérico (o detalhe vai só para o log em `stderr`). Controllers apenas lançam `abort(404, 'Município não encontrado.')`.
- Alternativa descartada: formato próprio `{message}` — funciona, mas é mais um formato a documentar; RFC 9457 é padrão que clientes e gateways entendem.

### D9. Cache HTTP com o middleware nativo `cache.headers`
O Laravel já calcula `ETag` (md5 do corpo) e devolve `304` quando `If-None-Match` confere (`SetCacheHeaders` + `isNotModified`). Zero código próprio. Aplica-se só a `200` (erros passam pelo handler de exceção e não herdam o cabeçalho — verificar em teste).
- Por que é seguro: o dado é imutável dentro de uma imagem; nova imagem ⇒ novo corpo ⇒ novo `ETag`.
- Ganho de escala: navegador, nginx ou CDN podem responder sem chegar ao PHP.
- Alternativa descartada: cache em Redis — adiciona serviço para um dado que já é lido de tabela pequena e indexada.

### D10. Contrato primeiro: `docs/api/openapi.yaml` é a fonte da verdade do HTTP
O contrato OpenAPI 3.0.3 foi escrito **antes** do código, junto com as specs, e passa em `npx @redocly/cli lint` (regras em `redocly.yaml`).
- **Front:** os tipos TypeScript são **gerados** a partir dele (`npm run api:tipos` → `openapi-typescript ../docs/api/openapi.yaml -o src/app/core/api/schema.ts`, arquivo versionado). `censo.models.ts` só reexporta aliases legíveis (`type MunicipioResumo = components['schemas']['MunicipioResumo']`). Mudou o contrato ⇒ o `ng build` quebra onde o front ficou desatualizado.
- **Back:** um teste de contrato (`ContratoOpenApiTest`) valida as respostas reais de cada rota contra o contrato com `osteel/openapi-httpfoundation-testing` (usa `league/openapi-psr7-validator`, que exige OAS 3.0 — por isso 3.0.3 e não 3.1).
- Divisão de responsabilidades: a **spec OpenSpec** diz *o comportamento* (regras, cenários); o **OpenAPI** diz *a forma* (rotas, tipos, códigos). Os exemplos da spec e do contrato MUST ser coerentes (tarefa do BA).
- Alternativa descartada: gerar o OpenAPI a partir do código (ex.: Scramble) — inverte a ordem (código define contrato) e amarra o contrato à implementação.

## Risks / Trade-offs

- [Porta 8080 ocupada na máquina do avaliador] → documentar no README como trocar (`FRONT_PORT=… docker compose up`), usando `"${FRONT_PORT:-8080}:80"`.
- [Build lento por `npm ci` e `composer install`] → copiar `package*.json`/`composer.*` antes do código para aproveitar cache de camadas.
- [Fim de linha CRLF em scripts no Windows/WSL] → `.gitattributes` com `* text=auto eol=lf` e `*.sqlite binary`.
- [Arquitetura do host ARM (Mac M1/M2)] → usar apenas imagens oficiais multi-arch (`php`, `node`, `nginx`, `composer`).
