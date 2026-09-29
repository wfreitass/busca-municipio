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

/** O `resource` do Angular pode embrulhar erros que não são `Error`; recupera o ApiErro original. */
export function apiErroDe(erro: unknown): ApiErro | undefined {
  const candidato = erro instanceof Error && erro.cause !== undefined ? erro.cause : erro;

  return typeof candidato === 'object' && candidato !== null && 'status' in candidato
    ? (candidato as ApiErro)
    : undefined;
}
