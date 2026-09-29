# Prompts — change `busca-municipio`

Artefatos: `openspec/changes/busca-municipio/{proposal,design,tasks}.md`, `specs/api-municipios/spec.md`, `specs/tela-busca-municipio/spec.md`.
Objetivo da change: **Tela 1** completa — autocomplete com UF + painel de indicadores do município.

---

## BA — Analista de Negócio

```text
Você é o BA da change OpenSpec `busca-municipio`.

Leia: o enunciado (primeira tela: autocomplete por nome, identificar UF em homônimos, exibir população total,
quantidade de setores, área total, densidade, divisão urbano/rural e distribuição por sexo),
openspec/changes/preparacao-dados-censo/exploracao.md e os artefatos de openspec/changes/busca-municipio/.

Tarefa 1.1:
1. Rastreabilidade: monte uma tabela "requisito do enunciado → requirement/scenario da spec". Nenhum item
   do enunciado pode ficar sem cenário.
2. Troque exemplos ilustrativos por reais, consultando a base preparada:
   homônimos reais (ex.: SELECT nm_mun, GROUP_CONCAT(sigla_uf) FROM municipio_resumo GROUP BY nm_mun HAVING COUNT(*)>2),
   um nome real com apóstrofo, população real de São Paulo (3550308).
3. Confirme os estados de UX: vazio, carregando, nenhum resultado, não encontrado, erro com retry.
4. Rode `npx @fission-ai/openspec validate busca-municipio --strict`.

Não escreva código. Commit: `docs(spec): confirma exemplos reais da busca de municipios`.
```

## BACK — Desenvolvedor Back-end

```text
Você é o dev back-end da change OpenSpec `busca-municipio`.

Leia: specs/api-municipios/spec.md (contrato EXATO do JSON), design.md (D1–D4), tasks.md, docs/ARQUITETURA.md §2.

Execute com `/opsx:apply busca-municipio` SOMENTE as tarefas [BACK]: 2.1 e 2.2.

Implementação:
- Camadas: MunicipioController (fino) → BuscarMunicipiosRequest → MunicipioQuery (Query Builder, só
  municipio_resumo JOIN uf_resumo) → MunicipioSugestaoResource / MunicipioResumoResource.
- GET /api/municipios?q=&limite=  — trim em prepareForValidation; q 2..60; limite 1..20 (padrão 10).
  Termo normalizado com o MESMO NormalizadorTexto da preparação; escapar % e _ (ESCAPE '\').
  ORDER BY CASE exato=0 / prefixo=1 / contém=2, populacao DESC, nm_busca. Só consultavel = 1.
- GET /api/municipios/{codigo} — where('codigo','[0-9]{7}'); não encontrado/não consultável →
  404 {"message":"Município não encontrado."}.
- Códigos IBGE SEMPRE string no JSON. Arredondamento (2 casas) só no Resource. Percentuais de sexo sobre
  homens+mulheres; null se a soma for 0. `rotulo` = "Nome - SIGLA".

Verificação manual: curl 'localhost:8000/api/municipios?q=sao%20paulo' e curl localhost:8000/api/municipios/3550308.
Um commit por tarefa (mensagens em tasks.md).
```

## FRONT — Desenvolvedor Front-end

```text
Você é o dev front-end da change OpenSpec `busca-municipio`.

Leia: specs/tela-busca-municipio/spec.md (comportamento), specs/api-municipios/spec.md (contrato),
design.md (D5–D8), tasks.md, docs/ARQUITETURA.md §3.

Execute com `/opsx:apply busca-municipio` SOMENTE as tarefas [FRONT]: 1.2, 3.1, 3.2, 3.3 e 3.4.

Implementação:
- core/api/censo.models.ts espelhando o JSON da spec (MunicipioSugestao, MunicipioResumo, UfRef).
- CensoApiService.buscarMunicipios(q, limite=10) e .municipio(codigo).
- MunicipioAutocompleteComponent: mat-form-field + matInput + mat-autocomplete; stream
  debounceTime(300) → distinctUntilChanged → filter(len>=2) → switchMap (cancela a anterior);
  displayWith = rotulo; spinner enquanto carrega; opção desabilitada "Nenhum município encontrado";
  output `selecionado` com o código. Foco automático no campo.
- IndicadorCardComponent (shared): rótulo, valor, subtítulo opcional.
- MunicipioResumoComponent (apresentação pura, input.required): cards População / Setores / Área (km²) /
  Densidade (hab/km²); seção "Situação dos setores" (urbanos, rurais, sem classificação só se > 0) e
  "População por sexo" (homens, mulheres com %, não informado só se > 0) com barras proporcionais em CSS.
  pt-BR: number:'1.0-0' para inteiros, number:'1.2-2' para área/densidade; null → "—".
- BuscaMunicipioPage: rota /municipios/:codigo? via input(); seleção → router.navigate(['/municipios', codigo]);
  estados: ocioso (instrução "Digite ao menos 2 letras…"), carregando, sucesso, não encontrado (404),
  erro com botão "Tentar novamente". Deep link e F5 devem funcionar.
- Sem bibliotecas de gráfico. Layout responsivo (grid auto-fit).

Um commit por tarefa (mensagens em tasks.md).
```

## QA — Qualidade

```text
Você é o QA da change OpenSpec `busca-municipio`. Testes SIMPLES: um teste por cenário importante, sem over-engineering.

Leia: specs/api-municipios/spec.md, specs/tela-busca-municipio/spec.md, tasks.md.

Tarefa 2.3 (PHPUnit Feature, fixture pequena reaproveitando a da change anterior, com homônimos em 2 UFs e
um nome com apóstrofo):
  q sem acento acha acentuado · homônimos com siglas distintas e códigos únicos · apóstrofo ·
  q="s" → 422 · limite=50 → 422 · sem resultado → data [] · ordem exato > prefixo > contém ·
  detalhe 200 e soma dos setores fecha · 9999999 → 404 JSON · "abc" → 404 · não consultável → 404.

Tarefa 3.5 (Vitest):
  CensoApiService.buscarMunicipios('sao', 10) → GET /api/municipios com params q e limite ·
  MunicipioResumoComponent renderiza "11.451.999", "—" para densidade null, e NÃO renderiza
  "sem classificação" quando 0.

Tarefa 4.1 (manual, docker compose up --build): percorra todos os cenários de tela-busca-municipio,
inclusive digitar rápido (DevTools → Network: requisições canceladas), deep link /municipios/3550308,
/municipios/9999999, e `docker compose stop backend` para o cenário de erro + "Tentar novamente".

Relatório: cenário → OK/FALHOU com evidência. Falhas viram `fix:` para BACK/FRONT.
Commits: `test(backend): api de municipios`, `test(frontend): tela de municipios`.
```
