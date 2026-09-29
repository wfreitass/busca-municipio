# Exploração do `censo.sqlite`

Executada em 28/09/2026 sobre uma cópia do arquivo original, dentro da imagem
Docker com SQLite 3.46.1. As consultas abaixo foram executadas antes da
implementação do read model.

## H1 — registro extra

Consulta:

```sql
SELECT m.cd_mun, m.nm_mun, m.cd_uf,
       COALESCE(SUM(s.populacao), 0), COALESCE(SUM(s.area_km2), 0),
       COUNT(s.cd_setor)
FROM municipio m LEFT JOIN setor s USING (cd_mun)
GROUP BY m.cd_mun
HAVING COALESCE(SUM(s.populacao), 0) = 0 OR COUNT(s.cd_setor) = 0;
```

Resultado: `cd_mun='.'`, `nm_mun=''`, `cd_uf='43'`, 2 setores, população
`0` e área `13085.864101 km²`. É a área operacional das lagoas dos Patos e
Mirim, não um município consultável.

Decisão: `consultavel=0`; excluir da busca e dos rankings, mas manter sua área
na agregação do RS e do Brasil.

## H2 — fonte da população

Consulta: soma de `setor.populacao` versus soma de `demografia.moradores`.

Resultado: `setor.populacao=203080756`; `demografia.moradores=202561627`.

Decisão: população sempre vem da soma de `setor.populacao`, com `NULL` tratado
como zero.

## H3 — setores sem demografia

Consulta:

```sql
SELECT COUNT(*), COALESCE(SUM(s.populacao), 0)
FROM setor s LEFT JOIN demografia d USING (cd_setor)
WHERE d.cd_setor IS NULL;
```

Resultado: `9327` setores sem linha em `demografia`; população desses setores:
`0`.

Decisão: manter `LEFT JOIN` para não descartar setores. A regra geral de
`sexo_nao_informado` continua sendo `MAX(populacao-homens-mulheres,0)`.

## H4 — inconsistências de sexo

Consulta:

```sql
SELECT COUNT(*) FROM demografia
WHERE COALESCE(homens,0)+COALESCE(mulheres,0) <> COALESCE(moradores,0);
```

Resultado: `56` linhas divergentes.

Decisão: não usar `moradores` para derivar homens/mulheres; somar as colunas
com `COALESCE` e calcular o não informado contra a população de setores.

## H5 — situações dos setores

Consulta:

```sql
SELECT COALESCE(situacao, '<NULL>'), COUNT(*)
FROM setor GROUP BY situacao ORDER BY situacao;
```

Resultado: nula `1103`, `Rural` `112031`, `Urbana` `354965`.

Decisão: comparação exata; nulo e qualquer valor futuro fora de Urbana/Rural
entram em `sem_classificacao`.

## H6 — áreas nulas ou zero

Consulta:

```sql
SELECT SUM(area_km2 IS NULL), SUM(area_km2=0) FROM setor;
```

Resultado: `0` nulas e `0` zero.

Decisão: manter `COALESCE(area_km2,0)` e a regra de densidade nula para área
agregada igual a zero, inclusive para fixtures futuras.

## H7 — área nacional

Consulta: `SELECT SUM(area_km2) FROM setor;`

Resultado: `8510417.2472671 km²`, isto é, `8510417 km²` com arredondamento.

Decisão: somar sem arredondar durante a preparação e validar com tolerância de
um km². A área fecha incluindo o registro extra de H1.

## H8 — tabela de UFs

Consulta: `SELECT cd_uf, nm_uf FROM uf ORDER BY cd_uf;`

Resultado: 27 códigos, de `11` a `53`, com os nomes oficiais (incluindo `35`
São Paulo, `43` Rio Grande do Sul e `14` Roraima).

Decisão: usar o mapa estático de 27 siglas definido em `SiglasUf`; não depender
de uma coluna de sigla na tabela crua.

## H9 — homônimos

Consulta:

```sql
SELECT nm_mun, COUNT(*) FROM municipio
GROUP BY nm_mun HAVING COUNT(*) > 1
ORDER BY 2 DESC, nm_mun LIMIT 10;
```

Resultado: `Bom Jesus` e `São Domingos` aparecem 5 vezes; `Bonito`,
`Planalto`, `Santa Helena`, `Santa Inês`, `Santa Luzia`, `Santa Terezinha`,
`São Francisco` e `Vera Cruz` aparecem 4 vezes.

Decisão: o rótulo sempre inclui `Nome - SIGLA` e as consultas usam
`cd_mun`, nunca o nome como chave.

## H10 — acentos, apóstrofos e hífens

Consulta: municípios com apóstrofo (`char(39)`) ou hífen.

Resultado: aparecem, entre outros, `Alta Floresta D'Oeste`, `Espigão
D'Oeste`, `Pau D'Arco`, `Olho d'Água das Flores` e vários nomes hifenizados.

Decisão: normalizar em PHP com ASCII, minúsculas, substituição de apóstrofos
e hífens por espaço, colapso de espaços e `trim`, usando o mesmo método na
preparação e na busca.

## Totais de controle

```text
municipios brutos: 5571
UFs:               27
soma população:    203080756
soma área:         8510417.2472671 km²
```

O 5.571º registro é o extra `.` do RS. Ele tem setores (2), população zero e
13.085,864101 km². A área nacional bate com ele; a população oficial bate por
`setor.populacao`, não por `demografia.moradores`.
