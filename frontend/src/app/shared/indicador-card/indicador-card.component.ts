import { ChangeDetectionStrategy, Component, input } from '@angular/core';

@Component({
  selector: 'app-indicador-card',
  changeDetection: ChangeDetectionStrategy.OnPush,
  template: `
    <span class="rotulo">{{ rotulo() }}</span>
    <strong class="valor">{{ valor() }}</strong>
    @if (unidade()) {
      <span class="unidade">{{ unidade() }}</span>
    }
  `,
  styles: `
    :host {
      background: var(--mat-sys-surface-container-low);
      border: 1px solid var(--mat-sys-outline-variant);
      border-radius: 12px;
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
      padding: 1rem 1.25rem;
    }
    .rotulo { color: var(--mat-sys-on-surface-variant); font-size: 0.875rem; }
    .valor { font-size: 1.6rem; font-variant-numeric: tabular-nums; line-height: 1.2; }
    .unidade { color: var(--mat-sys-on-surface-variant); font-size: 0.8rem; }
  `,
})
export class IndicadorCardComponent {
  readonly rotulo = input.required<string>();
  readonly valor = input.required<string>();
  readonly unidade = input<string>();
}
