# Tasks

## 1. Estilo

- [x] 1.1 [BACK] Remover `declare(strict_types=1)` de todos os arquivos PHP do back-end e a regra `declare_strict_types` do `pint.json` (design D6); verificar `composer lint`, `composer analyse` e `php artisan test` verdes — commit `refactor(backend): remove declare strict_types em favor do larastan`

## 2. Reorganização

- [x] 2.1 [BACK] Mover `SiglasUf` e `NormalizadorTexto` para `app/Support/` e o ETL para `app/Actions/PrepararBaseCenso.php` (D5), removendo `app/Censo/`; verificar testes verdes — commit `refactor(backend): separa etl em action e utilitarios em support`

## 3. Models

- [x] 3.1 [BACK] Models `Municipio` e `Uf` sobre o read model, com relações, scopes, casts, `@property` e route binding (D1, D2) — commit `feat(backend): models eloquent do read model`
- [x] 3.2 [BACK] Controllers com route model binding e `->missing()`, Resources recebendo Models, ranking com `forPage` (D2, D3); remover `app/Queries/` e `app/Dados/` — verificar testes de API e de contrato sem alterar asserções — commit `refactor(backend): api usa models eloquent e route model binding`
- [x] 3.3 [BACK] Busca: `BuscaMunicipios` / `Fts5BuscaMunicipios` devolvendo `Collection<Municipio>` (D4) — commit `refactor(backend): busca fts5 devolve models`

## 4. Documentação e verificação

- [ ] 4.1 [BA] Atualizar `docs/ARQUITETURA.md`, `README.md` e `openspec/config.yaml` com a nova organização e o registro do recuo (DTOs/Query Builder → Models) — commit `docs: arquitetura laravel idiomatica`
- [ ] 4.2 [QA] `docker compose up --build` em clone limpo + verificação das duas telas no navegador + suítes de teste; verificar que nada observável mudou
