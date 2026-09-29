# Design

## Context

O read model `municipio_resumo` (change `preparacao-dados-censo`) já tem tudo pré-calculado, inclusive `nm_busca` e `sigla_uf`. Esta change só lê. Ver `docs/ARQUITETURA.md` §2 e §3 para a organização de pastas.

## Goals / Non-Goals

**Goals:**
- Autocomplete com resposta < 50 ms na API e UX fluida (debounce + cancelamento).
- Contrato JSON único compartilhado entre `MunicipioResource` (PHP) e `censo.models.ts` (TS).

**Non-Goals:**
- Full-text search (FTS5), fuzzy/typo tolerance, cache HTTP.

## Decisions

### D1. Busca com `LIKE` sobre `nm_busca` (sem FTS)
Com 5.570 linhas, um scan com `LIKE '%termo%'` custa poucos ms. A relevância vem de um `CASE` no `ORDER BY`:
```sql
SELECT … FROM municipio_resumo mr JOIN uf_resumo u ON u.cd_uf = mr.cd_uf
 WHERE mr.consultavel = 1 AND mr.nm_busca LIKE '%' || :t || '%'
 ORDER BY CASE WHEN mr.nm_busca = :t THEN 0
               WHEN mr.nm_busca LIKE :t || '%' THEN 1 ELSE 2 END,
          mr.populacao DESC, mr.nm_busca
 LIMIT :limite;
```
`:t` = `NormalizadorTexto::normalizar(q)` (mesma função usada na preparação — garante simetria). Escapar `%` e `_` do termo (`ESCAPE '\'`).
- Alternativa descartada: FTS5 — exige tabela virtual e tokenização; ganho imperceptível nesse volume.
- Alternativa descartada: buscar prefixo por palavra (`% termo%`) — decidimos "contém" por ser mais tolerante (ex.: `paulo` encontra `São Paulo`).

### D2. Validação via FormRequest
`BuscarMunicipiosRequest`: `q => required|string|min:2|max:60` (aplicado após `trim`, via `prepareForValidation`), `limite => sometimes|integer|between:1,20`. Resposta 422 padrão do Laravel (`message` + `errors`).

### D3. Detalhe com restrição de rota
`Route::get('municipios/{codigo}', …)->where('codigo', '[0-9]{7}')`. Qualquer outro formato cai no fallback 404 JSON. `MunicipioQuery::detalhe()` retorna `null` → controller lança `abort(404, 'Município não encontrado.')`.

### D4. Arredondamento só na borda (Resource)
Read model guarda valores com precisão total; `MunicipioResource` aplica `round(…, 2)`. Percentuais: `homens / (homens + mulheres) * 100`.

### D5. Front — fluxo do autocomplete
```ts
termo$ = toObservable(this.termo).pipe(
  map(t => t.trim()), debounceTime(300), distinctUntilChanged(),
  filter(t => t.length >= 2),
  tap(() => this.carregando.set(true)),
  switchMap(t => this.api.buscarMunicipios(t).pipe(catchError(() => of([])))),
  tap(() => this.carregando.set(false)));
```
- `mat-autocomplete` com `[displayWith]` retornando `rotulo`; `optionSelected` → `router.navigate(['/municipios', codigo])`.
- Quando o valor do controle vira objeto (item selecionado) o stream ignora (`filter(typeof === 'string')`).

### D6. Front — página dirige o estado pela URL
`BuscaMunicipioPage` lê `codigo` via `input()` (`withComponentInputBinding()`), e um `effect`/`rxResource` carrega `GET /api/municipios/{codigo}`. Estados: `ocioso | carregando | sucesso | nao-encontrado | erro`. `MunicipioResumoComponent` é puramente de apresentação (`input.required<MunicipioResumo>()`).

### D7. Layout dos indicadores
Grid responsivo de `indicador-card` (rótulo + valor + sub-rótulo): População · Setores · Área · Densidade. Abaixo, duas seções: **Situação dos setores** (urbanos/rurais/sem classificação, com barra horizontal proporcional em CSS puro) e **População por sexo** (homens/mulheres/não informado, com barra proporcional). Sem biblioteca de gráficos.
- Alternativa descartada: Chart.js/ngx-charts — dependência extra para duas barras.

### D8. Formatação pt-BR
`registerLocaleData(localePt)` + `LOCALE_ID: 'pt-BR'`; pipes `number:'1.0-0'` (inteiros) e `number:'1.2-2'` (área/densidade). `null` → `—` via pipe simples `valorOuTraco`.

## Risks / Trade-offs

- [Termo com caracteres especiais `%`/`_`] → escape no LIKE (coberto por teste).
- [Selecionar opção e depois editar o texto] → ao editar, o resumo exibido permanece até nova seleção (comportamento previsível, sem limpar a tela de surpresa).
- [Muitos homônimos, ex.: "Santa"] → limite 10 + ordenação por população deixa os mais relevantes visíveis; usuário refina digitando.
