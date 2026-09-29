# Tasks

## 1. README

- [x] 1.1 [BA] Esqueleto do `README.md`: visão geral, pré-requisitos (apenas Docker + Compose v2), `docker compose up --build`, URL `http://localhost:8080`, troca de porta via `FRONT_PORT`; verificar que os comandos citados funcionam como escritos — commit `docs: readme com instalacao e execucao`
- [x] 1.2 [BACK] Seção "API" com as 6 rotas (método, parâmetros, exemplo `curl`) e "Rodando os testes do back-end" (`docker compose run --rm backend php artisan test`) — commit `docs: rotas da api e testes do backend`
- [x] 1.3 [FRONT] Seção "Rodando o front-end localmente" (`npm ci && npm start` com proxy) e "Testes do front-end" (`npm test`) — commit `docs: execucao local e testes do frontend`
- [x] 1.4 [BA] Seção "Decisões técnicas" (resumo de `docs/ARQUITETURA.md`: read model no build, cópia do sqlite, densidade ponderada, registro extra de município, siglas, busca normalizada, paginação no servidor, proxy nginx) e "Como conduzi o SDD com OpenSpec" (ordem das changes, onde a exploração mudou a spec) — commit `docs: decisoes tecnicas e uso do openspec`
- [x] 1.5 [BA] Seção "O que eu faria diferente com mais tempo" (ex.: OpenAPI, E2E com Playwright, cache HTTP/ETag, busca FTS5 com tolerância a erro de digitação, filtro por nome no ranking, gráficos, CI rodando testes e build, acessibilidade auditada) — commit `docs: proximos passos`

## 2. Fechamento

- [x] 2.1 [QA] Clone limpo em pasta temporária → `docker compose up --build` → roteiro de aceite: busca `sao paulo`, homônimo `bom jesus`, deep link `/municipios/3550308`, `/estados/SP` página 2, `/estados/RR`, `git status` limpo; rodar as duas suítes de testes — commit `fix:` para qualquer ajuste encontrado
- [ ] 2.2 [BA] `openspec archive` de cada change concluída na ordem (infraestrutura-base → qualidade-e-ci → preparacao-dados-censo → busca-municipio → ranking-estado → documentacao-entrega); verificar `openspec validate --all` e `openspec list --specs` — commit `docs(spec): arquiva changes concluidas`
