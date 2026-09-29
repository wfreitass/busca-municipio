import { ChangeDetectionStrategy, Component, input, output } from '@angular/core';
import { MatPaginatorIntl, MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatTableModule } from '@angular/material/table';
import { RouterLink } from '@angular/router';

import { ItemRanking, MetaPaginacao } from '../../core/api/censo.models';
import { PaginadorPtBr } from '../../core/i18n/paginador-pt-br';
import { NumeroOuTracoPipe } from '../../shared/numero-ou-traco.pipe';

export interface MudancaPagina {
  pagina: number;
  porPagina: number;
}

@Component({
  selector: 'app-ranking-municipios',
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [MatPaginatorModule, MatProgressBarModule, MatTableModule, NumeroOuTracoPipe, RouterLink],
  // Provido aqui (chunk lazy) e não no app.config, para não inflar o bundle inicial.
  providers: [{ provide: MatPaginatorIntl, useClass: PaginadorPtBr }],
  templateUrl: './ranking-municipios.component.html',
  styleUrl: './ranking-municipios.component.scss',
})
export class RankingMunicipiosComponent {
  readonly itens = input.required<ItemRanking[]>();
  readonly meta = input.required<MetaPaginacao>();
  readonly carregando = input(false);
  readonly mudouPagina = output<MudancaPagina>();

  protected readonly colunas = ['posicao', 'nome', 'populacao', 'area', 'densidade'];

  protected aoPaginar(evento: PageEvent): void {
    this.mudouPagina.emit({ pagina: evento.pageIndex + 1, porPagina: evento.pageSize });
  }
}
