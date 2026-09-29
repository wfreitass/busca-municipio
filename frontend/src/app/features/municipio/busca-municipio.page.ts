import { ChangeDetectionStrategy, Component, effect, inject, input } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { Router } from '@angular/router';

import { MunicipioSugestao } from '../../core/api/censo.models';
import { MunicipioAutocompleteComponent } from './municipio-autocomplete.component';
import { MunicipioResumoComponent } from './municipio-resumo.component';
import { MunicipioStore } from './municipio.store';

@Component({
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [MatButtonModule, MatProgressBarModule, MunicipioAutocompleteComponent, MunicipioResumoComponent],
  template: `
    <h1>Busca de municípios</h1>
    <p class="intro">Encontre um município pelo nome e veja os números do Censo Demográfico 2022 (IBGE).</p>

    <app-municipio-autocomplete [rotuloAtual]="store.rotulo()" (selecionado)="selecionar($event)" />

    @switch (store.estado()) {
      @case ('carregando') {
        <mat-progress-bar mode="indeterminate" aria-label="Carregando dados do município" />
      }
      @case ('nao-encontrado') {
        <p class="aviso" role="alert">Município não encontrado.</p>
      }
      @case ('erro') {
        <div class="aviso erro" role="alert">
          <span>Não foi possível carregar os dados.</span>
          <button mat-stroked-button type="button" (click)="store.tentarNovamente()">Tentar novamente</button>
        </div>
      }
      @case ('sucesso') {
        @if (store.resumo.hasValue()) {
          <app-municipio-resumo [resumo]="store.resumo.value()" />
        }
      }
    }
  `,
  styles: `
    h1 { margin: 0; }
    .intro { color: var(--mat-sys-on-surface-variant); margin: 0.5rem 0 1.5rem; }
    mat-progress-bar { margin-top: 1.5rem; }
    .aviso { align-items: center; display: flex; gap: 1rem; margin-top: 1.5rem; }
    .erro { color: var(--mat-sys-error); }
  `,
})
export class BuscaMunicipioPage {
  /** Parâmetro de rota /municipios/:codigo (withComponentInputBinding). */
  readonly codigo = input<string>();

  protected readonly store = inject(MunicipioStore);
  private readonly router = inject(Router);

  constructor() {
    effect(() => this.store.codigo.set(this.codigo()));
  }

  protected selecionar(municipio: MunicipioSugestao): void {
    void this.router.navigate(['/municipios', municipio.codigo]);
  }
}
