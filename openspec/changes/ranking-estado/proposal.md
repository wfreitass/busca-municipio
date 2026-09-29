# Proposal

## Why

Segunda tela exigida: escolher uma UF e ver seus municípios ranqueados por densidade demográfica (do mais denso ao menos denso) junto com os totais do estado. As UFs variam de 15 (RR) a 645 municípios (SP) — a API e a tela precisam se comportar bem nos dois extremos, sem despejar centenas de linhas de uma vez.

## What Changes

- `GET /api/v1/ufs` — lista das 27 UFs para o seletor.
- `GET /api/v1/ufs/{sigla}` — resumo da UF: população, área, densidade, total de municípios.
- `GET /api/v1/ufs/{sigla}/municipios?pagina=&por_pagina=` — ranking por densidade **paginado no servidor**, com posição global no ranking.
- Tela **Busca por estado** (`/estados` e `/estados/:sigla?pagina=N`) com seletor de UF, cards de totais e tabela paginada.

## Capabilities

### New Capabilities
- `api-estados`: contrato HTTP de listagem de UFs, resumo de UF e ranking paginado de municípios por densidade.
- `tela-busca-estado`: comportamento da tela de busca por estado.

### Modified Capabilities
- Nenhuma.

## Impact

- Back: `UfController`, `RankingUfRequest`, `UfQuery`, resources, rotas.
- Front: `features/estado/*`, reuso de `shared/indicador-card` e `core/api`.
- Depende de `preparacao-dados-censo` (`uf_resumo`, `posicao_densidade_uf`).
- Fora de escopo: ordenação por outras colunas, filtro por nome dentro do ranking, exportação CSV, ranking nacional.
