# Tasks

## 1. Contrato

- [ ] 1.1 [BA] Revisar a spec `api-municipios` contra `exploracao.md` (exemplos de homônimos e apóstrofos reais; população real de São Paulo) e ajustar exemplos se necessário; verificar `openspec validate busca-municipio` — commit `docs(spec): confirma exemplos reais da busca de municipios`
- [ ] 1.2 [FRONT] Criar interfaces `MunicipioSugestao`, `MunicipioResumo`, `UfRef` em `core/api/censo.models.ts` espelhando o JSON da spec — commit `feat(frontend): modelos do contrato de municipios`

## 2. API

- [ ] 2.1 [BACK] Porta `MunicipioSearch` + adaptador `Fts5MunicipioSearch` (MATCH por palavra entre aspas, palavras < 3 letras via LIKE, relevância no ORDER BY) + binding no `AppServiceProvider` + `BuscarMunicipiosRequest` + `GET /api/v1/municipios` (design D1/D2); verificar `curl '…/municipios?q=paulo%20sao'` e `q=sp` — commit `feat(backend): busca de municipios com fts5 trigram`
- [ ] 2.2 [BACK] `MunicipioQuery::detalhe()` + `MunicipioResource` + `GET /api/v1/municipios/{codigo}` (D3/D4); verificar com `curl localhost:8000/api/v1/municipios/3550308` — commit `feat(backend): resumo do municipio`
- [ ] 2.3 [QA] Feature tests com fixture: busca sem acento, homônimos com siglas distintas, apóstrofo, palavras fora de ordem (`paulo sao`), termo de 2 letras (`sp`), sintaxe FTS digitada (`sao* OR -paulo` → 200), `q` curto → 422, `limite=50` → 422, vazio → `[]`, ordenação exato > prefixo > contém, detalhe 200 com soma de setores, 404 inexistente, 404 mal formado, 404 não consultável; verificar `php artisan test` — commit `test(backend): api de municipios`

## 3. Tela

- [ ] 3.1 [FRONT] `CensoApiService.buscarMunicipios()` e `.municipio()`; verificar com teste do item 3.5 — commit `feat(frontend): servico de municipios`
- [ ] 3.2 [FRONT] `MunicipioAutocompleteComponent` (D5) com loading, "Nenhum município encontrado", emissão de `selecionado`; verificar manualmente digitando `bom jesus` — commit `feat(frontend): autocomplete de municipios`
- [ ] 3.3 [FRONT] `IndicadorCardComponent` (shared) + `MunicipioResumoComponent` (D7/D8) — commit `feat(frontend): painel de indicadores do municipio`
- [ ] 3.4 [FRONT] `BuscaMunicipioPage` com rota `/municipios/:codigo?`, estados carregando/não encontrado/erro + "Tentar novamente" (D6); verificar deep link e F5 — commit `feat(frontend): tela de busca de municipios`
- [ ] 3.5 [QA] Testes Vitest simples: serviço monta URL/params corretos (`HttpTestingController`); `MunicipioResumoComponent` renderiza população formatada `11.451.999`, `—` para densidade nula e oculta "sem classificação" quando 0; verificar `npm test` — commit `test(frontend): tela de municipios`

## 4. Verificação integrada

- [ ] 4.1 [QA] `docker compose up --build` e percorrer manualmente todos os cenários da spec `tela-busca-municipio` (incluindo derrubar o backend para o cenário de erro); registrar falhas como tarefas de `fix:`
