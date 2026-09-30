# Censo 2022 — consulta por município e por estado

[![CI](https://github.com/wfreitass/busca-municipio/actions/workflows/ci.yml/badge.svg)](https://github.com/wfreitass/busca-municipio/actions/workflows/ci.yml)

Aplicação web para consultar os dados do Censo Demográfico 2022 (IBGE) a partir do `censo.sqlite` versionado na raiz.

- **Tela 1 — Municípios** (`/municipios`): autocomplete por nome (sem acento, com homônimos identificados pela UF) e, ao escolher, população, setores (urbanos/rurais), área, densidade e distribuição por sexo.
- **Tela 2 — Estados** (`/estados`): escolha da UF, totais do estado (população, área, densidade) e ranking dos municípios por densidade, paginado no servidor.

Stack: **Laravel 12 (PHP 8.3)** · **Angular 20 + Angular Material** · **SQLite** · **Docker Compose** · specs com **OpenSpec**.

## Como subir

Pré-requisito: apenas **Docker** com **Compose v2**.

```bash
git clone https://github.com/wfreitass/busca-municipio.git
cd busca-municipio
docker compose up --build
```

Abra **http://localhost:8080**. Não há nenhum passo manual: dependências, `APP_KEY` e a preparação do banco acontecem no build das imagens. A primeira subida leva alguns minutos (download de imagens e `npm ci`); as seguintes, segundos.

Porta 8080 ocupada? `FRONT_PORT=9090 docker compose up --build`.

Links diretos úteis: [`/municipios/3550308`](http://localhost:8080/municipios/3550308) (São Paulo) · [`/estados/SP?pagina=2`](http://localhost:8080/estados/SP?pagina=2) · [`/estados/RR`](http://localhost:8080/estados/RR).

## API

Contrato completo: [`docs/api/openapi.yaml`](docs/api/openapi.yaml) (OpenAPI 3.0.3, fonte da verdade). Tudo passa pelo proxy do front, na mesma origem:

| Método e rota | Parâmetros | Respostas |
| --- | --- | --- |
| `GET /api/health` | — | 200 |
| `GET /api/v1/municipios` | `q` (2–60, obrigatório), `limite` (1–20, padrão 10) | 200, 422 |
| `GET /api/v1/municipios/{codigo}` | código IBGE de 7 dígitos | 200, 404 |
| `GET /api/v1/ufs` | — | 200 |
| `GET /api/v1/ufs/{sigla}` | sigla (maiúscula ou minúscula) | 200, 404 |
| `GET /api/v1/ufs/{sigla}/municipios` | `pagina` (≥ 1, padrão 1), `por_pagina` (1–100, padrão 50) | 200, 404, 422 |

```bash
curl 'http://localhost:8080/api/v1/municipios?q=bom%20jesus'
curl  http://localhost:8080/api/v1/municipios/3550308
curl 'http://localhost:8080/api/v1/ufs/SP/municipios?pagina=2'
```

Erros seguem Problem Details (RFC 9457, `application/problem+json`). Respostas 200 são cacheáveis (`Cache-Control: public, max-age=86400` + `ETag`, com `304` na revalidação).

## Testes

```bash
# Back-end (PHPUnit), dentro da imagem já construída — inclui os totais oficiais do IBGE sobre a base real
docker compose run --rm backend php artisan test

# Front-end (Vitest), só com Docker
docker run --rm -v "$PWD/frontend":/app -w /app node:22-alpine sh -c "npm ci && npm test -- --watch=false"
```

| Camada | O que cobre |
| --- | --- |
| Back — unit | normalização de nomes, siglas das UFs |
| Back — feature | regras de agregação (fixture), cada rota (sucesso, 404, 422, ordenação, paginação, cache/304) |
| Back — contrato | respostas reais validadas contra o `openapi.yaml` |
| Back — dados | 27 UFs, 5.570 municípios, 203.080.756 hab., 8.510.417 km² na base real preparada |
| Front — unit | serviço de API (URL e parâmetros), interceptor de erros, painel do município, tabela do ranking |

O CI (GitHub Actions) roda tudo isso a cada push, mais Pint, Larastan nível 8, ESLint, `openspec validate --strict`, lint do OpenAPI e um _smoke test_ com `docker compose up --build` numa máquina limpa.

Além dos testes automatizados, as duas telas foram percorridas num navegador real (Playwright/Chromium sobre o `docker compose`): 26 cenários das specs de tela, incluindo digitação rápida, deep links, paginação, erro com "Tentar novamente" e layout em 390 px.

## Desenvolvimento local (opcional)

Precisa de PHP 8.3 com `pdo_sqlite`, Composer e Node 22.

```bash
# API em :8000
cd backend && composer install && cp .env.example .env && php artisan key:generate
cp ../censo.sqlite database/censo.sqlite && php artisan migrate --seed
DB_DATABASE="$PWD/database/censo.sqlite" php -S localhost:8000 -t public

# SPA em :4200 (proxy de /api para :8000)
cd frontend && npm ci && npm start
```

## Estrutura

```
├── censo.sqlite            # dado original, nunca alterado
├── docker-compose.yml
├── backend/                # Laravel: Models/ (read model), Http/, Busca/ (interface + FTS5), Actions/ (carga), Support/; database/migrations (schema do read model) e database/seeders (carga)
├── frontend/               # Angular: core/ (API, interceptor), shared/, features/{municipio,estado}/
├── docs/
│   ├── ARQUITETURA.md      # decisões (estilo ADR), alternativas descartadas, plano de escala
│   ├── api/openapi.yaml    # contrato HTTP
│   └── prompts/            # prompts por papel (BA, BACK, FRONT, QA) de cada change
└── openspec/               # specs, designs e tarefas (OpenSpec)
```

## Decisões técnicas e por quê

Detalhe completo em [`docs/ARQUITETURA.md`](docs/ARQUITETURA.md). O essencial:

1. **Read model gerado no build com `php artisan migrate --seed`.** A migration cria o schema (`municipio_resumo`, `uf_resumo`, índices e a tabela FTS5 de busca) e o `ReadModelCensoSeeder` agrega os 468 mil setores, calcula a posição no ranking e indexa a busca, **numa cópia** do sqlite dentro da imagem. A API só faz leituras triviais e indexadas pelos Models. *Por quê:* o dado é imutável, então agregar uma vez é mais rápido, simples e testável do que agregar a cada requisição; o `censo.sqlite` da raiz continua intacto. Como `migrate:fresh` e `db:wipe` apagariam também as tabelas cruas (o dado original), esses comandos ficam bloqueados com `DB::prohibitDestructiveCommands()`.
2. **O build falha se os totais não baterem com o IBGE** (27 UFs, 5.570 municípios, 203.080.756 hab., 8.510.417 km²). *Por quê:* erro de agregação vira falha visível, não número errado na tela.
3. **Explorar o dado antes de codar** ([`exploracao.md`](openspec/changes/archive/2026-09-28-preparacao-dados-censo/exploracao.md)). Achados que viraram regra:
   - o 5.571º "município" é o registro `'.'` das lagoas dos Patos e Mirim (RS): fica fora da busca e do ranking, mas sua área (13.085,86 km²) continua somada ao RS, senão a área do Brasil não fecha;
   - a população oficial vem de `setor.populacao`; `demografia.moradores` soma 519 mil a menos;
   - 9.327 setores não têm linha em `demografia` → `LEFT JOIN`, e a diferença vira "não informado" na distribuição por sexo;
   - a tabela `uf` não tem sigla → mapa estático das 27 siglas.
4. **Densidade de agregado = soma da população ÷ soma da área**, nunca média das densidades dos municípios.
5. **Busca com FTS5 trigram atrás de uma interface (`BuscaMunicipios`).** Nome normalizado (sem acento, apóstrofo e hífen), palavras em qualquer ordem ("paulo sao"), termos de 2 letras por `LIKE` (o trigram exige 3), relevância explícita (exato > prefixo > contém, depois população). *Por que não Scout + Meilisearch:* tolera erro de digitação, mas exige outro container e indexação na subida, arriscando o "um comando só". Trocar de motor depois = um novo adaptador e uma linha de binding.
6. **Paginação no servidor com posição pré-calculada.** SP (645) e RR (15) custam o mesmo por requisição; a `posicao` é global, não o índice na página.
7. **Contrato primeiro (OpenAPI):** o front gera os tipos TypeScript dele; o back valida suas respostas contra ele nos testes.
8. **Convenções de API:** `/api/v1`, Problem Details e cache HTTP com `ETag` (dado imutável → cache de graça, pronto para CDN).
9. **Laravel idiomático, sem DDD/Hexagonal.** Models Eloquent sobre o read model, route model binding, FormRequests e API Resources. Uma única interface, na busca (onde a troca de motor é prevista). Schema do read model em migration e carga em seeder; SQL explícito só na agregação em lote. Sem `declare(strict_types=1)`, no estilo do esqueleto do Laravel; tipos garantidos pelo Larastan nível 8.
10. **Front por funcionalidade, estado na URL.** Uma pasta por tela, uma store com signals por rota, componentes de apresentação puros; `/municipios/:codigo` e `/estados/:sigla?pagina=N` fazem deep link e F5 funcionarem.
11. **Nginx do front faz proxy de `/api`**: mesma origem, sem CORS, e só a porta 8080 exposta.

## Como conduzi o SDD com OpenSpec

1. **Especificação antes do código.** Seis changes (hoje arquivadas em `openspec/changes/archive/`, com as specs consolidadas em `openspec/specs/`), cada uma com proposal, specs (cenários WHEN/THEN), design (decisão + alternativa descartada) e tasks, validadas com `openspec validate --strict` (também no CI).
2. **Ordem:** `infraestrutura-base` → `qualidade-e-ci` → `preparacao-dados-censo` → `busca-municipio` → `ranking-estado` → `documentacao-entrega`. Primeiro o que é eliminatório (subir com um comando) e a rede de segurança (CI); depois o dado, do qual as duas telas dependem.
3. **A spec mudou quando o dado contrariou a hipótese**, sempre em commits `docs(spec): …` antes do código: o registro extra das lagoas, a fonte da população, os exemplos reais (o município mais denso de SP é Taboão da Serra, não Diadema), e a descoberta de que o trigram não casa termos de 2 letras.
4. **Prompts por papel** em [`docs/prompts/`](docs/prompts/README.md): para cada change, um bloco para BA (valida a spec contra o dado e o enunciado), BACK, FRONT e QA (testes simples + verificação integrada).
5. `tasks.md` de cada change foi marcado tarefa a tarefa, com um commit Conventional Commits por tarefa.

**Onde voltei atrás:** a infraestrutura e a change de qualidade entraram num único commit grande (`build: infraestrutura base com qualidade e ci`), contra a regra de commits pequenos, e esse commit deixou o CI vermelho porque registrava o comando `censo:preparar` sem versionar a classe. As falhas pareciam instáveis e ganharam retentativas no CI; a causa real era a classe ausente. Corrigi versionando o código da change seguinte, removi as retentativas e daí em diante voltei ao ritmo de um commit por tarefa. O histórico nunca foi compactado (sem squash); a única reescrita foi remover das mensagens uma linha de coautoria automática, mantendo commits, datas e conteúdo.

**Segundo recuo — arquitetura do back-end:** a primeira versão usava Query Builder com DTOs escritos à mão, uma única interface na busca e consultas espalhadas em três pastas (`Censo/`, `Queries/`, `Busca/`). Na revisão ficou claro que era um meio-termo sem critério: nem hexagonal, nem Laravel idiomático. A change `backend-laravel-idiomatico` adotou Models Eloquent e route model binding de forma consistente, sem alterar nenhuma asserção dos testes nem o contrato da API. Na sequência, o comando próprio `censo:preparar` (que misturava criação de schema com carga de dados) deu lugar ao par do Laravel: migration para o schema do read model e seeder para a carga, com `php artisan migrate --seed` no build e `DB::prohibitDestructiveCommands()` protegendo o dado original.

## O que eu faria diferente com mais tempo

- **Commits pequenos desde o início**, sem o commit grande da infraestrutura.
- **Busca tolerante a erro de digitação** com um adaptador Meilisearch (ou Typesense) para `BuscaMunicipios`, indexado num job de build dedicado.
- **Testes E2E versionados** (Playwright) rodando no CI, transformando a verificação manual das 26 situações em regressão automática.
- **Filtro por nome e ordenação por outras colunas** no ranking, e busca da UF por nome.
- **Gráficos** (pirâmide/composição) e um mapa coroplético por densidade.
- **Swagger UI/Redoc** servindo o `openapi.yaml`.
- **Auditoria de acessibilidade** (axe) e testes de navegação por teclado no autocomplete.
- **Imagem de produção enxuta** (sem dependências de desenvolvimento, OPcache ajustado) e publicação das imagens no registry pelo CI.
- **Rate limiting** na API pública.
