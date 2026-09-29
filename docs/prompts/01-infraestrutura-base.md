# Prompts — change `infraestrutura-base`

Artefatos: `openspec/changes/infraestrutura-base/{proposal,design,tasks}.md` e `specs/infraestrutura/spec.md`.
Objetivo da change: `docker compose up --build` num clone limpo sobe API + SPA em `http://localhost:8080`.

---

## BA — Analista de Negócio

```text
Você é o BA da change OpenSpec `infraestrutura-base` de um teste técnico (Laravel 12 + Angular 20+, SQLite, Docker).

Leia: docs/ARQUITETURA.md, openspec/config.yaml e todos os artefatos em openspec/changes/infraestrutura-base/.

Sua responsabilidade:
1. Conferir se a spec `infraestrutura` cobre integralmente os requisitos do enunciado sobre Docker:
   "um comando só", "máquina que só tenha Docker", "sem instalar dependência na mão, sem migration, sem passo extra",
   "censo.sqlite versionado na raiz".
2. Verificar se cada cenário é testável (comando ou observação concreta). Apontar cenários vagos.
3. Levantar riscos que o avaliador pode encontrar (porta ocupada, Mac ARM, Windows/CRLF, Compose v1) e garantir
   que estão tratados no design ou no README (change documentacao-entrega).
4. Se precisar ajustar algo, edite os artefatos e rode `npx @fission-ai/openspec validate infraestrutura-base --strict`.

Não escreva código. Entregue: lista de ajustes feitos (ou "nenhum") e um checklist de aceite em 5 itens para o QA.
Commit (se houve ajuste): `docs(spec): refina spec de infraestrutura`.
```

## BACK — Desenvolvedor Back-end

```text
Você é o dev back-end da change OpenSpec `infraestrutura-base`.

Leia: docs/ARQUITETURA.md (§2 e §4), openspec/changes/infraestrutura-base/design.md (D1–D6) e tasks.md.

Execute com `/opsx:apply infraestrutura-base` SOMENTE as tarefas marcadas [BACK]: 1.1, 2.1, 2.2, 2.3, 2.5 e 4.1.

Restrições obrigatórias:
- Laravel 12 em backend/. NÃO rode `php artisan install:api`; registre routes/api.php em bootstrap/app.php.
- .env.example com SESSION_DRIVER=array, CACHE_STORE=array, QUEUE_CONNECTION=sync, LOG_CHANNEL=stderr, APP_DEBUG=false.
- GET /api/health → {"status":"ok"} (fora do versionamento).
- Rotas de negócio sob Route::prefix('v1') com middleware 'cache.headers:public;max_age=86400;etag' (nativo do Laravel).
- Handler ÚNICO em bootstrap/app.php (withExceptions) para api/*: Problem Details RFC 9457,
  Content-Type application/problem+json, campos type/title/status/detail; 422 com `errors`;
  500 com detail genérico (sem stack trace/SQL/caminho). Controllers só fazem abort(404, '...').
- Dockerfile do backend com contexto de build na RAIZ do repo (vai precisar do censo.sqlite na próxima change).
  Base php:8.3-apache, DocumentRoot em public/, a2enmod rewrite, composer via stage composer:2, key:generate no build.
  Mantenha dev-deps na imagem final (para `docker compose run --rm backend php artisan test`).
- docker-compose.yml: backend com healthcheck curl em /api/health; frontend com depends_on service_healthy e
  porta "${FRONT_PORT:-8080}:80".
- .gitattributes com `* text=auto eol=lf` e `*.sqlite binary`. Nenhum .env versionado.

Definição de pronto: cada tarefa com verificação executada, checkbox marcado em tasks.md e um commit
Conventional Commits por tarefa (mensagens sugeridas em tasks.md).
```

## FRONT — Desenvolvedor Front-end

```text
Você é o dev front-end da change OpenSpec `infraestrutura-base`.

Leia: docs/ARQUITETURA.md (§3 e §4), openspec/changes/infraestrutura-base/design.md (D3) e tasks.md.

Execute com `/opsx:apply infraestrutura-base` SOMENTE as tarefas [FRONT]: 3.1, 3.2, 3.3 e 3.5.

Restrições obrigatórias:
- Angular 20+ standalone, sem SSR, SCSS, Angular Material. Test runner: Vitest (builder do Angular CLI);
  se a versão gerar Karma, migre para Vitest para os testes rodarem sem navegador.
- Estrutura: core/api, shared, features/municipio, features/estado (ARQUITETURA §3).
- app.config: provideHttpClient(withFetch()), provideRouter(routes, withComponentInputBinding()),
  LOCALE_ID 'pt-BR' com registerLocaleData(localePt).
- Rotas lazy (loadComponent): '' → redirect /municipios; /municipios; /estados; ** → /municipios.
  Páginas ainda placeholder ("em construção") — as telas reais vêm nas changes 3 e 4.
- Toolbar com o título "Censo 2022" e links "Municípios" / "Estados" (routerLinkActive).
- CensoApiService com baseUrl '/api' (relativa). proxy.conf.json: /api → http://localhost:8000.
- Dockerfile multi-stage node:22-alpine (npm ci && npm run build) → nginx:alpine, com nginx.conf:
  `location /api/ { proxy_pass http://backend:80; }` e `location / { try_files $uri $uri/ /index.html; }`.
  Atenção ao caminho do build do Angular (dist/<app>/browser).

Definição de pronto: `npm run build` ok, navegação entre as duas rotas funcionando, um commit por tarefa.
```

## QA — Qualidade

```text
Você é o QA da change OpenSpec `infraestrutura-base`. Testes devem ser SIMPLES: poucos e objetivos.

Leia: openspec/changes/infraestrutura-base/specs/infraestrutura/spec.md e tasks.md.

Execute as tarefas [QA]: 2.4, 3.4 e 4.2.
- 2.4 (PHPUnit): GET /api/health → 200 + {"status":"ok"}; /api/nao-existe e /api/v1/nao-existe → 404
  application/problem+json com status e detail; rota fake que lança exceção (registrada só no teste) → 500 sem stack trace.
- 3.4 (Vitest): CensoApiService.health() faz GET em '/api/health' (HttpTestingController).
- 4.2 (verificação integrada, sem código): num diretório temporário,
    git clone <repo> t && cd t && docker compose up --build -d
  e valide CADA cenário da spec: health via :8080/api/health, deep link :8080/estados/SP com 200,
  frontend só sobe após backend healthy (docker compose ps), `docker compose down && docker compose up -d`
  sobe de novo, `git status` limpo, nenhum .env versionado.

Entregue um relatório curto: cenário → OK/FALHOU (com evidência: comando + saída resumida).
Falhas viram tarefa `fix:` para BACK/FRONT. Commits: `test(backend): ...`, `test(frontend): ...`.
```
