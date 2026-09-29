# Design

## Context

`uf_resumo` e `municipio_resumo.posicao_densidade_uf` já existem (change `preparacao-dados-censo`), com índice `(cd_uf, posicao_densidade_uf)`. Reusa `IndicadorCardComponent`, `CensoApiService` e a formatação pt-BR da change `busca-municipio`.

## Goals / Non-Goals

**Goals:**
- Ranking de SP (645) e RR (15) com o mesmo custo por requisição (uma página).
- Estado da tela (UF + página) inteiramente na URL.

**Non-Goals:**
- Ordenação configurável, filtros, virtual scroll, cache no front.

## Decisions

### D1. Paginação no servidor por posição pré-calculada
```sql
SELECT posicao_densidade_uf AS posicao, cd_mun, nm_mun, populacao, area_km2, densidade
  FROM municipio_resumo
 WHERE cd_uf = :uf AND consultavel = 1
 ORDER BY posicao_densidade_uf
 LIMIT :por_pagina OFFSET (:pagina - 1) * :por_pagina;
```
`total` vem de `uf_resumo.total_municipios` (sem `COUNT(*)` extra).
- Por que servidor e não cliente: o enunciado pede atenção a listas longas; paginar no servidor mantém payload pequeno e constante e é o comportamento esperado de uma API com listas que podem crescer.
- Alternativa descartada: devolver a UF inteira e paginar no Angular — funciona para 645 linhas, mas acopla o front ao tamanho do dado e não demonstra a preocupação pedida.
- Alternativa descartada: `LengthAwarePaginator` do Laravel — formato de `meta/links` verboso e em inglês; montamos `meta` enxuto no controller.
- Alternativa descartada: cursor/keyset — desnecessário com posição indexada e sem escrita concorrente.

### D2. Rota por sigla, resolvida via `SiglasUf`
`Route::get('ufs/{sigla}', …)->where('sigla', '[A-Za-z]{2}')`. `SiglasUf::codigo(strtoupper($sigla))` → `null` ⇒ `abort(404, 'UF não encontrada.')`. Sigla na URL é mais legível que o código IBGE (`/estados/SP`).

### D3. Validação `RankingUfRequest`
`pagina => sometimes|integer|min:1`, `por_pagina => sometimes|integer|between:1,100`.

### D4. Front — URL como fonte da verdade
- Rota `estados/:sigla?` + query param `pagina`, lidos via `input()` com `withComponentInputBinding()`.
- `BuscaEstadoPage`:
  - `ufs = rxResource(() => api.ufs())` (carrega uma vez).
  - `resumo = rxResource({ params: () => sigla(), stream: … })` — só recarrega quando a sigla muda.
  - `ranking = rxResource({ params: () => ({ sigla: sigla(), pagina: pagina(), porPagina: porPagina() }), stream: … })`.
- `mat-select` → `router.navigate(['/estados', sigla])` (sem `pagina` ⇒ volta para 1).
- `mat-paginator (page)` → `router.navigate([], { queryParams: { pagina }, queryParamsHandling: 'merge' })`. Trocar `por_pagina` também é refletido na URL (`?por_pagina=`), voltando à página 1.
- Alternativa descartada: estado apenas em signals locais — perde deep link e F5.

### D5. Componentes de apresentação
- `UfResumoComponent` — 4 `indicador-card`.
- `RankingMunicipiosComponent` — `mat-table` + `mat-paginator` (`length = meta.total`, `pageIndex = pagina - 1`), barra de progresso `mat-progress-bar` sobreposta durante carregamento (mantém a tabela montada). Nome do município é link para `/municipios/{codigo}` (liga as duas telas sem custo).

## Risks / Trade-offs

- [`pagina` na URL maior que o total] → API devolve `data: []`; a tela mostra "Página vazia" com o paginador permitindo voltar.
- [Sigla em minúsculas no deep link] → a página normaliza para maiúsculas ao comparar com o seletor; a API aceita ambos.
- [Recarregar resumo a cada troca de página] → evitado separando os dois `rxResource` (D4).
