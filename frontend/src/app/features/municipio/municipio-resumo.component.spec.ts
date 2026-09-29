import { registerLocaleData } from '@angular/common';
import localePt from '@angular/common/locales/pt';
import { LOCALE_ID } from '@angular/core';
import { TestBed } from '@angular/core/testing';
import { beforeAll, describe, expect, it } from 'vitest';

import { MunicipioResumo } from '../../core/api/censo.models';
import { MunicipioResumoComponent } from './municipio-resumo.component';

const SAO_PAULO: MunicipioResumo = {
  codigo: '3550308',
  nome: 'São Paulo',
  uf: { codigo: '35', sigla: 'SP', nome: 'São Paulo' },
  populacao: 11451999,
  area_km2: 1521.2,
  densidade_hab_km2: 7528.26,
  setores: { total: 27301, urbanos: 27037, rurais: 254, sem_classificacao: 10 },
  sexo: { homens: 5380188, mulheres: 6060887, nao_informado: 10924, percentual_homens: 47.03, percentual_mulheres: 52.97 },
};

function renderizar(resumo: MunicipioResumo): string {
  TestBed.configureTestingModule({ providers: [{ provide: LOCALE_ID, useValue: 'pt-BR' }] });
  const fixture = TestBed.createComponent(MunicipioResumoComponent);
  fixture.componentRef.setInput('resumo', resumo);
  fixture.detectChanges();

  return (fixture.nativeElement as HTMLElement).textContent ?? '';
}

describe('MunicipioResumoComponent', () => {
  beforeAll(() => registerLocaleData(localePt));

  it('exibe os indicadores formatados em pt-BR', () => {
    const texto = renderizar(SAO_PAULO);

    expect(texto).toContain('11.451.999');
    expect(texto).toContain('1.521,20');
    expect(texto).toContain('7.528,26');
    expect(texto).toContain('Sem classificação');
  });

  it('mostra traço para densidade nula e oculta categorias zeradas', () => {
    const texto = renderizar({
      ...SAO_PAULO,
      densidade_hab_km2: null,
      setores: { ...SAO_PAULO.setores, sem_classificacao: 0 },
      sexo: { ...SAO_PAULO.sexo, nao_informado: 0 },
    });

    expect(texto).toContain('—');
    expect(texto).not.toContain('Sem classificação');
    expect(texto).not.toContain('Não informado');
  });
});
