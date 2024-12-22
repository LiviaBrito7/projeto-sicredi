<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimentacoesTable extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->string('cooperativa');
            $table->string('agencia');
            $table->string('conta');
            $table->string('nome');
            $table->string('documento');
            $table->string('codigo');
            $table->string('descricao');
            $table->decimal('debito', 10, 2)->nullable();
            $table->decimal('credito', 10, 2)->nullable();
            $table->timestamp('data_hora')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
}
