import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { HttpHeaders } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { describe, expect, it, beforeEach, afterEach } from 'vitest';

import { CensoApiService } from './censo-api.service';
import { problemaInterceptor } from '../http/problema.interceptor';

describe('CensoApiService', () => {
  let service: CensoApiService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [
        provideHttpClient(withInterceptors([problemaInterceptor])),
        provideHttpClientTesting(),
      ],
    });
    service = TestBed.inject(CensoApiService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('health requests the operational endpoint', () => {
    service.health().subscribe((health) => expect(health.status).toBe('ok'));

    const request = http.expectOne('/api/health');
    expect(request.request.method).toBe('GET');
    request.flush({ status: 'ok' });
  });

  it('converts Problem Details into ApiErro', () => {
    service.health().subscribe({
      error: (error) => {
        expect(error).toEqual({ status: 404, titulo: 'Não encontrado', detalhe: 'Não existe.' });
      },
    });

    const request = http.expectOne('/api/health');
    request.flush(
      { type: 'about:blank', title: 'Não encontrado', status: 404, detail: 'Não existe.' },
      { status: 404, statusText: 'Not Found', headers: new HttpHeaders({ 'Content-Type': 'application/problem+json' }) },
    );
  });

  it('buscarMunicipios envia q e limite e devolve só o data', () => {
    service.buscarMunicipios('sao paulo', 5).subscribe((lista) => expect(lista).toEqual([]));

    const request = http.expectOne((r) => r.url === '/api/v1/municipios');
    expect(request.request.params.get('q')).toBe('sao paulo');
    expect(request.request.params.get('limite')).toBe('5');
    request.flush({ data: [] });
  });

  it('municipio busca o resumo pelo código', () => {
    service.municipio('3550308').subscribe((resumo) => expect(resumo.codigo).toBe('3550308'));

    http.expectOne('/api/v1/municipios/3550308').flush({ data: { codigo: '3550308' } });
  });

  it('ranking envia pagina e por_pagina e preserva o meta', () => {
    service.ranking('SP', 2, 50).subscribe((pagina) => expect(pagina.meta.total).toBe(645));

    const request = http.expectOne((r) => r.url === '/api/v1/ufs/SP/municipios');
    expect(request.request.params.get('pagina')).toBe('2');
    expect(request.request.params.get('por_pagina')).toBe('50');
    request.flush({ data: [], meta: { pagina: 2, por_pagina: 50, total: 645, total_paginas: 13 } });
  });
});
