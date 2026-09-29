import { Component, inject, signal } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { CensoApiService } from '../core/api/censo-api.service';

@Component({
  selector: 'app-root',
  imports: [RouterLink, RouterLinkActive, RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.scss',
})
export class App {
  private readonly api = inject(CensoApiService);
  readonly apiUnavailable = signal(false);

  constructor() {
    this.api.health().subscribe({
      error: () => this.apiUnavailable.set(true),
    });
  }
}
