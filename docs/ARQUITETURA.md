# Arquitetura — Censo 2022 por Município e UF

> Documento de decisão (estilo ADR) que orienta todas as changes em `openspec/changes/`.
> Timebox do teste: **2h30**. Toda decisão abaixo foi pesada contra esse prazo.

## 1. Recomendação

**Monorepo com SPA Angular desacoplada e API REST Laravel em camadas enxutas, contrato primeiro (OpenAPI), lendo um _read model_ imutável pré-computado no build da imagem (CQRS-lite).**

```
                         docker compose up --build
┌───────────────────────────────┐          ┌──────────────────────────────────────────────┐
│ frontend (nginx:alpine)       │          │ backend (php:8.3-apache + Laravel 12)        │
│  SPA Angular (2 telas lazy)   │  /api/*  │  Controller → FormRequest → Query|Search → DTO│
│  proxy /api → backend:80      ├─────────►│        → Resource  (Problem Details, ETag)   │
│  porta 8080                   │          │                      │ somente leitura       │
└───────────────────────────────┘          │                      ▼                       │
          ▲ tipos TS gerados               │  database/censo.sqlite (CÓPIA preparada)     │
          │                                │   ├─ tabelas originais (intocadas)           │
   docs/api/openapi.yaml ──────────────────┤   ├─ municipio_resumo / uf_resumo (read model)│
   (contrato primeiro)   valida respostas  │   └─ municipio_busca (FTS5 trigram)          │
                                           └───────────────────────▲──────────────────────┘
                                                                   │ docker build: censo:preparar
                                   ./censo.sqlite (raiz, versionado, NUNCA alterado)
```

### Por que essa arquitetura

| Força do problema | Como a arquitetura responde |
| --- | --- |
| Dado **somente leitura** e estático (468 mil setores) | Agregar **uma vez**, no build, e servir consultas triviais. Sem escrita não há por que pagar uma arquitetura de domínio rica. |
| Regras de agregação cheias de armadilhas (nulos, LEFT JOIN, densidade ponderada, registro `'.'` das lagoas) | Regras concentradas num único passo (`censo:preparar`), testado com fixture e **validado contra os totais do IBGE — o build falha se não bater**. |
| SP tem 645 municípios, RR tem 15 | Posição no ranking pré-calculada; API pagina por índice `(cd_uf, posicao)`. Mesmo custo por requisição em qualquer UF. |
| Busca por nome com acento, apóstrofo, homônimos | Nome normalizado + FTS5 trigram (palavras em qualquer ordem), atrás de uma **porta** trocável. |
| "Sobe com um comando, sem passo manual" | Preparação no `Dockerfile`: o container já nasce pronto. O CI roda o mesmo comando numa máquina limpa a cada push. |
| Front e back evoluindo em paralelo | **Contrato primeiro**: OpenAPI escrito com as specs; front gera tipos dele, back é testado contra ele. |
| 2h30 de prazo | Poucas camadas, abstração só onde há variação real, Angular Material pronto. |

### Alternativas descartadas

| Alternativa | Por que não |
| --- | --- |
| **Clean Architecture / Hexagonal / DDD completo** | Sem escrita e sem regra transacional, seriam ~15 arquivos de cerimônia para 5 rotas de leitura. Aplicamos porta/adaptador **só na busca**, o único ponto com variação previsível (FTS5 hoje, Meilisearch amanhã). Senioridade é saber onde a abstração se paga. |
| **Scout + Meilisearch** | Ganha tolerância a erro de digitação, mas exige um container a mais e indexação na subida, arriscando o requisito eliminatório. Fica como adaptador futuro de `MunicipioSearch`. |
| **Agregar em tempo de requisição** | `GROUP BY` sobre dezenas de milhares de setores por request e regras duplicadas em várias queries. |
| **Preparar o banco no _entrypoint_ ou via migration** | Subida mais lenta, volume gravável, estado inconsistente entre reinícios. |
| **Alterar o `censo.sqlite` da raiz** | Suja o `git status` e altera o dado entregue. |
| **Eloquent Models** | Tabelas `WITHOUT ROWID`, PK texto, 100% leitura: Query Builder + DTOs `readonly` é mais explícito e tipável. |
| **Redis / cache de aplicação** | O dado é imutável: cache HTTP (`ETag` + `Cache-Control`) resolve sem novo serviço. |
| **Microsserviços / BFF** | Um domínio, um time, 5 rotas. |
| **Git LFS para o sqlite** | 35 MB cabem no GitHub; LFS exigiria ferramenta extra na máquina do avaliador. |

## 2. Back-end (Laravel 12, PHP 8.3, `strict_types`, Larastan nível 8)

```
backend/app/
├── Censo/                                 # conhecimento do dado (ETL)
│   ├── PreparadorBaseCenso.php            # agrega, normaliza, indexa, valida totais
│   ├── SiglasUf.php                       # cd_uf ↔ sigla (27 UFs)
│   └── NormalizadorTexto.php              # "Olho-d'Água" → "olho d agua" (build e request)
├── Busca/                                 # ÚNICA porta/adaptador do projeto
│   ├── MunicipioSearch.php                # interface
│   └── Fts5MunicipioSearch.php            # adaptador atual (binding no AppServiceProvider)
├── Queries/                               # leitura do read model → DTOs
│   ├── MunicipioQuery.php
│   └── UfQuery.php
├── Dados/                                 # DTOs `final readonly class`
│   └── MunicipioSugestao.php, MunicipioResumo.php, UfResumo.php, ItemRanking.php, Pagina.php
├── Http/
│   ├── Controllers/Api/V1/{Municipio,Uf}Controller.php, Api/HealthController.php
│   ├── Requests/{BuscarMunicipiosRequest,RankingUfRequest}.php
│   └── Resources/…                        # contrato JSON (espelha docs/api/openapi.yaml)
└── Console/Commands/PrepararCenso.php     # censo:preparar
```

Fluxo de uma requisição: **Controller** (orquestra) → **FormRequest** (valida, 422) → **Query/Search** (SQL no read model → DTO tipado) → **Resource** (forma do JSON, arredondamento na borda).

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

- `backend`: `php:8.3-apache`, `censo:preparar` no build, healthcheck em `/api/health`.
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
3. **Busca**: volume ou exigência maior (tolerância a erro de digitação) ⇒ novo adaptador `MeilisearchMunicipioSearch` + uma linha de binding. Controller, contrato e front intactos.
4. **Dados**: nova edição do censo ou troca de SQLite por Postgres ⇒ muda só `censo:preparar` e as Queries. O read model isola o resto.
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
