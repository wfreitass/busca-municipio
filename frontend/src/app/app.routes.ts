import { Routes } from '@angular/router';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'municipios' },
  {
    path: 'municipios',
    loadComponent: () => import('../features/municipio/busca-municipio.page').then((m) => m.BuscaMunicipioPage),
  },
  {
    path: 'estados',
    loadComponent: () => import('../features/estado/busca-estado.page').then((m) => m.BuscaEstadoPage),
  },
  { path: '**', redirectTo: 'municipios' },
];
