# Proposal

## Why

O avaliador vai ler o código e o histórico. Qualidade que depende de disciplina manual se perde em 2h30 de pressão; qualidade automatizada fica. Além disso, o requisito eliminatório ("sobe com um comando numa máquina limpa") só é **provado** por uma máquina limpa de verdade, e um runner de CI é exatamente isso. Por isso esta change entra logo depois da infraestrutura: todo código escrito depois já nasce passando em lint, análise estática e testes.

## What Changes

- Back-end: `declare(strict_types=1)` obrigatório, **Pint** (estilo) e **Larastan nível 8** (análise estática), com scripts Composer `lint`, `analyse` e `test`.
- Front-end: **angular-eslint**, TypeScript `strict` (padrão do CLI, mantido) e script `lint`.
- **GitHub Actions** (`.github/workflows/ci.yml`) com 4 jobs em paralelo:
  1. `specs`: `openspec validate --all --strict` + `redocly lint` do contrato OpenAPI.
  2. `backend`: Pint `--test`, Larastan, testes (incluindo o grupo `dados` sobre a base real preparada).
  3. `frontend`: tipos gerados do contrato sem diferença (`git diff --exit-code`), lint, testes, build.
  4. `docker`: `docker compose up --build --wait` + _smoke test_ via `curl` em `:8080` — a mesma coisa que o avaliador fará.
- Badge de CI no README.

## Capabilities

### New Capabilities
- Nenhuma (ferramental de qualidade; sem mudança de comportamento observável — `skip_specs: true`).

### Modified Capabilities
- Nenhuma.

## Impact

- `backend/composer.json`, `backend/phpstan.neon`, `backend/pint.json`, `frontend/eslint.config.js`, `frontend/package.json`, `.github/workflows/ci.yml`.
- Custo estimado: ~15 min. Linha de corte (se o prazo apertar): o job `docker` é o último a ser removido; Larastan pode baixar para nível 6 com justificativa no README.
- Fora de escopo: deploy, publicação de imagens, cobertura mínima obrigatória, hooks de pre-commit.
