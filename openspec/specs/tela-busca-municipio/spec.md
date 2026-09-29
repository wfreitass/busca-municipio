# tela-busca-municipio Specification

## Purpose
Permitir ao usuário encontrar um município pelo nome, com autocomplete que distingue homônimos pela UF, e visualizar os indicadores agregados do Censo 2022 desse município.

## Requirements

### Requirement: Tela de busca de municípios
O sistema SHALL oferecer uma tela própria, acessível pela rota `/municipios` e pelo item "Municípios" da navegação, e a rota raiz `/` SHALL redirecionar para ela.

#### Scenario: Acesso inicial
- **WHEN** o usuário abre `http://localhost:8080/`
- **THEN** vê a tela de busca de municípios com o campo de busca focado e nenhum indicador exibido
- **AND** uma instrução do tipo "Digite ao menos 2 letras do nome do município"

### Requirement: Autocomplete de municípios
O campo de busca SHALL sugerir municípios a partir de 2 caracteres digitados, aguardando 300 ms sem digitação antes de consultar a API, cancelando consultas anteriores ainda pendentes e exibindo no máximo 10 sugestões no formato `Nome - UF`. As sugestões SHALL ser navegáveis por teclado (setas + Enter) e mouse.

#### Scenario: Sugestões com UF
- **WHEN** o usuário digita `bom jesus`
- **THEN** a lista mostra opções como `Bom Jesus - PI`, `Bom Jesus - RS`, `Bom Jesus - SC`, cada uma distinguível pela UF

#### Scenario: Menos de 2 caracteres
- **WHEN** o usuário digita apenas `s`
- **THEN** nenhuma requisição é feita e nenhuma sugestão é exibida

#### Scenario: Digitação rápida
- **WHEN** o usuário digita `curitiba` rapidamente
- **THEN** no máximo uma requisição de busca é concluída e usada (as anteriores são descartadas/canceladas)

#### Scenario: Nenhum resultado
- **WHEN** a API retorna lista vazia
- **THEN** o painel de sugestões mostra "Nenhum município encontrado"

#### Scenario: Carregando
- **WHEN** uma busca está em andamento
- **THEN** um indicador de carregamento é exibido no campo

### Requirement: Exibição do resumo do município
Ao selecionar uma sugestão, a tela SHALL navegar para `/municipios/{codigo}` e exibir abaixo do campo: nome e UF, população total, quantidade de setores, área total (km²), densidade demográfica (hab/km²), divisão de setores urbanos/rurais (e "sem classificação" quando > 0) e distribuição da população por sexo (homens e mulheres com valor absoluto e percentual, e "não informado" quando > 0). Números SHALL ser formatados em pt-BR (separador de milhar `.`, decimal `,`), área e densidade com 2 casas decimais.

#### Scenario: Selecionar município
- **WHEN** o usuário seleciona `São Paulo - SP`
- **THEN** a URL passa a ser `/municipios/3550308`
- **AND** o campo passa a exibir `São Paulo - SP`
- **AND** os indicadores são exibidos formatados (ex.: população `11.451.999`)

#### Scenario: Deep link
- **WHEN** o usuário abre diretamente `/municipios/3550308` (ou recarrega a página)
- **THEN** a tela carrega e exibe o resumo desse município sem precisar buscar de novo

#### Scenario: Código inexistente na URL
- **WHEN** o usuário abre `/municipios/9999999`
- **THEN** a tela exibe "Município não encontrado" e mantém o campo de busca utilizável

#### Scenario: Densidade indisponível
- **WHEN** a API retorna `densidade_hab_km2: null`
- **THEN** a tela exibe `—` no lugar da densidade

### Requirement: Falha da API
Quando a API estiver indisponível ou responder erro 5xx, a tela SHALL exibir mensagem de erro amigável com ação "Tentar novamente", sem quebrar a navegação.

#### Scenario: Back-end fora do ar
- **WHEN** a requisição de resumo falha com erro de rede ou 5xx
- **THEN** o usuário vê "Não foi possível carregar os dados. Tentar novamente"
- **AND** clicar em "Tentar novamente" refaz a requisição
