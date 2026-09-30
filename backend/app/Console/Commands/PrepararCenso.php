<?php

namespace App\Console\Commands;

use App\Actions\PrepararBaseCenso;
use Illuminate\Console\Command;
use PDO;
use RuntimeException;

final class PrepararCenso extends Command
{
    protected $signature = 'censo:preparar {--database= : Caminho do SQLite a preparar} {--skip-validation : Ignora os totais oficiais, útil para fixtures}';

    protected $description = 'Gera o read model do Censo em uma cópia do SQLite';

    public function handle(PrepararBaseCenso $preparar): int
    {
        $database = $this->option('database') ?: database_path('censo.sqlite');
        $db = new PDO('sqlite:'.$database);

        $preparar->handle($db, ! $this->option('skip-validation'));
        $totaisStatement = $db->query('SELECT COUNT(*) ufs, SUM(populacao) populacao, SUM(area_km2) area FROM uf_resumo');
        $municipiosStatement = $db->query('SELECT COUNT(*) FROM municipio_resumo WHERE consultavel = 1');
        if ($totaisStatement === false || $municipiosStatement === false) {
            throw new RuntimeException('Não foi possível ler os totais do read model.');
        }
        $totais = $totaisStatement->fetch(PDO::FETCH_ASSOC);
        $municipios = $municipiosStatement->fetchColumn();
        if (! is_array($totais)) {
            throw new RuntimeException('Não foi possível ler os totais do read model.');
        }

        $this->info(sprintf(
            'Read model pronto: %s UFs, %s municípios, %s habitantes, %s km².',
            $totais['ufs'], $municipios, $totais['populacao'], $totais['area'],
        ));

        return self::SUCCESS;
    }
}
