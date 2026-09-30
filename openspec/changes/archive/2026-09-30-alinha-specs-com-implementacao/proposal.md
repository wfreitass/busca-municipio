# Proposal

## Why

Uma auditoria das specs consolidadas contra a aplicação rodando (API, navegador e subida em clone limpo) encontrou divergências entre o que está escrito e o que o sistema faz:

- O cenário "Apóstrofo e hífen" de `api-municipios` busca `olho d agua` mas espera `Alta Floresta D'Oeste - RO`: uma edição anterior trocou só metade do cenário.
- O cenário "Termo de duas letras" cita como exemplo um município que aparece apenas em 10º lugar no resultado real.
- O exemplo de resposta do resumo do município usa números inventados, embora os valores reais de São Paulo já tenham sido medidos.
- `infraestrutura` exige `docker compose up --build`, mas um `docker compose up` simples, num clone limpo e sem nenhuma imagem em cache, já constrói e sobe tudo (verificado: cerca de 52 s com as imagens base locais).

## What Changes

- `infraestrutura`: o comando de subida passa a ser `docker compose up`; `--build` fica documentado como o comando para reconstruir após atualizar o código.
- `api-municipios`: cenários de apóstrofo e de termo de duas letras corrigidos com resultados reais; exemplo do resumo com os valores reais de São Paulo.
- README alinhado ao comando de subida.

## Capabilities

### New Capabilities
- Nenhuma.

### Modified Capabilities
- `infraestrutura`: requisito "Subida com um único comando".
- `api-municipios`: requisitos "Busca de municípios por nome" e "Resumo do município".

## Impact

- `openspec/specs/{infraestrutura,api-municipios}/spec.md` (via archive), `README.md`, `docs/ARQUITETURA.md`.
- Nenhuma mudança de código: as correções descrevem o comportamento que já existe.
