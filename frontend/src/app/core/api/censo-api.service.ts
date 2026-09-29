import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { MunicipioResumo, MunicipioSugestao } from './censo.models';

@Injectable({ providedIn: 'root' })
export class CensoApiService {
  private readonly http = inject(HttpClient);
  private readonly apiBaseUrl = '/api/v1';

  health(): Observable<{ status: 'ok' }> {
    return this.http.get<{ status: 'ok' }>('/api/health');
  }

  buscarMunicipios(q: string, limite = 10): Observable<MunicipioSugestao[]> {
    const params = new HttpParams().set('q', q).set('limite', limite);

    return this.http
      .get<{ data: MunicipioSugestao[] }>(`${this.apiBaseUrl}/municipios`, { params })
      .pipe(map((resposta) => resposta.data));
  }

  municipio(codigo: string): Observable<MunicipioResumo> {
    return this.http
      .get<{ data: MunicipioResumo }>(`${this.apiBaseUrl}/municipios/${encodeURIComponent(codigo)}`)
      .pipe(map((resposta) => resposta.data));
  }
}
