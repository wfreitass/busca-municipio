# Design

## Context

Ver `proposal.md`. As tabelas cruas (`uf`, `municipio`, `setor`, `demografia`) já existem no `censo.sqlite` e **não** são criadas por migration: são o dado de entrada. As migrations cuidam apenas do que a aplicação cria em cima dele.

## Goals / Non-Goals

**Goals:**
- Schema do read model declarado em migration (Schema Builder); carga em seeder; um único comando: `php artisan migrate --seed --force`.
- Mesmos dados, mesmos totais, mesma API; testes existentes passam sem alterar asserções de comportamento.

**Non-Goals:**
- Migrations para as tabelas cruas (não pertencem à aplicação).
- Agregar via Eloquent linha a linha (seriam 468 mil hidratações; o `INSERT … SELECT` continua em SQL dentro da Action).

## Decisions

### D1. Migration só com schema
- `Schema::create('municipio_resumo', …)` e `Schema::create('uf_resumo', …)` com `string('cd_mun')->primary()`, colunas tipadas e `index(['cd_uf', 'posicao_densidade_uf'])`, `index(['consultavel', 'nm_busca'])`.
- `Schema::table('setor', fn ($t) => $t->index('cd_mun'))`: índice de apoio à agregação.
- A tabela FTS5 usa `DB::statement('CREATE VIRTUAL TABLE municipio_busca USING fts5(…)')`, porque o Schema Builder não tem tabelas virtuais. Continua dentro da migration: é schema.
- `WITHOUT ROWID` deixa de ser usado no read model; era otimização irrelevante para 5.571 linhas e não é suportado pelo Schema Builder.
- Alternativa descartada: agregação dentro da migration. Misturar carga de dados com schema é o anti-padrão, e impediria reexecutar a carga sem mexer no schema.

### D2. Seeder chama a Action; a Action usa a conexão do Laravel
- `ReadModelCensoSeeder::run(PrepararBaseCenso $preparar)` → `$preparar->handle(validarTotais: config('censo.validar_totais'))`.
- A Action usa `DB::transaction()`, `DB::table(...)->delete()` para limpar (idempotência), `DB::statement('INSERT … SELECT …')` para agregar, `DB::table('municipio_resumo')->update(...)` para `nm_busca`/`sigla_uf`, e os Models (`Uf::count()`, `Uf::sum()`, `Municipio::consultaveis()->count()`) para validar os totais.
- `config/censo.php` com `validar_totais` (`env('CENSO_VALIDAR_TOTAIS', true)`). O `phpunit.xml` desliga para as fixtures; a imagem e o CI validam.

### D3. Proteção do dado original
`DB::prohibitDestructiveCommands()` no `AppServiceProvider::boot()`, sempre (não só em produção). `migrate:fresh` e `db:wipe` apagam **todas** as tabelas do arquivo, inclusive as cruas; o read model pode ser refeito, o dado original não.

### D4. Um caminho de banco para todos os ambientes
`config/database.php` → `'database' => env('DB_DATABASE', database_path('censo.sqlite'))`, e `.env.example` sem `DB_DATABASE`. Na imagem, `database_path()` já aponta para `/var/www/html/database/censo.sqlite`; no CI e localmente, para `backend/database/censo.sqlite`.

### D5. Testes
O trait `UsaBaseCenso` cria o SQLite de fixture com as tabelas cruas, aponta a conexão para ele e roda `migrate` + `db:seed --class=ReadModelCensoSeeder`. O teste de idempotência roda o seeder duas vezes. O teste de totais oficiais passa a usar os Models numa conexão apontada para `database_path('censo.sqlite')`.

## Risks / Trade-offs

- [Alguém rodar `migrate:fresh` localmente] → bloqueado por D3, com mensagem de erro do próprio Laravel.
- [Tabela `migrations` criada dentro do SQLite] → é a cópia dentro da imagem; o `censo.sqlite` da raiz continua intocado.
- [Rodar o seeder sem migrar antes] → o erro "no such table" é explícito; o README e o Dockerfile usam sempre `migrate --seed`.
