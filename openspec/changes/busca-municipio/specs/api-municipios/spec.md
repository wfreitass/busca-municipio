# Spec Delta

## Purpose

Expor via HTTP a busca de municípios por nome (para autocomplete) e o resumo agregado do Censo 2022 de um município, com contrato JSON estável consumido pelo front-end.

## ADDED Requirements

### Requirement: Busca de municípios por nome
O sistema SHALL expor `GET /api/municipios` com os parâmetros:
- `q` (obrigatório): texto com 2 a 60 caracteres após `trim`;
- `limite` (opcional): inteiro de 1 a 20, padrão 10.

A busca MUST ser insensível a caixa, acentos, apóstrofos e hífens, e MUST considerar apenas municípios consultáveis. A resposta `200` SHALL ter o formato:

```json
{
  "data": [
    {
      "codigo": "3550308",
      "nome": "São Paulo",
      "uf": { "codigo": "35", "sigla": "SP", "nome": "São Paulo" },
      "rotulo": "São Paulo - SP"
    }
  ]
}
```

`codigo` e `uf.codigo` MUST ser strings.

#### Scenario: Busca sem acento encontra nome acentuado
- **WHEN** o cliente chama `GET /api/municipios?q=sao paulo`
- **THEN** recebe `200` e o primeiro item é `São Paulo - SP` (código `3550308`)

#### Scenario: Homônimos identificados pela UF
- **WHEN** o cliente busca por um nome existente em mais de uma UF (ex.: `q=bom jesus`, presente em PI, RN, PB, SC e RS)
- **THEN** cada item traz a sigla da sua UF em `uf.sigla` e no `rotulo` (ex.: `Bom Jesus - PI`, `Bom Jesus - RS`)
- **AND** nenhum par de itens possui o mesmo `codigo`

#### Scenario: Apóstrofo e hífen
- **WHEN** o cliente chama `GET /api/municipios?q=olho d agua`
- **THEN** os resultados incluem municípios cujo nome é `Alta Floresta D'Oeste - RO`

#### Scenario: Palavras em qualquer ordem
- **WHEN** o cliente chama `GET /api/municipios?q=paulo sao`
- **THEN** `São Paulo - SP` está entre os resultados

#### Scenario: Termo de duas letras
- **WHEN** o cliente chama `GET /api/municipios?q=sp`
- **THEN** recebe `200` com municípios cujo nome normalizado contém `sp` (ex.: `Espigão D'Oeste - RO`)

#### Scenario: Sintaxe de busca digitada pelo usuário
- **WHEN** o cliente chama `GET /api/municipios?q=sao* OR -paulo`
- **THEN** recebe `200` (nunca `500`), tratando o texto como palavras comuns

#### Scenario: Termo curto demais
- **WHEN** o cliente chama `GET /api/municipios?q=s`
- **THEN** recebe `422` com o erro de validação no campo `q`

#### Scenario: Sem resultados
- **WHEN** o termo não corresponde a nenhum município (ex.: `q=xyzxyz`)
- **THEN** recebe `200` com `{"data": []}`

#### Scenario: Limite
- **WHEN** o cliente chama `GET /api/municipios?q=santa&limite=5`
- **THEN** recebe no máximo 5 itens
- **AND** `limite=50` resulta em `422`

### Requirement: Ordenação da busca por relevância
Os resultados SHALL ser ordenados por: (1) nome normalizado igual ao termo, (2) nome normalizado que começa com o termo, (3) nome que contém o termo; e, dentro de cada grupo, por população decrescente e depois nome ascendente.

#### Scenario: Prefixo antes de substring
- **WHEN** o cliente busca `q=santos`
- **THEN** `Santos - SP` é o primeiro item (nome igual ao termo)
- **AND** `Santos Dumont - MG` (prefixo) vem antes dos que apenas contêm `santos` (ex.: `Brejo dos Santos - PB`, `Urbano Santos - MA`)

#### Scenario: Maior população desempata
- **WHEN** vários municípios começam com o termo `q=sao`
- **THEN** `São Paulo - SP` vem antes de municípios menores que também começam com `São`

### Requirement: Resumo do município
O sistema SHALL expor `GET /api/municipios/{codigo}` onde `codigo` é o código IBGE de 7 dígitos, retornando `200`:

```json
{
  "data": {
    "codigo": "3550308",
    "nome": "São Paulo",
    "uf": { "codigo": "35", "sigla": "SP", "nome": "São Paulo" },
    "populacao": 11451999,
    "area_km2": 1521.2,
    "densidade_hab_km2": 7528.26,
    "setores": { "total": 27000, "urbanos": 26500, "rurais": 400, "sem_classificacao": 100 },
    "sexo": {
      "homens": 5400000, "mulheres": 6000000, "nao_informado": 51999,
      "percentual_homens": 47.37, "percentual_mulheres": 52.63
    }
  }
}
```
(valores ilustrativos; os reais vêm do read model). `area_km2` com 2 casas, `densidade_hab_km2` com 2 casas ou `null`, percentuais com 2 casas ou `null` quando `homens + mulheres = 0`.

#### Scenario: Município existente
- **WHEN** o cliente chama `GET /api/municipios/3550308`
- **THEN** recebe `200` com todos os campos acima preenchidos
- **AND** `setores.urbanos + setores.rurais + setores.sem_classificacao = setores.total`

#### Scenario: Código inexistente
- **WHEN** o cliente chama `GET /api/municipios/9999999`
- **THEN** recebe `404` com corpo JSON `{"message": "Município não encontrado."}`

#### Scenario: Código mal formado
- **WHEN** o cliente chama `GET /api/municipios/abc`
- **THEN** recebe `404` com corpo JSON

#### Scenario: Registro não consultável
- **WHEN** o cliente chama o código do registro extra identificado em `dados-censo`
- **THEN** recebe `404`
