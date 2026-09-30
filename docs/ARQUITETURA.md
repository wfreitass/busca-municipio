# Arquitetura — Censo 2022 por Município e UF

> Documento de decisão (estilo ADR) que orienta todas as changes do OpenSpec (arquivadas em `openspec/changes/archive/`; specs consolidadas em `openspec/specs/`).
> Timebox do teste: **2h30**. Toda decisão abaixo foi pesada contra esse prazo.

## 1. Recomendação

**Monorepo com SPA Angular desacoplada e API REST em Laravel idiomático (Models Eloquent, route model binding, API Resources), contrato primeiro (OpenAPI), lendo um _read model_ imutável pré-computado no build da imagem (CQRS-lite).**

```
                         docker compose up --build
┌───────────────────────────────┐          ┌──────────────────────────────────────────────┐
│ frontend (nginx:alpine)       │          │ backend (php:8.3-apache + Laravel 12)        │
│  SPA Angular (2 telas lazy)   │  /api/*  │  Controller → FormRequest → Model → Resource │
│  proxy /api → backend:80      ├─────────►│  (route model binding, Problem Details, ETag)│
│  porta 8080                   │          │                      │ somente leitura       │
└───────────────────────────────┘          │                      ▼                       │
          ▲ tipos TS gerados               │  database/censo.sqlite (CÓPIA preparada)     │
          │                                │   ├─ tabelas originais (intocadas)           │
   docs/api/openapi.yaml ──────────────────┤   ├─ municipio_resumo / uf_resumo (read model)│
   (contrato primeiro)   valida respostas  │   └─ municipio_busca (FTS5 trigram)          │
                                           └───────────────────────▲──────────────────────┘
                                                                   │ docker build: migrate --seed
                                   ./censo.sqlite (raiz, versionado, NUNCA alterado)
```

### Por que essa arquitetura

| Força do problema | Como a arquitetura responde |
| --- | --- |
| Dado **somente leitura** e estático (468 mil setores) | Agregar **uma vez**, no build, e servir consultas triviais. Sem escrita não há por que pagar uma arquitetura de domínio rica. |
| Regras de agregação cheias de armadilhas (nulos, LEFT JOIN, densidade ponderada, registro `'.'` das lagoas) | Regras concentradas num único passo (`ReadModelCensoSeeder` → `PrepararBaseCenso`), testado com fixture e **validado contra os totais do IBGE — o build falha se não bater**. |
| SP tem 645 municípios, RR tem 15 | Posição no ranking pré-calculada; API pagina por índice `(cd_uf, posicao)`. Mesmo custo por requisição em qualquer UF. |
| Busca por nome com acento, apóstrofo, homônimos | Nome normalizado + FTS5 trigram (palavras em qualquer ordem), atrás de uma **porta** trocável. |
| "Sobe com um comando, sem passo manual" | Preparação no `Dockerfile`: o container já nasce pronto. O CI roda o mesmo comando numa máquina limpa a cada push. |
| Front e back evoluindo em paralelo | **Contrato primeiro**: OpenAPI escrito com as specs; front gera tipos dele, back é testado contra ele. |
| 2h30 de prazo | Poucas camadas, abstração só onde há variação real, Angular Material pronto. |

### Alternativas descartadas

| Alternativa | Por que não |
| --- | --- |
| **Clean Architecture / Hexagonal / DDD completo** | Sem escrita e sem regra transacional, seriam ~25 arquivos (entidades, portas de repositório, casos de uso, adaptadores) para 5 rotas de leitura. Seguimos o idioma do Laravel de forma consistente e usamos uma interface **só na busca**, o único ponto com troca de motor prevista (FTS5 hoje, Meilisearch amanhã). |
| **Scout + Meilisearch** | Ganha tolerância a erro de digitação, mas exige um container a mais e indexação na subida, arriscando o requisito eliminatório. Fica como implementação futura de `BuscaMunicipios`. |
| **Agregar em tempo de requisição** | `GROUP BY` sobre dezenas de milhares de setores por request e regras duplicadas em várias queries. |
| **Preparar o banco no _entrypoint_ (na subida do container)** | Subida mais lenta, volume gravável, estado inconsistente entre reinícios. `migrate --seed` roda no **build** da imagem: o container já nasce pronto. |
| **Agregação dentro da migration** | Misturaria carga de dados com schema e impediria refazer a carga sem mexer no schema. Migration só cria tabelas/índices; o seeder carrega. |
| **Alterar o `censo.sqlite` da raiz** | Suja o `git status` e altera o dado entregue. |
| **Query Builder + DTOs escritos à mão** | Foi a primeira versão. Na revisão, virou um meio-termo sem critério (nem hexagonal, nem Laravel): DTOs duplicavam o que o Eloquent já dá (hidratação, casts, relações, route binding) e as consultas ficaram em três pastas. Substituído por Models (change `backend-laravel-idiomatico`). |
| **Models sobre as tabelas cruas** (`setor`, `demografia`) | Convidaria a agregar em tempo de requisição. Os Models apontam para o read model; as tabelas cruas só existem para o ETL. |
| **Redis / cache de aplicação** | O dado é imutável: cache HTTP (`ETag` + `Cache-Control`) resolve sem novo serviço. |
| **Microsserviços / BFF** | Um domínio, um time, 5 rotas. |
| **Git LFS para o sqlite** | 35 MB cabem no GitHub; LFS exigiria ferramenta extra na máquina do avaliador. |

## 2. Back-end (Laravel 12, PHP 8.3, Larastan nível 8)

```
backend/app/
├── Models/                                # Eloquent sobre o read model (somente leitura)
│   ├── Municipio.php                      # municipio_resumo · belongsTo Uf · scopes consultaveis(), rankingDensidade()
│   └── Uf.php                             # uf_resumo · hasMany Municipio · route key = sigla (sem distinção de caixa)
├── Http/
│   ├── Controllers/Api/V1/{Municipio,Uf}Controller.php   # recebem Models por route model binding
│   ├── Requests/{BuscarMunicipiosRequest,RankingUfRequest}.php
│   └── Resources/…                        # Model → JSON do contrato (docs/api/openapi.yaml)
├── Busca/                                 # única interface do back-end
│   ├── BuscaMunicipios.php                # contrato: termo → Collection<Municipio>
│   └── Fts5BuscaMunicipios.php            # implementação atual (binding no AppServiceProvider)
├── Actions/
│   └── PrepararBaseCenso.php              # carga do read model: agrega, normaliza, indexa, valida totais
├── Support/
│   ├── SiglasUf.php                       # cd_uf ↔ sigla (27 UFs)
│   └── NormalizadorTexto.php              # "Olho-d'Água" → "olho d agua" (build e request)

backend/database/
├── migrations/…_cria_read_model_do_censo.php   # schema: municipio_resumo, uf_resumo, índices, FTS5 municipio_busca
└── seeders/ReadModelCensoSeeder.php          # carga: chama Actions\PrepararBaseCenso (via DatabaseSeeder)
```

Fluxo de uma requisição: **Route** (binding do Model; `->missing()` gera o 404 com a mensagem da spec) → **FormRequest** (valida, 422) → **Controller** (Model/scopes ou `BuscaMunicipios`) → **API Resource** (forma do JSON, arredondamento na borda).

Por que cada peça está onde está:
- **Models só leem o read model.** Toda regra de agregação (nulos, `LEFT JOIN`, densidade ponderada, registro `'.'`) já foi aplicada no build; em runtime os Models só filtram, ordenam e paginam.
- **Schema em migration, carga em seeder** (`php artisan migrate --seed`, no build). As tabelas cruas do censo não têm migration: são o dado de entrada, não schema da aplicação.
- **SQL explícito só na agregação em lote** (`Actions/PrepararBaseCenso`, chamada pelo seeder): `INSERT … SELECT` e window function sobre 468 mil setores — hidratar Models linha a linha seria ordens de grandeza mais lento. O resto da carga (nomes normalizados, validação dos totais) usa os Models
- **Dado original protegido:** `DB::prohibitDestructiveCommands()` bloqueia `migrate:fresh`, `migrate:reset/refresh/rollback` e `db:wipe`, que apagariam também as tabelas cruas do arquivo — o read model pode ser refeito, o dado original não.
- **Ranking com `forPage()`, não `paginate()`**: mantém o `meta` do contrato e evita um `COUNT(*)`, porque o total já está em `uf_resumo`.
- **Tipagem**: sem `declare(strict_types=1)` (estilo do esqueleto do Laravel; o PHP não tem configuração global para isso, a declaração é por arquivo). A garantia de tipos é o Larastan nível 8 no CI, com `@property` documentando as colunas dos Models.

Convenções transversais (change `infraestrutura-base`, capability `convencoes-api`):
- **`/api/v1`** para negócio; `/api/health` fora do versionamento.
- **Problem Details (RFC 9457)** num handler único em `bootstrap/app.php` — controllers só fazem `abort(404, '…')`.
- **Cache HTTP** com o middleware nativo `cache.headers:public;max_age=86400;etag` (304 com `If-None-Match`).

Armadilhas do Laravel 11+/12 já tratadas no design: não rodar `install:api`; `SESSION_DRIVER=array`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`; `APP_KEY` gerada no build.

## 3. Front-end (Angular 20+, standalone, signals, Angular Material)

```
frontend/src/app/
├── core/
│   ├── api/schema.ts                  # GERADO de docs/api/openapi.yaml (openapi-typescript)
│   ├── api/censo.models.ts            # aliases legíveis dos tipos gerados
│   ├── api/censo-api.service.ts       # única porta HTTP (baseUrl '/api/v1')
│   └── http/problema.interceptor.ts   # HttpErrorResponse → ApiErro {status, titulo, detalhe}
├── shared/
│   └── indicador-card/, valor-ou-traco.pipe.ts
├── features/
│   ├── municipio/                     # TELA 1 — /municipios/:codigo
│   │   ├── municipio.store.ts         # estado da tela (signals), provido na rota
│   │   ├── busca-municipio.page.ts    # liga URL ↔ store
│   │   ├── municipio-autocomplete.component.ts
│   │   └── municipio-resumo.component.ts
│   └── estado/                        # TELA 2 — /estados/:sigla?pagina=N
│       ├── estado.store.ts
│       ├── busca-estado.page.ts
│       ├── uf-resumo.component.ts
│       └── ranking-municipios.component.ts
├── app.routes.ts                      # loadComponent (lazy) + providers da store por rota
└── app.config.ts                      # provideHttpClient(withInterceptors), LOCALE_ID pt-BR
```

| Decisão | Por quê |
| --- | --- |
| **Feature-first** (`features/municipio`, `features/estado`) | Duas telas distintas por exigência; cada uma autocontida e lazy. Uma 3ª tela é uma pasta nova. |
| **Store por feature** (serviço com signals, `providers` na rota) | Estado e efeitos (carregar, tentar novamente, erro) saem da página; a página só liga URL ↔ store; componentes são apresentação pura. Escala sem NgRx: o padrão já está pronto para crescer. |
| **Tipos gerados do OpenAPI** | Contrato muda ⇒ build quebra onde o front ficou para trás. Zero interface escrita à mão. |
| **Interceptor de Problem Details** | Um só lugar traduz erros da API para `ApiErro`; as stores só distinguem `404` (não encontrado) de falha (erro com retry). |
| **RxJS só no autocomplete** | `debounceTime` + `switchMap` cancelam a requisição anterior; o resto é signal/`rxResource`. |
| **URL como fonte da verdade** | Deep link, F5 e testes triviais (`/estados/SP?pagina=2`). |
| **Paginação no servidor + `mat-paginator`** | Payload constante para SP (645) ou RR (15). |
| **Angular Material** | Autocomplete acessível, tabela e paginador prontos. |

## 4. Infra e qualidade

- `backend`: `php:8.3-apache`, `php artisan migrate --seed --force` no build, healthcheck em `/api/health`.
- `frontend`: `node:22-alpine` → `nginx:alpine`, fallback SPA + proxy `/api/` (mesma origem, sem CORS).
- `depends_on: service_healthy`; porta `${FRONT_PORT:-8080}`.
- **CI (GitHub Actions)**: `openspec validate --strict` + lint do OpenAPI · Pint + Larastan 8 + testes · lint + testes + build do front + tipos sem diff · `docker compose up --build --wait` + smoke test.

## 5. Estratégia de testes (simples, por pedido)

| Camada | Ferramenta | O que cobre |
| --- | --- | --- |
| Back — unit | PHPUnit | `NormalizadorTexto`, `SiglasUf` |
| Back — feature | PHPUnit + fixture SQLite pequena | Cada rota: sucesso, 404, 422, ordenação, paginação, cache (304) |
| Back — contrato | PHPUnit + `osteel/openapi-httpfoundation-testing` | Respostas reais validadas contra `docs/api/openapi.yaml` |
| Back — dados | PHPUnit (`@group dados`) na base real preparada | 27 UFs · 5.570 municípios · 203.080.756 hab. · 8.510.417 km² |
| Front — unit | Vitest | Serviço de API (URL/params), componentes de resumo e ranking |

## 6. Como escala (e onde mexer)

1. **Horizontal**: API sem estado e dado imutável ⇒ N réplicas atrás de um balanceador, sem coordenação.
2. **Borda**: `ETag` + `Cache-Control: public` permitem que navegador, nginx ou CDN respondam sem tocar no PHP.
3. **Busca**: volume ou exigência maior (tolerância a erro de digitação) ⇒ novo adaptador `MeilisearchBuscaMunicipios` + uma linha de binding. Controller, contrato e front intactos.
4. **Dados**: nova edição do censo ou troca de SQLite por Postgres ⇒ muda só a migration/seeder do read model (e o driver da conexão). Os Models, a API e o front não mudam.
5. **API**: mudança incompatível ⇒ `/api/v2` convivendo com `/api/v1`, contrato versionado no OpenAPI.
6. **Front**: nova tela ⇒ nova pasta em `features/` com sua store; `core` e `shared` reaproveitados.

## 7. Plano de tempo (2h30) × changes OpenSpec

| Ordem | Change | Tempo | Entrega observável |
| --- | --- | --- | --- |
| 1 | `infraestrutura-base` | 30 min | `docker compose up --build` → SPA em :8080, `/api/health`, Problem Details, cache HTTP |
| 2 | `qualidade-e-ci` | 15 min | CI verde: specs, contrato, lint, Larastan, testes, subida Docker |
| 3 | `preparacao-dados-censo` | 30 min | Read model + FTS5 no build; totais do IBGE validados |
| 4 | `busca-municipio` | 35 min | Tela 1 ponta a ponta |
| 5 | `ranking-estado` | 30 min | Tela 2 ponta a ponta |
| 6 | `documentacao-entrega` | 10 min | README; ensaio em clone limpo |

**Linha de corte** (se atrasar, nesta ordem): Larastan 8 → 6 · teste de contrato só nas rotas de município · job `docker` do CI por último. **Nunca cortar**: as duas telas completas, subida com um comando, testes simples, README.

Os prompts por papel (BA, BACK, FRONT, QA) de cada change estão em [`docs/prompts/`](prompts/README.md).
