import { computed, inject, Injectable, signal } from '@angular/core';
import { rxResource } from '@angular/core/rxjs-interop';

import { CensoApiService } from '../../core/api/censo-api.service';
import { apiErroDe } from '../../core/http/problema.interceptor';

export type EstadoTelaMunicipio = 'ocioso' | 'carregando' | 'sucesso' | 'nao-encontrado' | 'erro';

/** Estado da Tela 1, provido na rota /municipios. A página só liga URL ↔ store. */
@Injectable()
export class MunicipioStore {
  private readonly api = inject(CensoApiService);

  readonly codigo = signal<string | undefined>(undefined);

  readonly resumo = rxResource({
    params: () => this.codigo(),
    stream: ({ params: codigo }) => this.api.municipio(codigo),
  });

  readonly rotulo = computed(() => {
    const resumo = this.resumo.hasValue() ? this.resumo.value() : undefined;

    return resumo ? `${resumo.nome} - ${resumo.uf.sigla}` : null;
  });

  readonly estado = computed<EstadoTelaMunicipio>(() => {
    if (!this.codigo()) {
      return 'ocioso';
    }
    if (this.resumo.isLoading()) {
      return 'carregando';
    }
    if (this.resumo.status() === 'error') {
      return apiErroDe(this.resumo.error())?.status === 404 ? 'nao-encontrado' : 'erro';
    }

    return 'sucesso';
  });

  tentarNovamente(): void {
    this.resumo.reload();
  }
}
