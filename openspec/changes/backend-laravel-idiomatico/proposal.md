# Proposal

## Why

Na revisão do back-end, a arquitetura se mostrou um meio-termo sem critério: não é hexagonal (existe uma única porta, `MunicipioSearch`, e as `Queries/` acessam o banco direto pela facade `DB`, sem domínio nem casos de uso) e também não é Laravel idiomático (nenhum Model; DTOs escritos à mão fazem o papel que os Models Eloquent fariam). As consultas ficaram espalhadas em três pastas (`Censo/`, `Queries/`, `Busca/`), e `Censo/` mistura regras do dado com o ETL de SQL. Para quem lê o código, isso comunica falta de direção.

A decisão é seguir **Laravel idiomático de forma consistente**: é o que um avaliador Laravel espera encontrar, reduz arquivos e combina com o "mais simples possível" do enunciado.

Também removemos o `declare(strict_types=1)` dos arquivos, alinhando o código ao estilo do esqueleto do Laravel. Registro importante: o PHP **não** oferece configuração global para `strict_types` (não há diretiva no `php.ini`); a declaração é por arquivo, então removê-la desliga o modo estrito. A checagem de tipos passa a depender do Larastan nível 8 (análise estática), que já roda no CI.

## What Changes

- **Models Eloquent** `App\Models\Municipio` (tabela `municipio_resumo`) e `App\Models\Uf` (tabela `uf_resumo`), com PK texto, sem timestamps, relações `Municipio::uf()` / `Uf::municipios()`, scopes `consultaveis()` e `rankingDensidade()`, e resolução de route model binding (sigla da UF sem distinção de caixa; só municípios consultáveis).
- Controllers recebem Models via **route model binding**; Resources passam a transformar Models.
- Removidas as pastas `app/Dados/` (DTOs) e `app/Queries/`.
- `app/Censo/` desmembrada: ETL de build vira `app/Actions/PrepararBaseCenso.php`; `SiglasUf` e `NormalizadorTexto` vão para `app/Support/`.
- Busca continua atrás de uma interface (único ponto com variação prevista de motor), renomeada para `App\Busca\BuscaMunicipios` / `Fts5BuscaMunicipios`, agora devolvendo `Collection<Municipio>`.
- `declare(strict_types=1)` removido de todos os arquivos PHP e a regra correspondente removida do `pint.json`.

## Capabilities

### New Capabilities
- Nenhuma.

### Modified Capabilities
- Nenhuma. Refatoração interna: contrato HTTP (`docs/api/openapi.yaml`), specs e testes de comportamento permanecem iguais (`skip_specs: true`).

## Impact

- `backend/app/**`, `backend/routes/api.php`, `backend/tests/**` (imports), `backend/pint.json`.
- Documentação: `docs/ARQUITETURA.md`, `README.md`, `openspec/config.yaml`.
- Fora de escopo: qualquer mudança de rota, formato de JSON, regra de agregação ou front-end.
