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

### D1. Busca com FTS5 trigram, atrás de uma porta (`MunicipioSearch`)
A tabela virtual `municipio_busca` (criada no build, change `preparacao-dados-censo`) indexa `nm_busca` com `tokenize='trigram'`. Validado na imagem `php:8.3-apache` (SQLite 3.46.1) contra o dado real.

Montagem da consulta a partir do termo normalizado (`NormalizadorTexto`, mesma função da preparação):
1. Quebra em palavras. Palavras com **≥ 3 letras** vão para o `MATCH`, **cada uma entre aspas** (`"paulo" "sao"`): o FTS5 faz AND entre elas, **em qualquer ordem**, e as aspas neutralizam a sintaxe do FTS (`-`, `*`, `AND`, `NEAR`) digitada pelo usuário.
2. Palavras com **< 3 letras** (ex.: `d` em `alta floresta d oeste`, ou o termo `sp` inteiro) não geram trigramas: viram filtro `nm_busca LIKE '%w%'` (com escape de `%` e `_`).
3. Se nenhuma palavra tem ≥ 3 letras, a busca é só por `LIKE` (5.570 linhas: < 2 ms).

```sql
SELECT … FROM municipio_busca b
  JOIN municipio_resumo mr ON mr.cd_mun = b.cd_mun
  JOIN uf_resumo u ON u.cd_uf = mr.cd_uf
 WHERE municipio_busca MATCH :match          -- omitido no caso 3
   [AND mr.nm_busca LIKE :curta ESCAPE '\']  -- uma por palavra curta
 ORDER BY CASE WHEN mr.nm_busca = :t THEN 0
               WHEN mr.nm_busca LIKE :t || '%' ESCAPE '\' THEN 1 ELSE 2 END,
          mr.populacao DESC, mr.nm_busca
 LIMIT :limite;
```
- **Relevância é nossa, não do `bm25`.** No teste, `MATCH 'sao paulo'` devolveu `sao paulo de olivenca` antes de `sao paulo`; o `ORDER BY` acima corrige.
- Por que FTS5 e não só `LIKE`: palavras fora de ordem (`paulo sao`, `jesus bom`) e busca indexada que continua rápida se o volume crescer. Nesse volume a latência é igual; o ganho é de comportamento.
- Por que não Meilisearch: tolera erro de digitação, mas exige container a mais e indexação na subida (risco ao "um comando só"). Fica como adaptador futuro da porta abaixo.
- **Porta e adaptador só aqui**: `interface MunicipioSearch { buscar(string $termo, int $limite): list<MunicipioSugestao> }`, implementada por `Fts5MunicipioSearch` e ligada em `AppServiceProvider`. Trocar por `MeilisearchMunicipioSearch` = nova classe + uma linha de binding, sem tocar em controller, contrato ou front. O restante da API **não** ganha interface (não há variação prevista — abstração sem motivo é custo).

### D2. Validação via FormRequest
`BuscarMunicipiosRequest`: `q => required|string|min:2|max:60` (aplicado após `trim`, via `prepareForValidation`), `limite => sometimes|integer|between:1,20`. Resposta 422 em Problem Details pelo handler central (infraestrutura-base D8).

### D3. Detalhe com restrição de rota
`Route::get('municipios/{codigo}', …)->where('codigo', '[0-9]{7}')`. Qualquer outro formato cai no fallback 404 JSON. `MunicipioQuery::detalhe()` retorna `null` → controller lança `abort(404, 'Município não encontrado.')`.

### D4. DTOs `readonly` entre Query e Resource; arredondamento só na borda
`MunicipioQuery::detalhe(string $codigo): ?MunicipioResumo` devolve um DTO `final readonly class` (valores com precisão total, tipados — o Query Builder devolve `stdClass`, e o mapeamento acontece num único ponto, o que mantém o Larastan nível 8 limpo). `MunicipioResumoResource` aplica `round(…, 2)` e calcula percentuais `homens / (homens + mulheres) * 100` (ou `null`).
- Alternativa descartada: devolver `stdClass`/array direto ao Resource — funciona, mas espalha acesso a propriedades não tipadas pela camada HTTP.

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

### D6. Front — URL → página → store → componentes
- `MunicipioStore` (`@Injectable()`, provida em `providers` da rota `/municipios`): `codigo = signal<string|null>()`, `resumo = rxResource({ params: codigo, stream: api.municipio })` e um `computed` `estado: 'ocioso' | 'carregando' | 'sucesso' | 'nao-encontrado' | 'erro'` derivado do recurso e do `ApiErro.status` (404 ⇒ `nao-encontrado`). Método `tentarNovamente()` = `resumo.reload()`.
- `BuscaMunicipioPage` lê `codigo` via `input()` (`withComponentInputBinding()`) e só faz `store.codigo.set(...)`; na seleção do autocomplete, `router.navigate(['/municipios', codigo])`.
- `MunicipioResumoComponent` é apresentação pura (`input.required<MunicipioResumo>()`).
- Erros HTTP chegam como `ApiErro` via `problemaInterceptor` (ver `docs/ARQUITETURA.md` §3).
- Alternativa descartada: estado na própria página — funciona com uma tela, mas mistura navegação, efeitos e estado; a store isola e deixa a página trivial.
- Alternativa descartada: NgRx — cerimônia desproporcional para duas telas.

### D7. Layout dos indicadores
Grid responsivo de `indicador-card` (rótulo + valor + sub-rótulo): População · Setores · Área · Densidade. Abaixo, duas seções: **Situação dos setores** (urbanos/rurais/sem classificação, com barra horizontal proporcional em CSS puro) e **População por sexo** (homens/mulheres/não informado, com barra proporcional). Sem biblioteca de gráficos.
- Alternativa descartada: Chart.js/ngx-charts — dependência extra para duas barras.

### D8. Formatação pt-BR
`registerLocaleData(localePt)` + `LOCALE_ID: 'pt-BR'`; pipes `number:'1.0-0'` (inteiros) e `number:'1.2-2'` (área/densidade). `null` → `—` via pipe simples `valorOuTraco`.

## Risks / Trade-offs

- [Termo com caracteres especiais `%`/`_`] → escape no LIKE (coberto por teste).
- [Selecionar opção e depois editar o texto] → ao editar, o resumo exibido permanece até nova seleção (comportamento previsível, sem limpar a tela de surpresa).
- [Muitos homônimos, ex.: "Santa"] → limite 10 + ordenação por população deixa os mais relevantes visíveis; usuário refina digitando.
