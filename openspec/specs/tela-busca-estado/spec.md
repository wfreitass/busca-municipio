# tela-busca-estado Specification

## Purpose
Permitir ao usuário escolher uma unidade federativa e visualizar os totais do estado e o ranking dos seus municípios por densidade demográfica, navegando com fluidez tanto em UFs pequenas quanto nas muito grandes.

## Requirements

### Requirement: Tela de busca por estado
O sistema SHALL oferecer uma tela própria, distinta da tela de municípios, acessível pela rota `/estados` e pelo item "Estados" da navegação, com um seletor contendo as 27 UFs no formato `Nome (SIGLA)` ordenadas por nome.

#### Scenario: Acesso sem UF
- **WHEN** o usuário abre `/estados`
- **THEN** vê o seletor de UF e a instrução "Selecione um estado"
- **AND** nenhum ranking é exibido

#### Scenario: Seletor com 27 UFs
- **WHEN** o usuário abre o seletor
- **THEN** vê 27 opções, como `São Paulo (SP)` e `Roraima (RR)`

### Requirement: Totais do estado
Ao escolher uma UF, a tela SHALL navegar para `/estados/{SIGLA}` e exibir cards com população total, área total (km²), densidade demográfica (hab/km²) e número de municípios da UF, formatados em pt-BR.

#### Scenario: Selecionar SP
- **WHEN** o usuário seleciona `São Paulo (SP)`
- **THEN** a URL passa a `/estados/SP`
- **AND** os cards exibem população, área, densidade e `645` municípios

#### Scenario: Deep link
- **WHEN** o usuário abre diretamente `/estados/rr`
- **THEN** a tela seleciona `Roraima (RR)` no seletor e carrega totais e ranking

#### Scenario: Sigla inválida na URL
- **WHEN** o usuário abre `/estados/XX`
- **THEN** a tela exibe "Estado não encontrado" e mantém o seletor utilizável

### Requirement: Ranking paginado por densidade
Abaixo dos totais, a tela SHALL exibir uma tabela com colunas Posição, Município, População, Área (km²) e Densidade (hab/km²), ordenada da maior para a menor densidade, paginada com 50 linhas por página (opções 25/50/100), mostrando o total de municípios e a página atual. A página atual SHALL ser refletida na URL (`?pagina=N`) e a troca de página MUST buscar apenas a página solicitada na API.

#### Scenario: UF grande
- **WHEN** o usuário está em `/estados/SP`
- **THEN** vê as posições 1 a 50 e o paginador indica `1 – 50 de 645`

#### Scenario: Trocar de página
- **WHEN** o usuário avança para a página 2
- **THEN** a URL passa a `/estados/SP?pagina=2`
- **AND** a tabela mostra as posições 51 a 100
- **AND** os cards de totais não são recarregados

#### Scenario: UF pequena
- **WHEN** o usuário está em `/estados/RR`
- **THEN** vê os 15 municípios numa única página

#### Scenario: Trocar de UF volta à primeira página
- **WHEN** o usuário está em `/estados/SP?pagina=3` e seleciona `Acre (AC)`
- **THEN** a URL passa a `/estados/AC` e a tabela mostra a página 1

#### Scenario: Carregando página
- **WHEN** uma página do ranking está sendo carregada
- **THEN** a tabela exibe indicador de carregamento sem desmontar o layout

### Requirement: Falha da API na tela de estados
Quando a API falhar (rede ou 5xx), a tela SHALL exibir mensagem amigável com ação "Tentar novamente".

#### Scenario: Erro ao carregar ranking
- **WHEN** a requisição do ranking falha
- **THEN** o usuário vê "Não foi possível carregar os dados. Tentar novamente"
