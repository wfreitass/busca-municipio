# Design

## Context

Schema cru (só PKs, tabelas `WITHOUT ROWID`). Volume: 27 UFs, 5.571 municípios, 468.099 setores, 458.772 linhas de demografia. Ver `proposal.md` para a motivação e `docs/ARQUITETURA.md` §1 para a escolha do read model.

## Goals / Non-Goals

**Goals:**
- Concentrar **todas** as regras de agregação num único passo testável.
- Consultas da API em O(1)/O(log n) sobre tabelas pequenas (≤ 5.571 linhas).
- Totais batendo com o IBGE e documentados.

**Non-Goals:**
- ORM/Eloquent, migrations do Laravel para o read model (é artefato de build, não esquema de aplicação).
- Otimizar o tempo do build além do razoável (< 30 s).

## Hipóteses a validar na exploração (antes de codar)

A tarefa 1.1 executa estas consultas e registra os resultados em `exploracao.md`. Cada hipótese tem a decisão já tomada para cada resultado possível — assim a exploração não trava a implementação.

| # | Hipótese | Consulta | Decisão |
| --- | --- | --- | --- |
| H1 | O 5.571º município é uma área operacional (ex.: lagoa) sem população — **pré-checagem: `cd_mun='.'`, nome vazio, RS, pop 0, 13.085,86 km²; área nacional só fecha com ela** | `SELECT m.cd_mun, m.nm_mun, m.cd_uf, SUM(s.populacao), SUM(s.area_km2), COUNT(s.cd_setor) FROM municipio m LEFT JOIN setor s USING(cd_mun) GROUP BY m.cd_mun HAVING COALESCE(SUM(s.populacao),0)=0 OR COUNT(s.cd_setor)=0;` e comparar nomes com a lista oficial | Marcar `consultavel = 0` nesse registro. Se a área oficial do Brasil só bate **com** a área dele, mantê-la na UF; caso contrário, excluí-la. Registrar qual caso ocorreu. |
| H2 | `SUM(setor.populacao)` = 203.080.756 | `SELECT SUM(populacao) FROM setor;` vs `SELECT SUM(moradores) FROM demografia;` | Usar a fonte que bate (esperado: `setor.populacao`). |
| H3 | 9.327 setores sem `demografia` | `SELECT COUNT(*) FROM setor s LEFT JOIN demografia d USING(cd_setor) WHERE d.cd_setor IS NULL;` + soma da população deles | LEFT JOIN; diferença vira `nao_informado`. |
| H4 | `homens + mulheres ≠ moradores` em alguns setores | `SELECT COUNT(*) FROM demografia WHERE COALESCE(homens,0)+COALESCE(mulheres,0) <> COALESCE(moradores,0);` | Expor `nao_informado`; percentuais sobre `homens + mulheres`. |
| H5 | Valores de `situacao` além de `Urbana`/`Rural`/nulo | `SELECT situacao, COUNT(*) FROM setor GROUP BY 1;` | Comparação exata; qualquer outro valor → `sem_classificacao`. Se houver variação de caixa/espaço, normalizar com `TRIM`/`LOWER`. |
| H6 | `area_km2` nula ou 0 | `SELECT COUNT(*) FROM setor WHERE area_km2 IS NULL OR area_km2 = 0;` | `COALESCE(...,0)`; densidade `NULL` se área 0. |
| H7 | `SUM(area_km2)` ≈ 8.510.417 | `SELECT SUM(area_km2) FROM setor;` | Conferir; se divergir, investigar H1. |
| H8 | `uf` sem sigla | `SELECT * FROM uf;` | Mapa estático `SiglasUf` (11 RO … 53 DF). |
| H9 | Homônimos entre UFs | `SELECT nm_mun, COUNT(*) FROM municipio GROUP BY nm_mun HAVING COUNT(*)>1 ORDER BY 2 DESC LIMIT 10;` | Rótulo sempre `Nome - UF`; seleção sempre por `cd_mun`. |
| H10 | Nomes com acento/apóstrofo/hífen | `SELECT nm_mun FROM municipio WHERE nm_mun LIKE '%''%' OR nm_mun LIKE '%-%' LIMIT 20;` | Coluna `nm_busca` normalizada em PHP. |

## Decisions

### D1. Onde a preparação roda: no `docker build`
```dockerfile
COPY censo.sqlite /var/www/html/database/censo.sqlite
RUN php artisan censo:preparar --database=/var/www/html/database/censo.sqlite \
 && chown root:www-data database/censo.sqlite && chmod 0444 database/censo.sqlite
```
A imagem já nasce com o read model; o arquivo fica somente leitura em runtime (qualquer escrita acidental falha alto).
- Alternativa descartada: _entrypoint_ em runtime — mais lento para subir e exige volume gravável.
- Localmente (sem Docker): `cp ../censo.sqlite database/censo.sqlite && php artisan censo:preparar` (documentado no README; `database/censo.sqlite` no `.gitignore` do backend).

### D2. Read model como tabelas físicas, não views
Views recalculariam a cada consulta. Tabelas + índices tornam o ranking de SP uma leitura indexada.

```sql
CREATE TABLE municipio_resumo (
  cd_mun TEXT PRIMARY KEY,  nm_mun TEXT NOT NULL,  nm_busca TEXT NOT NULL,
  cd_uf TEXT NOT NULL,      sigla_uf TEXT NOT NULL,
  consultavel INTEGER NOT NULL DEFAULT 1,
  populacao INTEGER NOT NULL, area_km2 REAL NOT NULL, densidade REAL,
  setores_total INTEGER NOT NULL, setores_urbanos INTEGER NOT NULL,
  setores_rurais INTEGER NOT NULL, setores_sem_classificacao INTEGER NOT NULL,
  homens INTEGER NOT NULL, mulheres INTEGER NOT NULL, sexo_nao_informado INTEGER NOT NULL,
  posicao_densidade_uf INTEGER
) WITHOUT ROWID;
CREATE INDEX ix_mr_busca   ON municipio_resumo(consultavel, nm_busca);
CREATE INDEX ix_mr_ranking ON municipio_resumo(cd_uf, posicao_densidade_uf);

-- Índice textual (busca do autocomplete; ver busca-municipio design D1)
CREATE VIRTUAL TABLE municipio_busca USING fts5(
  cd_mun UNINDEXED, nm_busca, tokenize = 'trigram'
);  -- preenchida com INSERT … SELECT cd_mun, nm_busca FROM municipio_resumo WHERE consultavel = 1

CREATE TABLE uf_resumo (
  cd_uf TEXT PRIMARY KEY, sigla TEXT NOT NULL UNIQUE, nm_uf TEXT NOT NULL,
  populacao INTEGER NOT NULL, area_km2 REAL NOT NULL, densidade REAL,
  total_municipios INTEGER NOT NULL
) WITHOUT ROWID;
```

### D3. Agregação em SQL, normalização e siglas em PHP
1. `DROP TABLE IF EXISTS` + `CREATE` (idempotência) dentro de **uma transação**.
2. `CREATE INDEX IF NOT EXISTS ix_setor_mun ON setor(cd_mun)` (acelera o passo 3).
3. `INSERT INTO municipio_resumo … SELECT` com agregação por município:
   ```sql
   SELECT m.cd_mun, m.nm_mun, m.cd_uf,
          COALESCE(SUM(s.populacao),0)                                   AS populacao,
          COALESCE(SUM(s.area_km2),0)                                    AS area_km2,
          COUNT(s.cd_setor)                                              AS setores_total,
          SUM(CASE WHEN s.situacao = 'Urbana' THEN 1 ELSE 0 END)         AS setores_urbanos,
          SUM(CASE WHEN s.situacao = 'Rural'  THEN 1 ELSE 0 END)         AS setores_rurais,
          COALESCE(SUM(d.homens),0) AS homens, COALESCE(SUM(d.mulheres),0) AS mulheres
     FROM municipio m
     LEFT JOIN setor s      ON s.cd_mun = m.cd_mun
     LEFT JOIN demografia d ON d.cd_setor = s.cd_setor
    GROUP BY m.cd_mun;
   ```
   Derivados (`setores_sem_classificacao`, `sexo_nao_informado = MAX(pop - h - m, 0)`, `densidade = CASE WHEN area>0 THEN pop/area END`) calculados no mesmo SELECT externo.
4. Preencher `nm_busca` e `sigla_uf` em PHP (5.571 `UPDATE`s numa transação ≈ < 1 s) usando `NormalizadorTexto` e `SiglasUf`.
   - `NormalizadorTexto`: `Str::ascii` → minúsculas → `['\'', '’', '-']` → espaço → colapsa espaços → `trim`.
   - Alternativa descartada: função SQL customizada (`sqliteCreateFunction`) — funciona, mas a mesma função é necessária no request (normalizar o termo buscado), então um único método PHP serve aos dois.
5. Marcar `consultavel = 0` conforme H1.
6. `posicao_densidade_uf` com window function + `UPDATE … FROM` (SQLite ≥ 3.33; a imagem PHP 8.3 traz ≥ 3.40). Evitar subconsulta correlacionada, que recalcularia a janela por linha (O(n²)):
   ```sql
   UPDATE municipio_resumo SET posicao_densidade_uf = r.pos
     FROM (SELECT cd_mun, ROW_NUMBER() OVER (PARTITION BY cd_uf
                  ORDER BY densidade IS NULL, densidade DESC, nm_busca) AS pos
             FROM municipio_resumo WHERE consultavel = 1) AS r
    WHERE r.cd_mun = municipio_resumo.cd_mun;
   ```
6b. `municipio_busca` (FTS5 trigram) preenchida a partir de `municipio_resumo` consultáveis. Usamos o `nm_busca` já normalizado em PHP em vez da opção `remove_diacritics` do trigram (SQLite ≥ 3.45): a normalização também trata apóstrofo/hífen e precisa ser idêntica à do termo buscado.
7. `uf_resumo` a partir de `municipio_resumo` (incluindo área de não consultáveis conforme H1), `total_municipios` = só consultáveis, `densidade = SUM(pop)/SUM(area)`.
8. Validação final no próprio comando: imprime totais Brasil (UFs, municípios, população, área) e **falha com exit code ≠ 0** se população ≠ 203.080.756 ou nº de UFs ≠ 27 — o build quebra em vez de subir com dado errado.

### D4. Conexão de banco
`config/database.php` → conexão `sqlite` com `database => env('DB_DATABASE', database_path('censo.sqlite'))`, `foreign_key_constraints => false`. O comando aceita `--database=` para testes.

## Risks / Trade-offs

- [H1 não se confirmar como "lagoa"] → a regra é genérica: "registro sem setores ou com população 0 e não presente na lista oficial"; a exploração decide e registra.
- [Arredondamento de área na soma de floats] → comparar com tolerância ±1 km²; nunca arredondar antes de somar.
- [Build falha por validação de totais] → é intencional; a mensagem de erro mostra o valor obtido.
- [Imagem maior] → aceitável (~40 MB) frente ao ganho de simplicidade.
