import { ChangeDetectionStrategy, Component, input } from '@angular/core';

import { UfResumo } from '../../core/api/censo.models';
import { IndicadorCardComponent } from '../../shared/indicador-card/indicador-card.component';
import { NumeroOuTracoPipe } from '../../shared/numero-ou-traco.pipe';

@Component({
  selector: 'app-uf-resumo',
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [IndicadorCardComponent, NumeroOuTracoPipe],
  template: `
    @let uf = resumo();
    <app-indicador-card rotulo="População" [valor]="uf.populacao | numeroOuTraco" unidade="habitantes" />
    <app-indicador-card rotulo="Área total" [valor]="uf.area_km2 | numeroOuTraco: '1.2-2'" unidade="km²" />
    <app-indicador-card rotulo="Densidade demográfica" [valor]="uf.densidade_hab_km2 | numeroOuTraco: '1.2-2'" unidade="hab/km²" />
    <app-indicador-card rotulo="Municípios" [valor]="uf.total_municipios | numeroOuTraco" unidade="no ranking" />
  `,
  styles: `
    :host {
      display: grid;
      gap: 1rem;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
  `,
})
export class UfResumoComponent {
  readonly resumo = input.required<UfResumo>();
}
