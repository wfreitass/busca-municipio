# infraestrutura Specification

## Purpose
Garantir que a aplicação inteira (API + SPA) suba em uma máquina limpa com um único comando Docker, sem nenhum passo manual, servindo front-end e API na mesma origem.

## Requirements

### Requirement: Subida com um único comando
O sistema SHALL subir back-end e front-end com o comando `docker compose up --build` executado na raiz do repositório recém-clonado, numa máquina que possua apenas Docker (com Compose v2). Nenhum passo prévio (instalar PHP/Node/Composer, copiar `.env`, rodar migration, seed ou script) MUST ser necessário.

#### Scenario: Clone limpo sobe sem intervenção
- **WHEN** o avaliador executa `git clone <repo> && cd <repo> && docker compose up --build`
- **THEN** ambos os serviços chegam ao estado `running` (back-end `healthy`) sem erro
- **AND** a aplicação fica acessível em `http://localhost:8080`

#### Scenario: Segunda subida reaproveita o estado
- **WHEN** o avaliador para os containers (`Ctrl+C` ou `docker compose down`) e executa `docker compose up` novamente
- **THEN** a aplicação volta a responder sem rebuild obrigatório e sem erro

### Requirement: Arquivo de dados versionado permanece intacto
O sistema MUST NOT modificar o arquivo `censo.sqlite` da raiz do repositório durante build ou execução.

#### Scenario: Git limpo após subir
- **WHEN** a aplicação é construída e usada via Docker
- **THEN** `git status` na raiz não lista `censo.sqlite` como modificado

### Requirement: Front-end e API na mesma origem
O sistema SHALL servir a SPA em `http://localhost:8080/` e encaminhar toda requisição com prefixo `/api/` para o back-end, de modo que o navegador nunca faça requisição cross-origin.

#### Scenario: Proxy da API
- **WHEN** o navegador requisita `GET http://localhost:8080/api/health`
- **THEN** a resposta vem do back-end com status `200`

#### Scenario: Deep link da SPA
- **WHEN** o navegador abre diretamente `http://localhost:8080/estados/SP` (ou dá F5 nessa URL)
- **THEN** o servidor responde `200` com o `index.html` da SPA e o Angular renderiza a rota correspondente

### Requirement: Endpoint de saúde
O back-end SHALL expor `GET /api/health` retornando `200` com `Content-Type: application/json` e corpo `{"status":"ok"}` quando a aplicação estiver pronta para atender.

#### Scenario: Saúde ok
- **WHEN** um cliente chama `GET /api/health`
- **THEN** recebe `200` e `{"status":"ok"}`

#### Scenario: Front-end aguarda o back-end
- **WHEN** o Compose inicia os serviços
- **THEN** o serviço `frontend` só inicia depois que o _healthcheck_ do `backend` reportar `healthy`

### Requirement: API sem estado e sem segredo versionado
O back-end MUST NOT depender de sessão, cache ou fila persistidos em banco, e o repositório MUST NOT conter arquivo `.env` com `APP_KEY` real.

#### Scenario: Nenhum segredo no repositório
- **WHEN** alguém inspeciona os arquivos versionados
- **THEN** não existe `backend/.env` versionado, apenas `backend/.env.example` sem `APP_KEY` preenchida

#### Scenario: Erros retornam JSON
- **WHEN** um cliente chama uma rota inexistente sob `/api/`
- **THEN** recebe `404` em JSON no formato Problem Details (não HTML) — ver `convencoes-api`
