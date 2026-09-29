# Prompts — change `documentacao-entrega`

Artefatos: `openspec/changes/documentacao-entrega/{proposal,tasks}.md` (`skip_specs: true`).
Objetivo da change: README completo, verificação em clone limpo e arquivamento das changes no OpenSpec.

---

## BA — Analista de Negócio

```text
Você é o BA da change OpenSpec `documentacao-entrega`.

Leia: o enunciado (seções "Sobre o Docker", "Sobre o uso de IA", "Sobre os commits", "No README, responda também"),
docs/ARQUITETURA.md, todos os exploracao.md/design.md das changes, e `git log --oneline`.

Execute as tarefas [BA]: 1.1, 1.4, 1.5 e 2.2.
- README.md na raiz, em pt-BR, direto ao ponto. Seções: Visão geral · Pré-requisitos (só Docker + Compose v2) ·
  Como subir (UM comando) · Como usar (as duas telas, com URLs) · Testes · Estrutura do repositório ·
  Decisões técnicas e por quê · Como conduzi o SDD com OpenSpec · O que eu faria diferente com mais tempo.
- "Decisões técnicas": no máximo ~10 bullets, cada um com o PORQUÊ (read model no build; cópia do sqlite;
  densidade ponderada; registro extra de município e o que a exploração revelou; siglas estáticas;
  busca normalizada; paginação no servidor; proxy nginx sem CORS; Query Builder em vez de Eloquent).
- "Como conduzi o SDD": ordem das 5 changes, o papel da exploração antes do código e onde a spec mudou
  por causa do dado (cite commits `docs(spec): ...`), prompts por papel em docs/prompts/.
- "Com mais tempo": OpenAPI, E2E Playwright, ETag/cache HTTP, Meilisearch como novo adaptador de MunicipioSearch (tolerância a erro de digitação),
  filtro por nome no ranking, gráficos, CI, auditoria de acessibilidade.
- 2.2: `npx @fission-ai/openspec archive <change> -y` para cada change concluída, na ordem; depois
  `npx @fission-ai/openspec validate --all --strict`.

Commits: `docs: ...` por seção e `docs(spec): arquiva changes concluidas`.
```

## BACK — Desenvolvedor Back-end

```text
Você é o dev back-end na change `documentacao-entrega`. Execute a tarefa [BACK] 1.2:
No README, seção "API": tabela com as 6 rotas (GET /api/health, /api/municipios, /api/municipios/{codigo},
/api/ufs, /api/ufs/{sigla}, /api/ufs/{sigla}/municipios), parâmetros, códigos de resposta e um exemplo curl
por rota (via http://localhost:8080/api/... — mesma origem). Seção "Testes do back-end":
`docker compose run --rm backend php artisan test` (e `--group=dados` para os totais do IBGE).
Execute CADA comando que documentar e cole a saída resumida no PR/commit. Commit: `docs: rotas da api e testes do backend`.
```

## FRONT — Desenvolvedor Front-end

```text
Você é o dev front-end na change `documentacao-entrega`. Execute a tarefa [FRONT] 1.3:
No README, seção "Desenvolvimento local (opcional)": back (`cd backend && composer install && cp .env.example .env
&& php artisan key:generate && cp ../censo.sqlite database/ && php artisan censo:preparar && php artisan serve`)
e front (`cd frontend && npm ci && npm start` com proxy para :8000). Seção "Testes do front-end": `cd frontend && npm test`
(ou `docker run --rm -v "$PWD/frontend":/app -w /app node:22-alpine sh -c "npm ci && npm test"` para quem só tem Docker).
Execute cada comando antes de documentar. Commit: `docs: execucao local e testes do frontend`.
```

## QA — Qualidade

```text
Você é o QA na change `documentacao-entrega`. Execute a tarefa [QA] 2.1 — o ensaio do avaliador:

1. Em um diretório temporário: git clone <repo> avaliacao && cd avaliacao
2. Siga o README LITERALMENTE, sem nenhum conhecimento prévio. Qualquer passo que você precisou "saber" é bug de doc.
3. docker compose up --build → http://localhost:8080
4. Roteiro de aceite:
   - Tela 1: "sao paulo" → São Paulo - SP com indicadores; "bom jesus" → várias UFs distinguíveis;
     /municipios/3550308 direto; F5 mantém a tela.
   - Tela 2: SP → 645 municípios, página 2 com posições 51–100; RR → 15 numa página; /estados/rr direto.
   - Totais: somar a população das 27 UFs via API = 203.080.756.
   - git status limpo; git log com commits pequenos e todos com prefixo Conventional Commits
     (`git log --format=%s | grep -vE '^(feat|fix|docs|refactor|test|chore|build|ci|perf|style)(\(.+\))?!?: '`
     deve retornar vazio).
   - As duas suítes de teste passam.
5. Relatório final: item → OK/FALHOU com evidência. Falhas viram commits `fix:`.
```
