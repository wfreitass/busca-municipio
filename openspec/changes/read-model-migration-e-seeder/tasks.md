# Tasks

## 1. Schema e carga

- [x] 1.1 [BACK] Migration do read model (D1) e caminho único do banco (D4); verificar `php artisan migrate` numa cópia do `censo.sqlite` — commit `feat(backend): migration do read model do censo`
- [x] 1.2 [BACK] `config/censo.php`, Action usando `DB`/Models só para carga (D2) e `ReadModelCensoSeeder` chamado pelo `DatabaseSeeder`; remover o comando `censo:preparar` — verificar `php artisan migrate --seed` com os totais do IBGE — commit `refactor(backend): carga do read model via seeder`
- [ ] 1.3 [BACK] `DB::prohibitDestructiveCommands()` (D3); verificar que `php artisan migrate:fresh` é recusado — commit `feat(backend): bloqueia comandos que apagariam o dado original`

## 2. Build, CI e testes

- [x] 2.1 [QA] Testes com `migrate` + seeder nas fixtures (D5), sem alterar asserções de comportamento; verificar `php artisan test` — commit `test(backend): fixtures preparadas por migration e seeder`
- [x] 2.2 [BACK] Dockerfile e CI com `php artisan migrate --seed --force` — verificar `docker compose build backend` — commit `build: read model gerado por migrate --seed`

## 3. Documentação e verificação

- [ ] 3.1 [BA] Atualizar `README.md`, `docs/ARQUITETURA.md` e `openspec/config.yaml` — commit `docs: read model por migration e seeder`
- [ ] 3.2 [QA] Clone limpo + `docker compose up --build` + verificação das duas telas no navegador + suítes de teste
