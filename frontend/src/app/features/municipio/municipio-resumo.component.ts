import { ChangeDetectionStrategy, Component, computed, input } from '@angular/core';

import { MunicipioResumo } from '../../core/api/censo.models';
import { IndicadorCardComponent } from '../../shared/indicador-card/indicador-card.component';
import { NumeroOuTracoPipe } from '../../shared/numero-ou-traco.pipe';

interface Fatia {
  rotulo: string;
  valor: number;
  percentual: number;
  classe: string;
}

function fatias(total: number, itens: [string, number, string][]): Fatia[] {
  return itens
    .filter(([, valor]) => valor > 0)
    .map(([rotulo, valor, classe]) => ({ rotulo, valor, classe, percentual: total > 0 ? (valor / total) * 100 : 0 }));
}

@Component({
  selector: 'app-municipio-resumo',
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [IndicadorCardComponent, NumeroOuTracoPipe],
  templateUrl: './municipio-resumo.component.html',
  styleUrl: './municipio-resumo.component.scss',
})
export class MunicipioResumoComponent {
  readonly resumo = input.required<MunicipioResumo>();

  protected readonly setores = computed(() => {
    const s = this.resumo().setores;

    return fatias(s.total, [
      ['Urbanos', s.urbanos, 'urbano'],
      ['Rurais', s.rurais, 'rural'],
      ['Sem classificação', s.sem_classificacao, 'neutro'],
    ]);
  });

  protected readonly sexo = computed(() => {
    const r = this.resumo();

    return fatias(r.populacao, [
      ['Homens', r.sexo.homens, 'homens'],
      ['Mulheres', r.sexo.mulheres, 'mulheres'],
      ['Não informado', r.sexo.nao_informado, 'neutro'],
    ]);
  });
}
