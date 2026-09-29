# Arquitetura — Censo 2022 por Município e UF

> Documento de decisão (estilo ADR) que orienta todas as changes em `openspec/changes/`.
> Timebox do teste: **2h30**. Toda decisão abaixo foi tomada pensando nisso.

## 1. Recomendação

**Monorepo com SPA Angular desacoplada + API REST Laravel em camadas enxutas, lendo um _read model_ pré-computado no build da imagem (CQRS-lite).**

```
                    docker compose up --build
┌──────────────────────────────┐        ┌───────────────────────────────────────────┐
│ frontend (nginx:alpine)      │        │ backend (php:8.3-apache + Laravel 12)     │
│  - serve o build do Angular  │ /api/* │  Controller → FormRequest → Query → Resource│
│  - proxy /api → backend:80   ├───────►│                   │                        │
│  porta 8080 (host)           │        │                   ▼ (somente leitura)      │
└──────────────────────────────┘        │  database/censo.sqlite  (CÓPIA preparada)  │
                                        │   ├─ tabelas originais (intocadas)          │
                                        │   ├─ municipio_resumo  (read model)        │
                                        │   └─ uf_resumo         (read model)        │
                                        └───────────────▲───────────────────────────┘
                                                        │ docker build: php artisan censo:preparar
                                        ./censo.sqlite (raiz, versionado, NUNCA alterado)
```

### Por que essa arquitetura

| Força do problema | Como a arquitetura responde |
| --- | --- |
| Dado **somente leitura**, estático, 468 mil setores | Agregar **uma vez**, no build, e servir consultas triviais. Não há escrita, logo não há por que pagar o custo de uma arquitetura de domínio rica. |
| Regras de agregação cheias de armadilhas (nulos, LEFT JOIN, densidade ponderada, município extra) | As regras ficam concentradas **num único lugar** (`censo:preparar`), testáveis e auditáveis, em vez de espalhadas em SQL de cada endpoint. |
| SP tem 645 municípios; ranking por densidade | O ranking (inclusive a **posição**) é pré-calculado; a API pagina com `LIMIT/OFFSET` sobre um índice `(cd_uf, posicao)`. Resposta em milissegundos independente do tamanho da UF. |
| "Subir com um comando, sem passo manual" | A preparação roda no `Dockerfile` (build). O container sobe já pronto: sem migration, sem seed, sem volume para inicializar. |
| O `censo.sqlite` deve continuar versionado e íntegro | O build trabalha sobre uma **cópia** dentro da imagem. `git status` continua limpo depois de subir. |
| Duas telas distintas, front em framework JS | SPA Angular com duas rotas lazy, consumindo uma API REST com contrato explícito (testável dos dois lados). |
| 2h30 de prazo | Poucas camadas, nenhum framework extra, Angular Material pronto para autocomplete/tabela/paginador. |

### Alternativas descartadas

| Alternativa | Por que não |
| --- | --- |
| **Clean Architecture / Hexagonal / DDD completo** (entidades, ports, adapters, use cases) | Não há regra de negócio transacional nem escrita. Seriam ~15 arquivos de cerimônia para 4 endpoints de leitura — consumiria o timebox sem ganho de qualidade observável. Mantemos só a separação que traz valor: HTTP ↔ consulta ↔ preparação de dados. |
| **Agregar em tempo de requisição** (só criando índices) | Funciona, mas cada request de UF grande faz `GROUP BY` sobre dezenas de milhares de setores e as regras de nulos/densidade se repetem em várias queries. Mais lento e mais fácil de errar. |
| **Preparar o banco no _entrypoint_ (runtime) ou via migration** | Aumenta o tempo de subida, precisa de volume gravável e pode gerar estado inconsistente entre reinícios. No build é determinístico e cacheado pelo Docker. |
| **Alterar o `censo.sqlite` da raiz** (índices/tabelas direto no arquivo versionado) | Suja o `git status`, muda o binário versionado a cada subida e quebra a premissa "dado cru entregue". |
| **Eloquent Models** | Tabelas `WITHOUT ROWID` com PK texto e acesso 100% leitura: Query Builder é mais direto e explícito. |
| **Laravel + Inertia / Blade (monólito)** | O pedido é front em framework JS separado; SPA + API deixa o contrato claro e testável isoladamente. |
| **Postgres/MySQL** | O enunciado exige SQLite; um servidor de banco só adicionaria um container e um passo de carga. |
| **Git LFS para o sqlite** | 35 MB está abaixo do limite do GitHub (100 MB). LFS exigiria `git lfs` na máquina do avaliador — viola "máquina só com Docker". |

## 2. Back-end (Laravel 12, PHP 8.3)

```
backend/
├── app/
│   ├── Console/Commands/PrepararCenso.php      # censo:preparar (ETL idempotente)
│   ├── Censo/
│   │   ├── PreparadorBaseCenso.php             # SQL de agregação (regras de negócio do dado)
│   │   ├── SiglasUf.php                        # mapa cd_uf → sigla (27 UFs)
│   │   └── NormalizadorTexto.php               # "Olho-d'Água" → "olho d agua"
│   ├── Queries/
│   │   ├── MunicipioQuery.php                  # busca (autocomplete) e detalhe
│   │   └── UfQuery.php                         # lista, resumo e ranking paginado
│   └── Http/
│       ├── Controllers/Api/{Health,Municipio,Uf}Controller.php
│       ├── Requests/{BuscarMunicipiosRequest,RankingUfRequest}.php
│       └── Resources/…                          # formato estável do JSON
├── routes/api.php                               # registrado em bootstrap/app.php
├── tests/{Unit,Feature}/
└── Dockerfile                                   # contexto de build = raiz do repo
```

Camadas e responsabilidades:

1. **Controller** — só orquestra: recebe o request validado, chama a Query, devolve o Resource.
2. **FormRequest** — validação de entrada (`q`, `limit`, `page`, `per_page`) com 422 padrão do Laravel.
3. **Query (repositório de leitura)** — SQL via Query Builder **apenas sobre o read model**.
4. **Resource** — contrato JSON (nomes em pt-BR, `snake_case`, códigos IBGE como _string_).
5. **Censo/** — tudo que é conhecimento do dado: preparação, siglas, normalização.

Pontos de atenção do Laravel 11+/12 (evitam perder tempo):

- Não existe `routes/api.php` por padrão. **Não** rodar `install:api` (instala Sanctum e cria migrations). Registrar `api:` em `bootstrap/app.php`.
- O `.env` padrão usa `database` para session/cache/queue → falha num SQLite só leitura. Usar `SESSION_DRIVER=array`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`.
- `APP_KEY` é gerada no build (`key:generate`) — não há segredo versionado.

## 3. Front-end (Angular 20+, standalone, signals, Angular Material)

```
frontend/src/app/
├── core/
│   ├── api/censo-api.service.ts     # única porta de saída HTTP (baseUrl '/api')
│   └── api/censo.models.ts          # interfaces espelhando o contrato da API
├── shared/
│   └── indicador-card/              # card "rótulo + valor" reutilizado nas duas telas
├── features/
│   ├── municipio/                   # TELA 1 — rota /municipios(/:codigo)
│   │   ├── busca-municipio.page.ts
│   │   ├── municipio-autocomplete.component.ts
│   │   └── municipio-resumo.component.ts
│   └── estado/                      # TELA 2 — rota /estados(/:sigla)
│       ├── busca-estado.page.ts
│       ├── uf-resumo.component.ts
│       └── ranking-municipios.component.ts
├── app.routes.ts                    # loadComponent (lazy) por tela; '' → /municipios
└── app.config.ts                    # provideHttpClient, LOCALE_ID pt-BR
```

- **Feature-first**: cada tela é uma pasta autocontida; `core` fala com a API; `shared` só tem UI burra.
- **Página (container) × componentes (apresentação)**: a página detém o estado (signals) e a navegação; os componentes recebem `input()` e emitem `output()` — ficam triviais de testar.
- **RxJS só onde brilha**: o autocomplete (`debounceTime` → `distinctUntilChanged` → `switchMap`, cancelando a requisição anterior). O resto é signal.
- **Estado na URL** (`/municipios/:codigo`, `/estados/:sigla?pagina=N`): link compartilhável, F5 não perde a tela, e é o jeito mais simples de testar.
- **Listas longas**: paginação **no servidor** (50 por página, `mat-paginator`). Não usamos virtual scroll porque o total de SP (645) já é paginado pela API.

## 4. Infra (Docker Compose)

- `backend`: `php:8.3-apache`, build multi-stage (composer → runtime), `censo:preparar` no build, _healthcheck_ em `/api/health`.
- `frontend`: build multi-stage (`node:22-alpine` → `nginx:alpine`), `nginx.conf` com fallback SPA (`try_files … /index.html`) e `proxy_pass` de `/api/` para `backend` — **mesma origem, sem CORS**.
- `depends_on: backend: condition: service_healthy`.
- Porta única exposta ao avaliador: **http://localhost:8080**.

## 5. Estratégia de testes (simples, por pedido)

| Camada | Ferramenta | O que cobre |
| --- | --- | --- |
| Back — unit | PHPUnit | `NormalizadorTexto`, `SiglasUf` |
| Back — feature | PHPUnit + fixture SQLite pequena | Cada endpoint: sucesso, 404, 422, ordenação, paginação |
| Back — dados | PHPUnit (grupo `dados`) sobre a base preparada | Totais de conferência do IBGE (27 / 5.570 / 203.080.756 / 8.510.417) |
| Front — unit | Vitest (builder do Angular CLI) | `CensoApiService` com `HttpTestingController`; renderização dos componentes de resumo e ranking |

Execução: `docker compose run --rm backend php artisan test` e `cd frontend && npm test` (detalhado no README).

## 6. Plano de tempo (2h30) × changes OpenSpec

| Ordem | Change | Tempo | Entrega observável |
| --- | --- | --- | --- |
| 1 | `infraestrutura-base` | 25 min | `docker compose up --build` → Angular em :8080 e `/api/health` ok |
| 2 | `preparacao-dados-censo` | 30 min | Read model gerado no build; teste de totais verde |
| 3 | `busca-municipio` | 40 min | Tela 1 funcionando ponta a ponta |
| 4 | `ranking-estado` | 35 min | Tela 2 funcionando ponta a ponta |
| 5 | `documentacao-entrega` | 15 min | README completo; teste em clone limpo |
| — | folga | 5 min | — |

Os prompts por papel (BA, BACK, FRONT, QA) de cada change estão em [`docs/prompts/`](prompts/README.md).
