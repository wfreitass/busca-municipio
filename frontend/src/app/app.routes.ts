import { Routes } from '@angular/router';

import { MunicipioStore } from './features/municipio/municipio.store';

const carregarBuscaMunicipio = () =>
  import('./features/municipio/busca-municipio.page').then((m) => m.BuscaMunicipioPage);

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'municipios' },
  {
    path: 'municipios',
    providers: [MunicipioStore],
    children: [
      { path: '', loadComponent: carregarBuscaMunicipio },
      { path: ':codigo', loadComponent: carregarBuscaMunicipio },
    ],
  },
  {
    path: 'estados',
    loadComponent: () => import('./features/estado/busca-estado.page').then((m) => m.BuscaEstadoPage),
  },
  { path: '**', redirectTo: 'municipios' },
];
