<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Município no read model gerado por `censo:preparar` (tabela municipio_resumo). Somente leitura.
 *
 * @property string $cd_mun
 * @property string $nm_mun
 * @property string $nm_busca nome sem acento/apóstrofo/hífen, usado na busca
 * @property string $cd_uf
 * @property string $sigla_uf
 * @property bool $consultavel falso só para o registro '.' (lagoas do RS)
 * @property int $populacao
 * @property float $area_km2
 * @property float|null $densidade nula quando a área é zero
 * @property int $setores_total
 * @property int $setores_urbanos
 * @property int $setores_rurais
 * @property int $setores_sem_classificacao
 * @property int $homens
 * @property int $mulheres
 * @property int $sexo_nao_informado
 * @property int|null $posicao_densidade_uf posição no ranking de densidade da UF (1 = mais denso)
 * @property-read Uf $uf
 */
class Municipio extends Model
{
    protected $table = 'municipio_resumo';

    protected $primaryKey = 'cd_mun';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'consultavel' => 'boolean',
            'populacao' => 'integer',
            'area_km2' => 'float',
            'densidade' => 'float',
            'setores_total' => 'integer',
            'setores_urbanos' => 'integer',
            'setores_rurais' => 'integer',
            'setores_sem_classificacao' => 'integer',
            'homens' => 'integer',
            'mulheres' => 'integer',
            'sexo_nao_informado' => 'integer',
            'posicao_densidade_uf' => 'integer',
        ];
    }

    /** @return BelongsTo<Uf, $this> */
    public function uf(): BelongsTo
    {
        return $this->belongsTo(Uf::class, 'cd_uf', 'cd_uf');
    }

    /** @param Builder<self> $query */
    public function scopeConsultaveis(Builder $query): void
    {
        $query->where($this->qualifyColumn('consultavel'), 1);
    }

    /** @param Builder<self> $query */
    public function scopeRankingDensidade(Builder $query): void
    {
        $query->orderBy($this->qualifyColumn('posicao_densidade_uf'));
    }

    public function rotulo(): string
    {
        return "{$this->nm_mun} - {$this->sigla_uf}";
    }

    /** Route model binding só enxerga municípios consultáveis, já com a UF carregada. */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->newQuery()->consultaveis()->with('uf')->whereKey($value)->first();
    }
}
