import { computed, inject, Injectable, signal } from '@angular/core';
import { rxResource } from '@angular/core/rxjs-interop';

import { CensoApiService } from '../../core/api/censo-api.service';
import { apiErroDe } from '../../core/http/problema.interceptor';

export type EstadoTelaUf = 'sem-uf' | 'carregando' | 'pronto' | 'nao-encontrado' | 'erro';

/** Estado da Tela 2, provido na rota /estados. Resumo e ranking são recursos separados: trocar de página não recarrega os totais. */
@Injectable()
export class EstadoStore {
  private readonly api = inject(CensoApiService);

  readonly sigla = signal<string | undefined>(undefined);
  readonly pagina = signal(1);
  readonly porPagina = signal(50);

  readonly ufs = rxResource({ stream: () => this.api.ufs() });

  readonly resumo = rxResource({
    params: () => this.sigla(),
    stream: ({ params: sigla }) => this.api.uf(sigla),
  });

  readonly ranking = rxResource({
    params: () => {
      const sigla = this.sigla();

      return sigla ? { sigla, pagina: this.pagina(), porPagina: this.porPagina() } : undefined;
    },
    stream: ({ params }) => this.api.ranking(params.sigla, params.pagina, params.porPagina),
  });

  readonly estado = computed<EstadoTelaUf>(() => {
    if (!this.sigla()) {
      return 'sem-uf';
    }
    if (this.resumo.status() === 'error') {
      return apiErroDe(this.resumo.error())?.status === 404 ? 'nao-encontrado' : 'erro';
    }
    if (this.ranking.status() === 'error') {
      return 'erro';
    }

    return this.resumo.hasValue() ? 'pronto' : 'carregando';
  });

  tentarNovamente(): void {
    this.resumo.reload();
    this.ranking.reload();
  }
}
