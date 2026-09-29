# Tasks

## 1. Repositório

- [x] 1.1 [BACK] Criar `.gitignore`, `.gitattributes` (`eol=lf`, `*.sqlite binary`) e `.dockerignore` na raiz; copiar `censo.sqlite` para a raiz; verificar com `git status` que o sqlite aparece como binário a versionar — commit `chore: estrutura inicial do monorepo`

## 2. Back-end

- [x] 2.1 [BACK] Criar projeto Laravel 12 em `backend/` (`composer create-project laravel/laravel backend`), remover migrations/seeders padrão de users/cache/jobs; verificar `php artisan --version` — commit `chore(backend): cria projeto laravel`
- [x] 2.2 [BACK] Registrar `routes/api.php` em `bootstrap/app.php`, criar `GET /api/health` → `{"status":"ok"}`; ajustar `.env.example` conforme design D4; verificar com `curl localhost:8000/api/health` e `curl localhost:8000/api/nao-existe` — commit `feat(backend): endpoint de health e api sem estado`
- [x] 2.3 [BACK] Convenções da API (design D7–D9): grupo `/api/v1` com `cache.headers:public;max_age=86400;etag`, handler único de Problem Details para `api/*`; rota de exemplo temporária não é necessária — validar com `/api/v1/nao-existe` (404 problem+json) — commit `feat(backend): convencoes da api (v1, problem details, cache http)`
- [x] 2.4 [QA] Testes feature: `HealthTest` (200 + JSON); `/api/nao-existe` e `/api/v1/nao-existe` → 404 `application/problem+json` com `status` e `detail`; 500 forçado (rota fake registrada só no teste) sem stack trace; rota fake GET em `/api/v1` registrada só no teste → `Cache-Control: public, max-age=86400` + `ETag`, e `If-None-Match` → 304; 404/422 sem `Cache-Control: public` — commit `test(backend): health, formato de erro e cache http`
- [x] 2.4b [BACK] Adicionar `osteel/openapi-httpfoundation-testing` (dev) e um trait de teste `ValidaContratoOpenApi` apontando para `docs/api/openapi.yaml` (copiado para a imagem no build); validar com a resposta de `/api/health` — commit `test(backend): base de validacao do contrato openapi`
- [x] 2.5 [BACK] `backend/Dockerfile` (composer stage → `php:8.3-apache`, DocumentRoot `public/`, `a2enmod rewrite`, `key:generate` no build, permissões de `storage/`); verificar `docker build -f backend/Dockerfile .` — commit `build(backend): dockerfile apache`

## 3. Front-end

- [x] 3.1 [FRONT] Criar app Angular em `frontend/` (standalone, routing, SCSS, sem SSR), adicionar Angular Material, `LOCALE_ID` pt-BR e `provideHttpClient`; verificar `npm run build` — commit `chore(frontend): cria projeto angular com material`
- [x] 3.2 [FRONT] Shell com toolbar e navegação entre `/municipios` e `/estados` (páginas placeholder lazy), `''` redireciona para `/municipios`, rota coringa volta para `/municipios`; `proxy.conf.json` para `ng serve`; verificar navegação manual — commit `feat(frontend): shell e rotas das duas telas`
- [x] 3.2b [FRONT] Script `api:tipos` (`openapi-typescript`) gerando `src/app/core/api/schema.ts` a partir de `docs/api/openapi.yaml`, e `censo.models.ts` com aliases; verificar `npm run api:tipos && npm run build` — commit `build(frontend): tipos gerados a partir do contrato openapi`
- [x] 3.3 [FRONT] `CensoApiService.health()` + indicador discreto de "API indisponível" no shell; verificar desligando o back-end — commit `feat(frontend): verificação de saúde da api`
- [x] 3.3b [FRONT] `problemaInterceptor` (Problem Details → `ApiErro {status, titulo, detalhe}`; erro de rede → `status: 0`) registrado com `withInterceptors`; verificar com `/api/v1/nao-existe` no console — commit `feat(frontend): interceptor de problem details`
- [x] 3.4 [QA] Teste do `CensoApiService.health()` com `HttpTestingController` e do `problemaInterceptor` (404 problem+json → `ApiErro.status = 404`); verificar `npm test` verde — commit `test(frontend): servico de api`
- [x] 3.5 [FRONT] `frontend/Dockerfile` multi-stage + `nginx.conf` (fallback SPA + proxy `/api/`); verificar `docker build frontend` — commit `build(frontend): dockerfile nginx com proxy`

## 4. Orquestração

- [x] 4.1 [BACK] `docker-compose.yml` com `backend` (healthcheck) e `frontend` (`depends_on: service_healthy`, `${FRONT_PORT:-8080}:80`); verificar `docker compose up --build` + `curl localhost:8080/api/health` + abrir `localhost:8080/estados/SP` direto — commit `build: docker compose com um comando`
- [ ] 4.2 [QA] Teste de clone limpo: `git clone` para pasta temporária, `docker compose up --build`, validar cenários da spec `infraestrutura` e `git status` limpo; registrar resultado no PR/commit — commit `test: valida subida em clone limpo` (se houver ajuste)
