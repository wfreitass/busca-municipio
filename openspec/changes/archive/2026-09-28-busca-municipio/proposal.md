# Proposal

## Why

É a primeira das duas telas exigidas: o usuário precisa encontrar um município pelo nome — com homônimos em UFs diferentes e nomes acentuados — e ver seus números agregados do Censo 2022. Entregar como fatia vertical (API + tela + testes) garante uma funcionalidade completa e demonstrável antes de iniciar a segunda.

## What Changes

- `GET /api/v1/municipios?q=&limite=` — autocomplete por nome, insensível a acento/caixa, com UF no rótulo.
- `GET /api/v1/municipios/{codigo}` — resumo do município: população, setores (total/urbanos/rurais/sem classificação), área, densidade e distribuição por sexo.
- Tela **Busca de municípios** (`/municipios` e `/municipios/:codigo`) com campo de autocomplete e painel de indicadores abaixo.

## Capabilities

### New Capabilities
- `api-municipios`: contrato HTTP de busca e detalhe de municípios.
- `tela-busca-municipio`: comportamento da tela de busca de municípios.

### Modified Capabilities
- Nenhuma.

## Impact

- Back: `MunicipioController`, `BuscarMunicipiosRequest`, `MunicipioQuery`, resources, rotas em `routes/api.php`.
- Front: `features/municipio/*`, `core/api/*`, `shared/indicador-card`.
- Depende de `preparacao-dados-censo` (lê `municipio_resumo`).
- Fora de escopo: gráficos, mapa, comparação entre municípios, busca por código IBGE.
