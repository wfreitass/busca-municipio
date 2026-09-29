# Tasks

## 1. Back-end

- [ ] 1.1 [BACK] `pint.json` (preset laravel + `declare_strict_types`) e script `composer lint`; rodar `vendor/bin/pint` uma vez no código existente; verificar `composer lint` verde — commit `chore(backend): pint com strict types`
- [ ] 1.2 [BACK] `larastan/larastan` nível 8 (`phpstan.neon`, paths `app` e `tests`) e script `composer analyse`; verificar verde — commit `chore(backend): larastan nivel 8`

## 2. Front-end

- [ ] 2.1 [FRONT] `ng add @angular-eslint/schematics` e script `lint`; verificar `npm run lint` verde — commit `chore(frontend): angular-eslint`

## 3. CI

- [ ] 3.1 [QA] `.github/workflows/ci.yml` com jobs `specs`, `backend`, `frontend` (design D3); verificar a execução verde no GitHub após o push — commit `ci: specs, lint, analise estatica e testes`
- [ ] 3.2 [QA] Job `docker` (`compose up --build --wait` + smoke `curl` em health, deep link da SPA e uma rota de negócio assim que existir); verificar verde — commit `ci: smoke test da subida com docker compose`
- [ ] 3.3 [BA] Badge do workflow no topo do README (criar README mínimo se ainda não existir) — commit `docs: badge de ci`
