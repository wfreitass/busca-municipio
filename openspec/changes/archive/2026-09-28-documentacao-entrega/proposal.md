# Proposal

## Why

O enunciado exige um README com instalação/execução e respostas a duas perguntas ("decisões técnicas e por quê" e "o que faria diferente com mais tempo"), além de avaliar a entrega clonando o repositório do zero. Esta change fecha a entrega: documentação e verificação final em clone limpo.

## What Changes

- `README.md` na raiz com: pré-requisitos (só Docker), comando único, URLs, como rodar testes, estrutura do repositório, como o OpenSpec foi conduzido, decisões técnicas e "com mais tempo".
- Verificação final em clone limpo (critério de aceite do avaliador).
- Arquivamento das changes concluídas (`openspec archive`), promovendo os deltas para `openspec/specs/`.

## Capabilities

### New Capabilities
- Nenhuma (apenas documentação e processo; `skip_specs: true`).

### Modified Capabilities
- Nenhuma.

## Impact

- `README.md`, `openspec/specs/*` (gerados pelo archive), `openspec/changes/archive/*`.
- Fora de escopo: documentação de API em OpenAPI/Swagger (citada em "com mais tempo").
