import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class CensoApiService {
  private readonly http = inject(HttpClient);
  private readonly apiBaseUrl = '/api/v1';

  health(): Observable<{ status: 'ok' }> {
    return this.http.get<{ status: 'ok' }>('/api/health');
  }
}
