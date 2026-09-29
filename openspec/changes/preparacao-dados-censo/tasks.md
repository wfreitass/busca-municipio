# Tasks

## 1. Exploração do dado

- [x] 1.1 [BA] Executar as consultas H1–H10 do `design.md` com `sqlite3 censo.sqlite` (ou `docker run --rm -v $PWD:/d keinos/sqlite3`) e registrar resultados e decisões em `openspec/changes/preparacao-dados-censo/exploracao.md`; verificar que toda hipótese tem resultado e decisão — commit `docs(spec): exploracao do censo.sqlite`
- [x] 1.2 [BA] (sem divergência: a spec já refletia o dado real) Se alguma decisão da exploração contrariar a spec `dados-censo` (ex.: H1 diferente do esperado), atualizar a spec e o design antes de codar (`/opsx:update`); verificar `openspec validate preparacao-dados-censo` — commit `docs(spec): ajusta regras apos exploracao`

## 2. Utilitários de domínio

- [ ] 2.1 [BACK] `App\Censo\SiglasUf` (mapa estático 27 UFs, `sigla(cd)` e `codigo(sigla)` case-insensitive) — commit `feat(backend): mapa de siglas das ufs`
- [ ] 2.2 [BACK] `App\Censo\NormalizadorTexto::normalizar()` conforme design D3.4 — commit `feat(backend): normalizador de nomes para busca`
- [ ] 2.3 [QA] Testes unit: 27 siglas únicas; `SP`/`sp` → `35`; `Olho-d'Água das Flores` → `olho d agua das flores`; `  São   Paulo ` → `sao paulo`; verificar `php artisan test --testsuite=Unit` — commit `test(backend): siglas e normalizador`

## 3. Preparação do read model

- [ ] 3.1 [BACK] `App\Censo\PreparadorBaseCenso` com os passos 1–7 do design D3 (incluindo 6b, índice FTS5 trigram `municipio_busca`) e comando `censo:preparar {--database=}`; verificar localmente com cópia do sqlite (tempo < 30 s) — commit `feat(backend): comando censo:preparar gera read model`
- [ ] 3.2 [BACK] Passo 8 (validação de totais com exit code ≠ 0) e saída resumida no console — commit `feat(backend): valida totais do ibge na preparacao`
- [ ] 3.3 [QA] Teste feature com fixture mínima (2 UFs, 4 municípios, ~10 setores cobrindo: situação nula, área 0, setor sem demografia, homens+mulheres < pop, município sem setores) verificando cada cenário da spec `dados-censo` (soma de setores, `nao_informado`, densidade `null`, densidade ponderada da UF, posições 1..N, idempotência, `municipio_busca` sem o registro não consultável) — commit `test(backend): regras de agregacao do censo`
- [ ] 3.4 [QA] Teste do grupo `dados` sobre a base real preparada (`@group dados`, pula se o arquivo não existir): 27 UFs, 5.570 consultáveis, população 203.080.756, área 8.510.417 ±1 — commit `test(backend): totais oficiais do ibge`

## 4. Integração no build

- [ ] 4.1 [BACK] Dockerfile: `COPY censo.sqlite`, `RUN php artisan censo:preparar`, `chmod 0444`; `config/database.php` apontando para a cópia; verificar `docker compose build backend` e `docker compose run --rm backend php artisan test --group=dados` — commit `build(backend): prepara base do censo no build`
- [ ] 4.2 [QA] Verificar `git status` limpo após o build (arquivo da raiz intocado) e `md5sum censo.sqlite` igual ao original
