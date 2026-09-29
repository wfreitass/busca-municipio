import type { components } from './schema';

// Aliases legíveis dos tipos gerados de docs/api/openapi.yaml (npm run api:tipos).
type Schemas = components['schemas'];

export type UfRef = Schemas['UfRef'];
export type MunicipioSugestao = Schemas['MunicipioSugestao'];
export type MunicipioResumo = Schemas['MunicipioResumo'];
export type UfResumo = Schemas['UfResumo'];
export type ItemRanking = Schemas['ItemRanking'];
export type MetaPaginacao = Schemas['MetaPaginacao'];

export interface Pagina<T> {
  data: T[];
  meta: MetaPaginacao;
}
