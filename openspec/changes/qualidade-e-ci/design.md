# Design

## Context

Depende de `infraestrutura-base` (projetos Laravel/Angular e `docker-compose.yml` existentes). Ver `proposal.md` para a motivação.

## Goals / Non-Goals

**Goals:**
- Um `push` prova: specs válidas, contrato válido, código analisado, testes verdes e **subida com um comando numa máquina limpa**.
- Tempo total de CI < 6 min.

**Non-Goals:**
- Meta de cobertura, mutation testing, SonarQube, pre-commit hooks.

## Decisions

### D1. PHP: Pint + Larastan nível 8 + `strict_types`
- `pint.json`: preset `laravel` + regra `declare_strict_types: true` (Pint insere/garante o `declare` em todo arquivo).
- `phpstan.neon`: `larastan/larastan`, `level: 8`, paths `app/` e `tests/`. Nível 8 = checagem de null rigorosa, adequado a um código pequeno e novo. Nível máximo (9/10) exige tipagem de `mixed` do Query Builder e custa tempo sem ganho proporcional.
- DTOs `final readonly class` (ex.: `MunicipioResumoDados`) retornados pelas Queries tornam o nível 8 barato: o Query Builder devolve `stdClass`; o mapeamento para DTO acontece num único ponto por consulta.
- Scripts: `composer lint` (pint --test), `composer analyse` (phpstan), `composer test` (artisan test).
- Alternativa descartada: PHP-CS-Fixer puro — Pint é o padrão do ecossistema Laravel e já vem no skeleton.

### D2. Front: angular-eslint + `strict`
`ng add @angular-eslint/schematics` (flat config). Mantidos `strict`, `strictTemplates` e `noUncheckedIndexedAccess: false` (padrão). Sem Prettier para não gastar tempo com formatação (o `.editorconfig` do CLI basta).

### D3. GitHub Actions — 4 jobs paralelos
```yaml
on: [push, pull_request]
jobs:
  specs:    # node 22
    - npx @fission-ai/openspec@1.13.2 validate --all --strict
    - npx @redocly/cli lint docs/api/openapi.yaml
  backend:  # shivammathur/setup-php@v2 (8.3, pdo_sqlite), cache composer
    - composer install; composer lint; composer analyse
    - cp ../censo.sqlite database/censo.sqlite && php artisan censo:preparar
    - composer test   # inclui @group dados
  frontend: # node 22, cache npm
    - npm ci; npm run api:tipos && git diff --exit-code src/app/core/api/schema.ts
    - npm run lint; npm test -- --watch=false; npm run build
  docker:
    - docker compose up --build --wait --wait-timeout 180
    - curl -fs localhost:8080/api/health
    - curl -fs localhost:8080/api/v1/ufs/SP | jq -e '.data.total_municipios == 645'
    - curl -fs -o /dev/null -w '%{http_code}' localhost:8080/estados/SP   # 200 (fallback SPA)
    - docker compose down
```
- `docker compose up --wait` bloqueia até os healthchecks passarem — mesmo critério de "subiu" que usamos na spec `infraestrutura`.
- O job `specs` roda o `openspec validate` no CI: a spec é tratada como artefato de primeira classe, não como documentação solta.
- `git diff --exit-code` nos tipos gerados impede que o contrato e o front divirjam silenciosamente.
- Alternativa descartada: um único job sequencial — mais lento e mistura falhas de naturezas diferentes.

## Risks / Trade-offs

- [`censo.sqlite` de 35 MB no checkout] → `actions/checkout` sem LFS baixa normalmente; custo aceitável.
- [Larastan nível 8 travar o ritmo] → linha de corte: baixar para 6 e registrar no README.
- [Flakiness do job docker em runners lentos] → `--wait-timeout 180` e healthcheck com `retries: 20`.
