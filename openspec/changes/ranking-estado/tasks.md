# Tasks

## 1. Contrato

- [ ] 1.1 [BA] Conferir na base preparada: SP = 645 e RR = 15 municípios, e o município mais denso de SP; ajustar exemplos da spec `api-estados` se necessário; verificar `openspec validate ranking-estado` — commit `docs(spec): confirma exemplos do ranking por uf`
- [ ] 1.2 [FRONT] Interfaces `Uf`, `UfResumo`, `ItemRanking`, `Pagina<T>` em `core/api/censo.models.ts` — commit `feat(frontend): modelos do contrato de ufs`

## 2. API

- [ ] 2.1 [BACK] `UfQuery::listar()` + `GET /api/v1/ufs` — verificar com `curl localhost:8000/api/v1/ufs | jq '.data | length'` = 27 — commit `feat(backend): lista de ufs`
- [ ] 2.2 [BACK] `UfQuery::resumo()` + `GET /api/v1/ufs/{sigla}` (D2) — commit `feat(backend): resumo da uf`
- [ ] 2.3 [BACK] `UfQuery::ranking()` + `RankingUfRequest` + `GET /api/v1/ufs/{sigla}/municipios` com `meta` (D1/D3); verificar `curl 'localhost:8000/api/v1/ufs/SP/municipios?pagina=2' | jq '.data[0].posicao'` = 51 — commit `feat(backend): ranking paginado por densidade`
- [ ] 2.4 [QA] Feature tests com fixture: 27 UFs (ou as da fixture) ordenadas por nome; resumo 200/404; sigla minúscula; ranking ordenado decrescente; posição global na página 2; última página parcial; página além do fim → `[]`; `por_pagina=500`/`pagina=0` → 422; densidade nula ao final — commit `test(backend): api de ufs e ranking`

## 3. Tela

- [ ] 3.1 [FRONT] `CensoApiService.ufs()`, `.uf(sigla)`, `.ranking(sigla, pagina, porPagina)` — commit `feat(frontend): servico de ufs`
- [ ] 3.2 [FRONT] `BuscaEstadoPage` com seletor de UF e rota `/estados/:sigla?` + `?pagina` (D4) — commit `feat(frontend): tela de busca por estado`
- [ ] 3.3 [FRONT] `UfResumoComponent` com 4 cards — commit `feat(frontend): totais do estado`
- [ ] 3.4 [FRONT] `RankingMunicipiosComponent` com `mat-table` + `mat-paginator` (25/50/100) + loading sobreposto + link para `/municipios/{codigo}` (D5); verificar SP página 2 → posições 51–100 — commit `feat(frontend): ranking paginado de municipios`
- [ ] 3.5 [FRONT] Estados "Estado não encontrado", erro com "Tentar novamente" e página vazia — commit `feat(frontend): estados de erro da tela de estados`
- [ ] 3.6 [QA] Testes Vitest simples: serviço envia `pagina`/`por_pagina` corretos; `RankingMunicipiosComponent` renderiza posições recebidas (ex.: 51) e `length` do paginador = `meta.total`; verificar `npm test` — commit `test(frontend): tela de estados`

## 4. Verificação integrada

- [ ] 4.1 [QA] `docker compose up --build` e percorrer os cenários da spec `tela-busca-estado` com SP (645), RR (15) e deep link `/estados/rr?pagina=1`; registrar falhas como tarefas de `fix:`
