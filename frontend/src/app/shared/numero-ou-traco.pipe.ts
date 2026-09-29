import { formatNumber } from '@angular/common';
import { inject, LOCALE_ID, Pipe, PipeTransform } from '@angular/core';

/** Formata em pt-BR; valor ausente (ex.: densidade de área zero) vira "—". */
@Pipe({ name: 'numeroOuTraco' })
export class NumeroOuTracoPipe implements PipeTransform {
  private readonly locale = inject(LOCALE_ID);

  transform(valor: number | null | undefined, formato = '1.0-0'): string {
    return valor === null || valor === undefined ? '—' : formatNumber(valor, this.locale, formato);
  }
}
