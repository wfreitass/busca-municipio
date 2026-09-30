# Design

## Context

Ver `proposal.md`. O read model (`municipio_resumo`, `uf_resumo`, `municipio_busca`) continua sendo gerado no build por `censo:preparar`; esta change só troca a forma como a API **lê** esse read model e reorganiza pastas.

## Goals / Non-Goals

**Goals:**
- Uma única convenção para acesso a dados na API: Models Eloquent.
- Nenhuma mudança observável: mesmos JSONs, códigos HTTP, cabeçalhos de cache e mensagens de erro; todos os testes existentes (incluindo validação contra o OpenAPI) passam sem alterar asserções.

**Non-Goals:**
- Hexagonal/DDD (avaliado e descartado: ~25 arquivos para 5 rotas de leitura).
- Migrations para o read model (ele é artefato de build, não esquema evolutivo da aplicação).

## Decisions

### D1. Models sobre o read model, não sobre as tabelas cruas
`Municipio` → `municipio_resumo`, `Uf` → `uf_resumo`. As tabelas cruas (`setor`, `demografia`) só interessam ao ETL; expô-las como Models convidaria a agregar em tempo de requisição, que é o que o read model evita.
- Config: `$primaryKey` texto, `$incrementing = false`, `$keyType = 'string'`, `$timestamps = false`, `$casts` numéricos.
- Tipos para o Larastan via `@property` no PHPDoc (sem migrations, ele não infere colunas).
- Alternativa descartada: manter Query Builder + DTOs — funciona, mas duplica o que o Eloquent já dá (hidratação, casts, relações, binding) e foge do idioma do framework.

### D2. Route model binding com `->missing()`
- `Route::get('/municipios/{municipio}', …)` com `Municipio::resolveRouteBinding()` restrito a `consultaveis()` e carregando `uf` (eager load).
- `Route::get('/ufs/{uf}', …)` com `Uf::getRouteKeyName() = 'sigla'` e `resolveRouteBinding()` em maiúsculas.
- `->missing(fn () => abort(404, '…'))` mantém as mensagens da spec ("Município não encontrado.", "UF não encontrada.") sem vazar o nome da classe do Model (mensagem padrão do `ModelNotFoundException`).

### D3. Ranking sem `paginate()`
`$uf->municipios()->consultaveis()->rankingDensidade()->forPage($pagina, $porPagina)->get()`, com `meta` montado a partir de `uf.total_municipios`. O `paginate()` do Laravel geraria `links`/`meta` em outro formato, quebrando o contrato OpenAPI, e faria um `COUNT(*)` desnecessário.

### D4. Busca continua atrás de interface
Único ponto com troca de motor prevista (FTS5 hoje; Meilisearch como evolução). O adaptador passa a montar a consulta com o builder do Model (`Municipio::query()->join('municipio_busca', …)->whereRaw('municipio_busca MATCH ?', …)`) e devolve `Collection<Municipio>` — o resto da API só conhece Models. Colunas qualificadas (`municipio_resumo.nm_busca`) por causa do `JOIN` com a tabela FTS, que também tem `nm_busca`.

### D5. ETL como Action
`App\Actions\PrepararBaseCenso` concentra o SQL de agregação. SQL explícito é a ferramenta certa aqui: é um processo batch sobre 468 mil setores, com window function e tabela FTS5, rodado uma vez no build — não é acesso a dados da aplicação.

### D6. `strict_types` removido
`sed` remove `declare(strict_types=1);` (e a linha em branco seguinte) de `app/`, `bootstrap/`, `config/`, `routes/`, `database/` e `tests/`; regra `declare_strict_types` sai do `pint.json`. Não existe equivalente global no PHP; a garantia de tipos passa a ser o Larastan nível 8 no CI.

## Risks / Trade-offs

- [Larastan reclamar de propriedades mágicas dos Models] → `@property` explícito em cada Model.
- [Ambiguidade de coluna no `JOIN` com a FTS] → qualificar colunas; coberto pelos testes de busca existentes.
- [Sem `strict_types`, coerções silenciosas em runtime (ex.: `"12"` → `12`)] → aceito; Larastan 8 cobre a maior parte estaticamente.
