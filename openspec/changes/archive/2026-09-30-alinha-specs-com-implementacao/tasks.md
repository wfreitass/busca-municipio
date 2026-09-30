# Tasks

## 1. Specs e documentação

- [x] 1.1 [BA] Deltas de `infraestrutura` e `api-municipios` com os cenários corrigidos a partir da auditoria; verificar `openspec validate --strict` — commit `docs(spec): alinha specs com o comportamento real`
- [x] 1.2 [BA] README e `docs/ARQUITETURA.md` com `docker compose up` como comando de subida — commit `docs: subida com docker compose up`

## 2. Verificação

- [x] 2.1 [QA] Conferir cada cenário alterado contra a aplicação rodando (clone limpo com `docker compose up` sem imagens em cache; `olho d agua`, `alta floresta d oeste`, `sp` e `GET /api/v1/municipios/3550308` na API)
