import { HttpErrorResponse, HttpHandlerFn, HttpRequest } from '@angular/common/http';
import { catchError, throwError } from 'rxjs';

export interface ApiErro {
  status: number;
  titulo: string;
  detalhe: string;
}

export function problemaInterceptor(request: HttpRequest<unknown>, next: HttpHandlerFn) {
  return next(request).pipe(
    catchError((error: HttpErrorResponse) => {
      const problema = error.error?.type && error.error?.title ? error.error : undefined;
      const apiError: ApiErro = problema
        ? { status: error.status, titulo: problema.title, detalhe: problema.detail ?? '' }
        : { status: error.status, titulo: 'Erro de rede', detalhe: 'A API está indisponível.' };

      return throwError(() => apiError);
    }),
  );
}
