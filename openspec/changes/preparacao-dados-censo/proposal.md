# Proposal

## Why

O `censo.sqlite` vem cru: sem índices, sem agregados, sem sigla de UF, com nulos, com 5.571 municípios (o IBGE diz 5.570) e com 9.327 setores sem linha em `demografia`. Toda resposta das duas telas depende de agregar 468 mil setores corretamente. Se cada endpoint refizer essa agregação em SQL próprio, as regras de nulos/densidade se espalham e divergem — e o avaliador vai conferir os totais contra os números oficiais do IBGE.

## What Changes

- Exploração documentada do dado (`openspec/changes/preparacao-dados-censo/exploracao.md`) com as consultas executadas e os números obtidos, decidindo cada hipótese de armadilha (H1–H10 do design).
- Comando Artisan `censo:preparar` que, numa **cópia** do `censo.sqlite`, cria o _read model_:
  - `municipio_resumo`: um registro por município com população, área, densidade, contagem de setores por situação, homens/mulheres/não informado, nome normalizado para busca, sigla da UF e posição no ranking de densidade da UF.
  - `uf_resumo`: um registro por UF com sigla, população, área, densidade e total de municípios.
  - Índices para busca por nome e ranking paginado.
- Execução do comando no build da imagem do back-end.
- Conexão de banco do Laravel apontando para a cópia preparada.

## Capabilities

### New Capabilities
- `dados-censo`: regras de agregação e consistência dos dados do Censo 2022 que todas as consultas da aplicação devem respeitar.

### Modified Capabilities
- Nenhuma.

## Impact

- `backend/app/Console/Commands/PrepararCenso.php`, `backend/app/Censo/*`, `backend/Dockerfile` (passo `censo:preparar`), `backend/config/database.php`.
- Imagem do back-end cresce ~40 MB (cópia + agregados). Build ganha poucos segundos.
- Fora de escopo: atualização incremental de dados, outras variáveis do censo (idade, cor/raça etc.).
