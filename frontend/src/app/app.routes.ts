import { Routes } from '@angular/router';

import { EstadoStore } from './features/estado/estado.store';
import { MunicipioStore } from './features/municipio/municipio.store';

const carregarBuscaMunicipio = () =>
  import('./features/municipio/busca-municipio.page').then((m) => m.BuscaMunicipioPage);
const carregarBuscaEstado = () => import('./features/estado/busca-estado.page').then((m) => m.BuscaEstadoPage);

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
    providers: [EstadoStore],
    children: [
      { path: '', loadComponent: carregarBuscaEstado },
      { path: ':sigla', loadComponent: carregarBuscaEstado },
    ],
  },
  { path: '**', redirectTo: 'municipios' },
];
