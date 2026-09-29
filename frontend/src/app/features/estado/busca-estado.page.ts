import { ChangeDetectionStrategy, Component, effect, inject, input } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatSelectModule } from '@angular/material/select';
import { Router } from '@angular/router';

import { EstadoStore } from './estado.store';
import { MudancaPagina, RankingMunicipiosComponent } from './ranking-municipios.component';
import { UfResumoComponent } from './uf-resumo.component';

function inteiroPositivo(valor: string | undefined, padrao: number): number {
  const numero = Number(valor);

  return Number.isInteger(numero) && numero >= 1 ? numero : padrao;
}

@Component({
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [MatButtonModule, MatFormFieldModule, MatProgressBarModule, MatSelectModule, RankingMunicipiosComponent, UfResumoComponent],
  templateUrl: './busca-estado.page.html',
  styleUrl: './busca-estado.page.scss',
})
export class BuscaEstadoPage {
  /** Parâmetro de rota /estados/:sigla e query params ?pagina=&por_pagina= (withComponentInputBinding). */
  readonly sigla = input<string>();
  readonly pagina = input<string>();
  // Mesmo nome do query param e do parâmetro da API.
  readonly por_pagina = input<string>();

  protected readonly store = inject(EstadoStore);
  private readonly router = inject(Router);

  constructor() {
    effect(() => {
      this.store.sigla.set(this.sigla()?.toUpperCase());
      this.store.pagina.set(inteiroPositivo(this.pagina(), 1));
      this.store.porPagina.set(Math.min(inteiroPositivo(this.por_pagina(), 50), 100));
    });
  }

  protected escolherUf(sigla: string): void {
    // Sem query params: trocar de UF volta para a primeira página.
    void this.router.navigate(['/estados', sigla]);
  }

  protected paginar({ pagina, porPagina }: MudancaPagina): void {
    const mudouTamanho = porPagina !== this.store.porPagina();
    void this.router.navigate([], {
      queryParams: { pagina: mudouTamanho ? 1 : pagina, por_pagina: porPagina },
      queryParamsHandling: 'merge',
    });
  }
}
