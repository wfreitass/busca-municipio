import {
  afterNextRender,
  ChangeDetectionStrategy,
  Component,
  effect,
  ElementRef,
  inject,
  input,
  output,
  signal,
  viewChild,
} from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { MatAutocompleteModule } from '@angular/material/autocomplete';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { catchError, debounceTime, distinctUntilChanged, filter, finalize, map, Observable, of, switchMap } from 'rxjs';

import { CensoApiService } from '../../core/api/censo-api.service';
import { MunicipioSugestao } from '../../core/api/censo.models';

const TAMANHO_MINIMO = 2;

@Component({
  selector: 'app-municipio-autocomplete',
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [ReactiveFormsModule, MatAutocompleteModule, MatFormFieldModule, MatInputModule, MatProgressSpinnerModule],
  template: `
    <mat-form-field appearance="outline" class="campo" subscriptSizing="dynamic">
      <mat-label>Município</mat-label>
      <input
        #campo
        matInput
        type="search"
        autocomplete="off"
        placeholder="Ex.: São Paulo, Bom Jesus…"
        [formControl]="controle"
        [matAutocomplete]="auto"
      />
      @if (carregando()) {
        <mat-spinner matSuffix diameter="20" aria-label="Buscando municípios" />
      }
      <mat-hint>Digite ao menos {{ tamanhoMinimo }} letras do nome do município</mat-hint>
      <mat-autocomplete #auto="matAutocomplete" [displayWith]="exibir" (optionSelected)="selecionado.emit($event.option.value)">
        @for (municipio of sugestoes() ?? []; track municipio.codigo) {
          <mat-option [value]="municipio">{{ municipio.rotulo }}</mat-option>
        }
        @if (sugestoes()?.length === 0) {
          <mat-option disabled>Nenhum município encontrado</mat-option>
        }
      </mat-autocomplete>
    </mat-form-field>
  `,
  styles: `
    .campo { width: 100%; }
  `,
})
export class MunicipioAutocompleteComponent {
  private readonly api = inject(CensoApiService);
  private readonly campo = viewChild.required<ElementRef<HTMLInputElement>>('campo');

  /** Rótulo do município já exibido (deep link): preenche o campo sem disparar busca. */
  readonly rotuloAtual = input<string | null>(null);
  readonly selecionado = output<MunicipioSugestao>();

  protected readonly tamanhoMinimo = TAMANHO_MINIMO;
  protected readonly controle = new FormControl<string | MunicipioSugestao>('', { nonNullable: true });
  protected readonly carregando = signal(false);

  /** `null` = nenhuma busca feita (termo curto); `[]` = busca sem resultado. */
  protected readonly sugestoes = toSignal(
    this.controle.valueChanges.pipe(
      filter((valor): valor is string => typeof valor === 'string'),
      map((termo) => termo.trim()),
      debounceTime(300),
      distinctUntilChanged(),
      // switchMap cancela a requisição anterior ainda pendente (digitação rápida).
      switchMap((termo) => (termo.length < TAMANHO_MINIMO ? of(null) : this.buscar(termo))),
    ),
    { initialValue: null },
  );

  constructor() {
    afterNextRender(() => this.campo().nativeElement.focus());

    effect(() => {
      const rotulo = this.rotuloAtual();
      if (rotulo && this.controle.value !== rotulo && typeof this.controle.value === 'string') {
        this.controle.setValue(rotulo, { emitEvent: false });
      }
    });
  }

  protected exibir(valor: string | MunicipioSugestao | null): string {
    return typeof valor === 'string' || valor === null ? (valor ?? '') : valor.rotulo;
  }

  private buscar(termo: string): Observable<MunicipioSugestao[]> {
    this.carregando.set(true);

    return this.api.buscarMunicipios(termo).pipe(
      catchError(() => of([])),
      finalize(() => this.carregando.set(false)),
    );
  }
}
