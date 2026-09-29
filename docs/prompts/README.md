# Prompts por papel

Cada change do OpenSpec tem **um arquivo de prompt** com quatro blocos independentes — um por papel. Cada bloco pode ser colado numa sessão nova do Claude Code (ou num subagente) e funciona sozinho, porque aponta para os artefatos da change em vez de repetir o conteúdo.

| # | Change | Prompt | Papéis ativos |
| --- | --- | --- | --- |
| 1 | `infraestrutura-base` | [01-infraestrutura-base.md](01-infraestrutura-base.md) | BA · BACK · FRONT · QA |
| 2 | `preparacao-dados-censo` | [02-preparacao-dados-censo.md](02-preparacao-dados-censo.md) | BA · BACK · QA (FRONT só revisa contrato) |
| 3 | `busca-municipio` | [03-busca-municipio.md](03-busca-municipio.md) | BA · BACK · FRONT · QA |
| 4 | `ranking-estado` | [04-ranking-estado.md](04-ranking-estado.md) | BA · BACK · FRONT · QA |
| 5 | `documentacao-entrega` | [05-documentacao-entrega.md](05-documentacao-entrega.md) | BA · BACK · FRONT · QA |

## Ordem dentro de cada change

```
BA (valida a spec contra o dado/enunciado)  →  BACK  →  FRONT  →  QA (testa cenários + aceite)
                                              └── em paralelo quando o contrato já está fechado ──┘
```

- **BA** só mexe em `openspec/` (via `/opsx:update` ou edição direta + `openspec validate`). Nunca escreve código.
- **BACK** só implementa tarefas marcadas `[BACK]` em `tasks.md`, dentro de `backend/` e arquivos de infra.
- **FRONT** só implementa tarefas `[FRONT]`, dentro de `frontend/`.
- **QA** escreve os testes das tarefas `[QA]` (**testes simples**: poucos, objetivos, um por cenário relevante) e executa a verificação integrada. Encontrou bug? Reporta com o cenário da spec que falhou; quem corrige é BACK/FRONT com commit `fix:`.

## Regras comuns a todos os papéis

1. Leia antes de agir: `docs/ARQUITETURA.md`, `openspec/config.yaml` e os artefatos da change (`proposal.md`, `design.md`, `specs/**/spec.md`, `tasks.md`).
2. Use o fluxo do OpenSpec: `/opsx:apply <change>` para implementar; marque `- [x]` em `tasks.md` ao concluir cada tarefa.
3. **Um commit por tarefa**, Conventional Commits, com a mensagem sugerida na própria tarefa. Nunca squash.
4. Divergência entre spec e realidade? Pare, avise o BA, atualize a spec **antes** do código.
5. Não expanda escopo: o que não está na spec vai para "O que faria com mais tempo" no README.
