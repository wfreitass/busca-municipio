<?php

declare(strict_types=1);

namespace App\Queries;

use App\Dados\MunicipioResumo;
use App\Dados\UfRef;
use Illuminate\Support\Facades\DB;

final class MunicipioQuery
{
    public function detalhe(string $codigo): ?MunicipioResumo
    {
        $linha = DB::table('municipio_resumo as mr')
            ->join('uf_resumo as u', 'u.cd_uf', '=', 'mr.cd_uf')
            ->where('mr.cd_mun', $codigo)
            ->where('mr.consultavel', 1)
            ->select('mr.*', 'u.sigla', 'u.nm_uf')
            ->first();

        if ($linha === null) {
            return null;
        }

        return new MunicipioResumo(
            codigo: (string) $linha->cd_mun,
            nome: (string) $linha->nm_mun,
            uf: new UfRef((string) $linha->cd_uf, (string) $linha->sigla, (string) $linha->nm_uf),
            populacao: (int) $linha->populacao,
            areaKm2: (float) $linha->area_km2,
            densidade: $linha->densidade === null ? null : (float) $linha->densidade,
            setoresTotal: (int) $linha->setores_total,
            setoresUrbanos: (int) $linha->setores_urbanos,
            setoresRurais: (int) $linha->setores_rurais,
            setoresSemClassificacao: (int) $linha->setores_sem_classificacao,
            homens: (int) $linha->homens,
            mulheres: (int) $linha->mulheres,
            sexoNaoInformado: (int) $linha->sexo_nao_informado,
        );
    }
}
