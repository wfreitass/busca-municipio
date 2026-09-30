<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Schema do read model do Censo 2022. As tabelas cruas (uf, municipio, setor, demografia)
 * já vêm no censo.sqlite e não são criadas aqui; a carga fica no ReadModelCensoSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Índice de apoio: a agregação por município percorre os 468 mil setores.
        Schema::table('setor', function (Blueprint $table) {
            $table->index('cd_mun', 'setor_cd_mun_index');
        });

        Schema::create('uf_resumo', function (Blueprint $table) {
            $table->string('cd_uf', 2)->primary();
            $table->string('sigla', 2)->unique();
            $table->string('nm_uf');
            $table->unsignedBigInteger('populacao');
            $table->double('area_km2');
            $table->double('densidade')->nullable();
            $table->unsignedInteger('total_municipios');
        });

        Schema::create('municipio_resumo', function (Blueprint $table) {
            $table->string('cd_mun', 7)->primary();
            $table->string('nm_mun');
            $table->string('nm_busca');
            $table->string('cd_uf', 2);
            $table->string('sigla_uf', 2);
            $table->boolean('consultavel')->default(true);
            $table->unsignedBigInteger('populacao');
            $table->double('area_km2');
            $table->double('densidade')->nullable();
            $table->unsignedInteger('setores_total');
            $table->unsignedInteger('setores_urbanos');
            $table->unsignedInteger('setores_rurais');
            $table->unsignedInteger('setores_sem_classificacao');
            $table->unsignedBigInteger('homens');
            $table->unsignedBigInteger('mulheres');
            $table->unsignedBigInteger('sexo_nao_informado');
            $table->unsignedInteger('posicao_densidade_uf')->nullable();

            $table->index(['cd_uf', 'posicao_densidade_uf']);
            $table->index(['consultavel', 'nm_busca']);
        });

        // O Schema Builder não tem tabelas virtuais: FTS5 com tokenizador trigram para a busca por nome.
        DB::statement("CREATE VIRTUAL TABLE municipio_busca USING fts5(cd_mun UNINDEXED, nm_busca, tokenize='trigram')");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS municipio_busca');
        Schema::dropIfExists('municipio_resumo');
        Schema::dropIfExists('uf_resumo');
        Schema::table('setor', function (Blueprint $table) {
            $table->dropIndex('setor_cd_mun_index');
        });
    }
};
