# Spec Delta

## MODIFIED Requirements

### Requirement: Busca de municípios por nome
O sistema SHALL expor `GET /api/v1/municipios` com os parâmetros:
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
- **WHEN** o cliente chama `GET /api/v1/municipios?q=sao paulo`
- **THEN** recebe `200` e o primeiro item é `São Paulo - SP` (código `3550308`)

#### Scenario: Homônimos identificados pela UF
- **WHEN** o cliente busca por um nome existente em mais de uma UF (ex.: `q=bom jesus`, presente em PI, RN, PB, SC e RS)
- **THEN** cada item traz a sigla da sua UF em `uf.sigla` e no `rotulo` (ex.: `Bom Jesus - PI`, `Bom Jesus - RS`)
- **AND** nenhum par de itens possui o mesmo `codigo`

#### Scenario: Apóstrofo e hífen
- **WHEN** o cliente chama `GET /api/v1/municipios?q=olho d agua`
- **THEN** os resultados incluem `Olho d'Água - PB` e `Olho d'Água das Flores - AL`
- **AND** `GET /api/v1/municipios?q=alta floresta d oeste` retorna `Alta Floresta D'Oeste - RO`

#### Scenario: Palavras em qualquer ordem
- **WHEN** o cliente chama `GET /api/v1/municipios?q=paulo sao`
- **THEN** `São Paulo - SP` está entre os resultados

#### Scenario: Termo de duas letras
- **WHEN** o cliente chama `GET /api/v1/municipios?q=sp`
- **THEN** recebe `200` e todo item tem `sp` no nome normalizado (ex.: `Vespasiano - MG`, `Gaspar - SC`)

#### Scenario: Sintaxe de busca digitada pelo usuário
- **WHEN** o cliente chama `GET /api/v1/municipios?q=sao* OR -paulo`
- **THEN** recebe `200` (nunca `500`), tratando o texto como palavras comuns

#### Scenario: Termo curto demais
- **WHEN** o cliente chama `GET /api/v1/municipios?q=s`
- **THEN** recebe `422` em Problem Details com `errors.q` preenchido

#### Scenario: Sem resultados
- **WHEN** o termo não corresponde a nenhum município (ex.: `q=xyzxyz`)
- **THEN** recebe `200` com `{"data": []}`

#### Scenario: Limite
- **WHEN** o cliente chama `GET /api/v1/municipios?q=santa&limite=5`
- **THEN** recebe no máximo 5 itens
- **AND** `limite=50` resulta em `422`

### Requirement: Resumo do município
O sistema SHALL expor `GET /api/v1/municipios/{codigo}` onde `codigo` é o código IBGE de 7 dígitos, retornando `200`:

```json
{
  "data": {
    "codigo": "3550308",
    "nome": "São Paulo",
    "uf": { "codigo": "35", "sigla": "SP", "nome": "São Paulo" },
    "populacao": 11451999,
    "area_km2": 1521.2,
    "densidade_hab_km2": 7528.26,
    "setores": { "total": 27301, "urbanos": 27037, "rurais": 254, "sem_classificacao": 10 },
    "sexo": {
      "homens": 5380188, "mulheres": 6060887, "nao_informado": 10924,
      "percentual_homens": 47.03, "percentual_mulheres": 52.97
    }
  }
}
```
(valores reais de São Paulo no Censo 2022). `area_km2` com 2 casas, `densidade_hab_km2` com 2 casas ou `null`, percentuais com 2 casas ou `null` quando `homens + mulheres = 0`.

#### Scenario: Município existente
- **WHEN** o cliente chama `GET /api/v1/municipios/3550308`
- **THEN** recebe `200` com todos os campos acima preenchidos
- **AND** `setores.urbanos + setores.rurais + setores.sem_classificacao = setores.total`

#### Scenario: Código inexistente
- **WHEN** o cliente chama `GET /api/v1/municipios/9999999`
- **THEN** recebe `404` em Problem Details (`convencoes-api`) com `detail: "Município não encontrado."`

#### Scenario: Código mal formado
- **WHEN** o cliente chama `GET /api/v1/municipios/abc`
- **THEN** recebe `404` com corpo JSON

#### Scenario: Registro não consultável
- **WHEN** o cliente chama o código do registro extra identificado em `dados-censo`
- **THEN** recebe `404`
