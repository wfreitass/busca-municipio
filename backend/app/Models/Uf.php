<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * UF no read model criado pela migration cria_read_model_do_censo e carregado pelo ReadModelCensoSeeder (tabela uf_resumo). Somente leitura.
 *
 * @property string $cd_uf
 * @property string $sigla
 * @property string $nm_uf
 * @property int $populacao
 * @property float $area_km2
 * @property float|null $densidade soma(pop) / soma(área) — nunca média das densidades
 * @property int $total_municipios apenas municípios consultáveis
 * @property-read Collection<int, Municipio> $municipios
 */
class Uf extends Model
{
    protected $table = 'uf_resumo';

    protected $primaryKey = 'cd_uf';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'populacao' => 'integer',
            'area_km2' => 'float',
            'densidade' => 'float',
            'total_municipios' => 'integer',
        ];
    }

    /** @return HasMany<Municipio, $this> */
    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class, 'cd_uf', 'cd_uf');
    }

    public function getRouteKeyName(): string
    {
        return 'sigla';
    }

    /** Sigla na URL aceita maiúsculas ou minúsculas (`/ufs/rr`). */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->newQuery()->where('sigla', strtoupper((string) $value))->first();
    }
}
