# Prompts — change `qualidade-e-ci`

Artefatos: `openspec/changes/qualidade-e-ci/{proposal,design,tasks}.md` (`skip_specs: true`).
Objetivo da change: todo `push` prova specs válidas, contrato válido, código analisado, testes verdes e subida com um comando numa máquina limpa.

---

## BA — Analista de Negócio

```text
Você é o BA da change OpenSpec `qualidade-e-ci`.

Leia: openspec/changes/qualidade-e-ci/ e o enunciado ("subir com Docker numa máquina limpa", "commits pequenos",
"testes automatizados").

1. Confirme que o job `docker` do CI reproduz exatamente o que o avaliador fará (clone limpo + um comando)
   e que o job `specs` roda `openspec validate --all --strict` — a spec é artefato verificado, não enfeite.
2. Tarefa 3.3: badge do workflow no topo do README (crie um README mínimo com título + badge se ainda não existir;
   o README completo vem na change documentacao-entrega).
3. Registre em design.md, se usada, a linha de corte (Larastan 8 → 6) com o motivo.

Não escreva código de aplicação. Commit: `docs: badge de ci`.
```

## BACK — Desenvolvedor Back-end

```text
Você é o dev back-end da change OpenSpec `qualidade-e-ci`.

Leia: openspec/changes/qualidade-e-ci/design.md (D1) e tasks.md. Execute com `/opsx:apply qualidade-e-ci`
SOMENTE as tarefas [BACK]: 1.1 e 1.2.

- pint.json: {"preset":"laravel","rules":{"declare_strict_types":true}}. Rode `vendor/bin/pint` no código
  existente num commit separado do de configuração se o diff for grande.
- larastan/larastan (dev), phpstan.neon com level: 8, paths: [app, tests].
  Se o Query Builder retornar stdClass/mixed, NÃO use ignoreErrors genéricos: mapeie para DTOs
  `final readonly class` num único ponto (padrão que as changes seguintes vão usar).
- Scripts composer: "lint": "pint --test", "analyse": "phpstan analyse --memory-limit=1G", "test": "@php artisan test".

Definição de pronto: `composer lint && composer analyse && composer test` verdes. Um commit por tarefa.
```

## FRONT — Desenvolvedor Front-end

```text
Você é o dev front-end da change OpenSpec `qualidade-e-ci`.

Leia: openspec/changes/qualidade-e-ci/design.md (D2) e tasks.md. Execute a tarefa [FRONT] 2.1:
`ng add @angular-eslint/schematics` (flat config), script "lint", corrigir o que aparecer no código existente.
Mantenha "strict": true e "strictTemplates": true no tsconfig. Sem Prettier.
Verifique também que `npm test -- --watch=false` roda sem navegador (Vitest) — o CI depende disso.

Definição de pronto: `npm run lint && npm test -- --watch=false && npm run build` verdes. Commit: `chore(frontend): angular-eslint`.
```

## QA — Qualidade

```text
Você é o QA da change OpenSpec `qualidade-e-ci`. Execute as tarefas [QA] 3.1 e 3.2.

Crie .github/workflows/ci.yml conforme design.md D3, com 4 jobs paralelos:
- specs:    npx @fission-ai/openspec@1.13.2 validate --all --strict ; npx @redocly/cli lint docs/api/openapi.yaml
- backend:  shivammathur/setup-php@v2 (php 8.3, extensão pdo_sqlite), cache do composer;
            composer install, composer lint, composer analyse,
            cp ../censo.sqlite database/censo.sqlite && php artisan censo:preparar (se o comando já existir), composer test
- frontend: actions/setup-node (22, cache npm); npm ci; npm run api:tipos && git diff --exit-code src/app/core/api/schema.ts;
            npm run lint; npm test -- --watch=false; npm run build
- docker:   docker compose up --build --wait --wait-timeout 180; curl -fs localhost:8080/api/health;
            curl de status 200 em localhost:8080/estados/SP (fallback SPA); docker compose down.
            Deixe comentado o smoke `curl -fs localhost:8080/api/v1/ufs/SP | jq -e '.data.total_municipios == 645'`
            e descomente-o na change ranking-estado.

Passos que dependem de changes futuras (censo:preparar) devem ser condicionais ou adicionados depois — o CI
precisa ficar verde a cada push. Verifique no GitHub (gh run list / gh run view) que o workflow ficou verde.
Commits: `ci: specs, lint, analise estatica e testes` e `ci: smoke test da subida com docker compose`.
```
