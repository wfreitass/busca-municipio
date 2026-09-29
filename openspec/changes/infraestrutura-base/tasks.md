# Tasks

## 1. Repositório

- [ ] 1.1 [BACK] Criar `.gitignore`, `.gitattributes` (`eol=lf`, `*.sqlite binary`) e `.dockerignore` na raiz; copiar `censo.sqlite` para a raiz; verificar com `git status` que o sqlite aparece como binário a versionar — commit `chore: estrutura inicial do monorepo`

## 2. Back-end

- [ ] 2.1 [BACK] Criar projeto Laravel 12 em `backend/` (`composer create-project laravel/laravel backend`), remover migrations/seeders padrão de users/cache/jobs; verificar `php artisan --version` — commit `chore(backend): cria projeto laravel`
- [ ] 2.2 [BACK] Registrar `routes/api.php` em `bootstrap/app.php`, forçar JSON em exceções de `api/*`, criar `GET /api/health` → `{"status":"ok"}`; ajustar `.env.example` conforme design D4; verificar com `curl localhost:8000/api/health` e `curl localhost:8000/api/nao-existe` (404 JSON) — commit `feat(backend): endpoint de health e api sem estado`
- [ ] 2.3 [QA] Teste feature `HealthTest` (200 + JSON) e teste de rota inexistente (404 JSON); verificar `php artisan test` verde — commit `test(backend): health e 404 json`
- [ ] 2.4 [BACK] `backend/Dockerfile` (composer stage → `php:8.3-apache`, DocumentRoot `public/`, `a2enmod rewrite`, `key:generate` no build, permissões de `storage/`); verificar `docker build -f backend/Dockerfile .` — commit `build(backend): dockerfile apache`

## 3. Front-end

- [ ] 3.1 [FRONT] Criar app Angular em `frontend/` (standalone, routing, SCSS, sem SSR), adicionar Angular Material, `LOCALE_ID` pt-BR e `provideHttpClient`; verificar `npm run build` — commit `chore(frontend): cria projeto angular com material`
- [ ] 3.2 [FRONT] Shell com toolbar e navegação entre `/municipios` e `/estados` (páginas placeholder lazy), `''` redireciona para `/municipios`, rota coringa volta para `/municipios`; `proxy.conf.json` para `ng serve`; verificar navegação manual — commit `feat(frontend): shell e rotas das duas telas`
- [ ] 3.3 [FRONT] `CensoApiService.health()` + indicador discreto de "API indisponível" no shell; verificar desligando o back-end — commit `feat(frontend): verificação de saúde da api`
- [ ] 3.4 [QA] Teste do `CensoApiService.health()` com `HttpTestingController`; verificar `npm test` verde — commit `test(frontend): servico de api`
- [ ] 3.5 [FRONT] `frontend/Dockerfile` multi-stage + `nginx.conf` (fallback SPA + proxy `/api/`); verificar `docker build frontend` — commit `build(frontend): dockerfile nginx com proxy`

## 4. Orquestração

- [ ] 4.1 [BACK] `docker-compose.yml` com `backend` (healthcheck) e `frontend` (`depends_on: service_healthy`, `${FRONT_PORT:-8080}:80`); verificar `docker compose up --build` + `curl localhost:8080/api/health` + abrir `localhost:8080/estados/SP` direto — commit `build: docker compose com um comando`
- [ ] 4.2 [QA] Teste de clone limpo: `git clone` para pasta temporária, `docker compose up --build`, validar cenários da spec `infraestrutura` e `git status` limpo; registrar resultado no PR/commit — commit `test: valida subida em clone limpo` (se houver ajuste)
