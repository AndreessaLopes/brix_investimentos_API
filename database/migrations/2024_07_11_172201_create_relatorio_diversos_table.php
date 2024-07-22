<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelatorioDiversosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relatorio_diversos', function (Blueprint $table) {
            $table->double('posicao_inicial');
            $table->double('posicao_final');
            $table->double('movimentacao');
            $table->double('rentabilidade_periodo');
            $table->double('volatilidade');
            $table->double('resultado_projetado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('relatorio_diversos');
    }
}
