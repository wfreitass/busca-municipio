# Spec Delta

## MODIFIED Requirements

### Requirement: Subida com um único comando
O sistema SHALL subir back-end e front-end com o comando `docker compose up` executado na raiz do repositório recém-clonado, numa máquina que possua apenas Docker (com Compose v2): na ausência das imagens, o Compose as constrói automaticamente. Nenhum passo prévio (instalar PHP/Node/Composer, copiar `.env`, rodar migration, seed ou script) MUST ser necessário. Após atualizar o código de um clone já usado, `docker compose up --build` SHALL reconstruir as imagens.

#### Scenario: Clone limpo sobe sem intervenção
- **WHEN** o avaliador executa `git clone <repo> && cd <repo> && docker compose up`, sem nenhuma imagem do projeto em cache
- **THEN** as imagens são construídas e ambos os serviços chegam ao estado `running` (back-end `healthy`) sem erro
- **AND** a aplicação fica acessível em `http://localhost:8080`

#### Scenario: Segunda subida reaproveita o estado
- **WHEN** o avaliador para os containers (`Ctrl+C` ou `docker compose down`) e executa `docker compose up` novamente
- **THEN** a aplicação volta a responder sem rebuild obrigatório e sem erro

#### Scenario: Código atualizado
- **WHEN** o código do repositório muda depois de uma subida (ex.: `git pull`)
- **THEN** `docker compose up --build` reconstrói as imagens e a aplicação sobe com a versão nova
