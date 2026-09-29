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
});
