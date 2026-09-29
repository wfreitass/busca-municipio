# Spec Delta

## Purpose

Expor via HTTP a lista de UFs, os totais agregados de cada UF e o ranking paginado dos seus municípios por densidade demográfica, de forma eficiente mesmo para UFs com centenas de municípios.

## ADDED Requirements

### Requirement: Lista de UFs
O sistema SHALL expor `GET /api/v1/ufs` retornando `200` com as 27 UFs ordenadas por nome:
```json
{ "data": [ { "codigo": "12", "sigla": "AC", "nome": "Acre" } ] }
```

#### Scenario: 27 UFs
- **WHEN** o cliente chama `GET /api/v1/ufs`
- **THEN** recebe 27 itens com siglas únicas, ordenados por `nome`

### Requirement: Resumo da UF
O sistema SHALL expor `GET /api/v1/ufs/{sigla}` (sigla de 2 letras, aceita maiúsculas ou minúsculas) retornando `200`:
```json
{
  "data": {
    "codigo": "35", "sigla": "SP", "nome": "São Paulo",
    "populacao": 44411238, "area_km2": 248219.49, "densidade_hab_km2": 178.92,
    "total_municipios": 645
  }
}
```
(valores reais de SP no Censo 2022). A densidade MUST ser `populacao / area_km2` da UF (não a média dos municípios), com 2 casas.

#### Scenario: UF existente
- **WHEN** o cliente chama `GET /api/v1/ufs/SP`
- **THEN** recebe `200` com `total_municipios = 645`

#### Scenario: Sigla minúscula
- **WHEN** o cliente chama `GET /api/v1/ufs/rr`
- **THEN** recebe `200` com `sigla = "RR"` e `total_municipios = 15`

#### Scenario: UF inexistente
- **WHEN** o cliente chama `GET /api/v1/ufs/XX`
- **THEN** recebe `404` em Problem Details (`convencoes-api`) com `detail: "UF não encontrada."`

### Requirement: Ranking paginado de municípios por densidade
O sistema SHALL expor `GET /api/v1/ufs/{sigla}/municipios` com parâmetros opcionais `pagina` (inteiro ≥ 1, padrão 1) e `por_pagina` (inteiro de 1 a 100, padrão 50), retornando os municípios consultáveis da UF ordenados pela posição no ranking de densidade (1 = mais denso):
```json
{
  "data": [
    { "posicao": 1, "codigo": "3552809", "nome": "Taboão da Serra",
      "populacao": 273542, "area_km2": 20.39, "densidade_hab_km2": 13416.96 }
  ],
  "meta": { "pagina": 1, "por_pagina": 50, "total": 645, "total_paginas": 13 }
}
```
(primeira página real de SP; Diadema é o 2º e Iporanga o 645º). `posicao` MUST ser a posição global na UF (não o índice na página). Página além da última MUST retornar `data: []` com `meta` preenchido (não 404).

#### Scenario: Ordem decrescente de densidade
- **WHEN** o cliente chama `GET /api/v1/ufs/SP/municipios`
- **THEN** recebe 50 itens com `posicao` de 1 a 50
- **AND** para todo par consecutivo com densidades não nulas, `densidade[i] >= densidade[i+1]`

#### Scenario: Posição global em páginas seguintes
- **WHEN** o cliente chama `GET /api/v1/ufs/SP/municipios?pagina=2&por_pagina=50`
- **THEN** o primeiro item tem `posicao = 51`

#### Scenario: Última página parcial
- **WHEN** o cliente chama `GET /api/v1/ufs/SP/municipios?pagina=13&por_pagina=50`
- **THEN** recebe 45 itens e `meta.total_paginas = 13`

#### Scenario: UF pequena cabe numa página
- **WHEN** o cliente chama `GET /api/v1/ufs/RR/municipios`
- **THEN** recebe 15 itens e `meta.total_paginas = 1`

#### Scenario: Página além do fim
- **WHEN** o cliente chama `GET /api/v1/ufs/RR/municipios?pagina=5`
- **THEN** recebe `200` com `data: []` e `meta.total = 15`

#### Scenario: Parâmetros inválidos
- **WHEN** o cliente chama com `por_pagina=500` ou `pagina=0` ou `pagina=abc`
- **THEN** recebe `422` em Problem Details com o campo correspondente em `errors`

#### Scenario: UF inexistente no ranking
- **WHEN** o cliente chama `GET /api/v1/ufs/XX/municipios`
- **THEN** recebe `404` em Problem Details

#### Scenario: Densidade nula ao final
Não ocorre no dado real (nenhum município tem área 0), mas a regra protege contra dados futuros e é coberta por fixture.

- **WHEN** uma UF possui município com `densidade_hab_km2 = null`
- **THEN** ele aparece nas últimas posições do ranking
