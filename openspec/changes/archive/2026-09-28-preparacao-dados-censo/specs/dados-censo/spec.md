# Spec Delta

## Purpose

Definir as regras de agregação e consistência dos dados do Censo 2022 (população, área, densidade, setores e sexo) que todas as consultas por município e por UF devem respeitar, garantindo que os totais batam com os números oficiais do IBGE.

## ADDED Requirements

### Requirement: Totais nacionais conferem com o IBGE
Os agregados SHALL reproduzir os totais oficiais do Censo 2022: 27 UFs, 5.570 municípios consultáveis, população de 203.080.756 habitantes e área de 8.510.417 km² (tolerância de ±1 km² por arredondamento).

#### Scenario: Soma das UFs
- **WHEN** somam-se população e área de todas as 27 UFs do read model
- **THEN** a população total é exatamente `203080756`
- **AND** a área total está entre `8510416` e `8510418` km²

#### Scenario: Contagem de municípios
- **WHEN** contam-se os municípios consultáveis (os que aparecem na busca e nos rankings)
- **THEN** o total é `5570`

### Requirement: Registro extra de município tratado explicitamente
A tabela `municipio` possui 5.571 linhas. Uma delas não é município: `cd_mun = '.'`, `nm_mun = ''`, UF 43 (RS), 2 setores, população 0 e 13.085,86 km² (as lagoas dos Patos e Mirim, áreas operacionais do IBGE). Esse registro MUST NOT aparecer na busca nem nos rankings e MUST NOT contar em `total_municipios`, mas sua área MUST continuar somada ao RS e ao Brasil — sem ela a área nacional não fecha em 8.510.417 km².

#### Scenario: Registro extra fora da busca
- **WHEN** qualquer busca de município é feita
- **THEN** nenhum resultado tem código `.` ou nome vazio

#### Scenario: Registro extra fora do ranking
- **WHEN** o ranking do RS é consultado
- **THEN** o total de municípios é 497 (oficial) e a área do RS inclui os 13.085,86 km² do registro extra

### Requirement: População do município
A população de um município SHALL ser a soma de `setor.populacao` de todos os seus setores, tratando `NULL` como 0.

#### Scenario: Setor com população nula
- **WHEN** um município possui um setor com `populacao` nula
- **THEN** esse setor contribui com 0 para a população e continua contado na quantidade de setores

### Requirement: Área e densidade
A área de um município ou UF SHALL ser a soma de `setor.area_km2` (nulo = 0). A densidade demográfica SHALL ser `população / área` em hab/km², calculada sobre os totais do agregado. Quando a área for 0, a densidade MUST ser `null` (nunca erro de divisão por zero nem infinito).

#### Scenario: Densidade da UF é ponderada
- **WHEN** a densidade de uma UF é calculada
- **THEN** ela é igual a `população total da UF / área total da UF`
- **AND** não é a média das densidades dos municípios

#### Scenario: Área zero
- **WHEN** um agregado tem área total 0
- **THEN** a densidade é `null`

### Requirement: Setores por situação
A quantidade de setores de um município SHALL ser informada no total e dividida em `urbanos` (`situacao = 'Urbana'`), `rurais` (`situacao = 'Rural'`) e `sem_classificacao` (situação nula ou qualquer outro valor), de modo que `urbanos + rurais + sem_classificacao = total`.

#### Scenario: Soma fecha
- **WHEN** um município possui setores urbanos, rurais e com situação nula
- **THEN** as três contagens somadas são iguais ao total de setores

### Requirement: Distribuição por sexo
A distribuição por sexo SHALL somar `demografia.homens` e `demografia.mulheres` dos setores do município por LEFT JOIN (setores sem linha em `demografia` não são descartados). A diferença entre a população do município e `homens + mulheres` SHALL ser exposta como `nao_informado` (nunca negativa; se negativa, 0). Percentuais de homens e mulheres SHALL ser calculados sobre `homens + mulheres`.

#### Scenario: Setores sem demografia
- **WHEN** um município possui setores sem registro em `demografia`
- **THEN** a população desses setores é contabilizada em `nao_informado`
- **AND** a população total do município não é reduzida

#### Scenario: Percentuais somam 100
- **WHEN** `homens + mulheres > 0`
- **THEN** `percentual_homens + percentual_mulheres = 100` (± 0,1 por arredondamento)

### Requirement: Sigla da UF
Toda UF SHALL ser identificada por sua sigla oficial de duas letras (ex.: `35` → `SP`), mesmo que a tabela `uf` não possua essa coluna.

#### Scenario: Mapeamento completo
- **WHEN** o read model é gerado
- **THEN** as 27 UFs possuem sigla não nula e única

### Requirement: Nome normalizado para busca
Cada município SHALL ter uma forma normalizada do nome em minúsculas, sem acentos, com apóstrofos e hífens substituídos por espaço e espaços repetidos colapsados (ex.: `Santa Bárbara d'Oeste` → `santa barbara d oeste`).

#### Scenario: Normalização
- **WHEN** o nome é `Olho-d'Água das Flores`
- **THEN** a forma normalizada é `olho d agua das flores`

### Requirement: Índice textual de municípios
O read model SHALL conter um índice de texto completo sobre o nome normalizado de cada município consultável, que permita encontrar municípios por trechos de 3 ou mais letras de qualquer palavra do nome, com as palavras em qualquer ordem. O registro não consultável MUST NOT estar no índice.

#### Scenario: Índice cobre os consultáveis
- **WHEN** o read model é gerado
- **THEN** o índice possui exatamente 5.570 entradas

#### Scenario: Trecho de palavra
- **WHEN** o índice é consultado pelo trecho `tocan`
- **THEN** retorna `Bom Jesus do Tocantins`

### Requirement: Ranking de densidade pré-calculado
Cada município consultável SHALL ter sua posição no ranking de densidade da própria UF, com 1 = mais denso. Empates de densidade SHALL ser desempatados por nome normalizado ascendente; municípios com densidade `null` SHALL ficar ao final.

#### Scenario: Posições contínuas
- **WHEN** o ranking de uma UF com N municípios é consultado
- **THEN** as posições são exatamente `1..N`, sem buracos nem repetição

### Requirement: Preparação idempotente e sem alterar o original
A preparação SHALL operar sobre uma cópia do `censo.sqlite`, MUST NOT alterar o arquivo original e SHALL produzir o mesmo resultado se executada mais de uma vez.

#### Scenario: Reexecução
- **WHEN** a preparação é executada duas vezes seguidas sobre a mesma cópia
- **THEN** a segunda execução termina com sucesso e os totais permanecem iguais
