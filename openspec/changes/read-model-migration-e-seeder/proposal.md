# Proposal

## Why

O read model é gerado por um comando Artisan próprio (`censo:preparar`) que mistura criação de schema (`DROP`/`CREATE TABLE`, índices, tabela FTS5) com carga de dados (`INSERT … SELECT` sobre 468 mil setores) e abre uma conexão PDO paralela à do Laravel. O projeto adotou como regra seguir sempre o idioma do Laravel: schema vive em **migrations**, carga de dados em **seeders**, e ambos rodam com `php artisan migrate --seed`, que qualquer dev Laravel reconhece.

## What Changes

- **Migration** `database/migrations/…_cria_read_model_do_censo.php`: cria `municipio_resumo`, `uf_resumo`, os índices do read model, a tabela FTS5 `municipio_busca` e o índice de apoio `setor(cd_mun)` usado na agregação; `down()` desfaz só o que ela criou.
- **Seeder** `database/seeders/ReadModelCensoSeeder.php` (chamado pelo `DatabaseSeeder`): popula o read model chamando a Action `PrepararBaseCenso`, que passa a usar a conexão do Laravel (`DB`) e os Models, e continua validando os totais do IBGE.
- Removidos o comando `censo:preparar` e seu registro no `AppServiceProvider`.
- **`DB::prohibitDestructiveCommands()`**: bloqueia `migrate:fresh`, `migrate:refresh`, `migrate:reset`, `migrate:rollback` e `db:wipe`, que apagariam também as tabelas cruas do censo (dado original, irrecuperável a partir do read model).
- Caminho do banco padronizado em `config/database.php` (`database_path('censo.sqlite')`), sem `DB_DATABASE` fixo no `.env.example`, para funcionar igual na imagem, no CI e localmente.
- Dockerfile e CI trocam `censo:preparar` por `php artisan migrate --seed --force`.

## Capabilities

### New Capabilities
- Nenhuma.

### Modified Capabilities
- Nenhuma. O comportamento observável (read model, totais, API) não muda; a spec `dados-censo` descreve regras, não o mecanismo (`skip_specs: true`).

## Impact

- `backend/database/{migrations,seeders}`, `backend/app/Actions/PrepararBaseCenso.php`, `backend/app/Console/` (removido), `backend/app/Providers/AppServiceProvider.php`, `backend/config/database.php`, `backend/.env.example`, `backend/Dockerfile`, `.github/workflows/ci.yml`, testes que preparavam fixtures via comando.
- Documentação: `README.md`, `docs/ARQUITETURA.md`, `openspec/config.yaml`.
