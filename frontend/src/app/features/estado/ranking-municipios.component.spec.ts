import { registerLocaleData } from '@angular/common';
import localePt from '@angular/common/locales/pt';
import { LOCALE_ID } from '@angular/core';
import { TestBed } from '@angular/core/testing';
import { MatPaginator } from '@angular/material/paginator';
import { By } from '@angular/platform-browser';
import { provideRouter } from '@angular/router';
import { beforeAll, describe, expect, it } from 'vitest';

import { RankingMunicipiosComponent } from './ranking-municipios.component';

describe('RankingMunicipiosComponent', () => {
  beforeAll(() => registerLocaleData(localePt));

  it('exibe a posição global recebida e o total no paginador', () => {
    TestBed.configureTestingModule({ providers: [provideRouter([]), { provide: LOCALE_ID, useValue: 'pt-BR' }] });
    const fixture = TestBed.createComponent(RankingMunicipiosComponent);
    fixture.componentRef.setInput('itens', [
      { posicao: 51, codigo: '3500600', nome: 'Águas de São Pedro', populacao: 2780, area_km2: 3.61, densidade_hab_km2: 769.62 },
      { posicao: 52, codigo: '3500001', nome: 'Sem Área', populacao: 10, area_km2: 0, densidade_hab_km2: null },
    ]);
    fixture.componentRef.setInput('meta', { pagina: 2, por_pagina: 50, total: 645, total_paginas: 13 });
    fixture.detectChanges();

    const texto = (fixture.nativeElement as HTMLElement).textContent ?? '';
    expect(texto).toContain('51º');
    expect(texto).toContain('769,62');
    expect(texto).toContain('—');
    expect(texto).toContain('51 – 100 de 645');

    const paginador = fixture.debugElement.query(By.directive(MatPaginator)).componentInstance as MatPaginator;
    expect(paginador.length).toBe(645);
    expect(paginador.pageIndex).toBe(1);
  });
});
