<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('data_hora')->useCurrent();
            $table->string('acao');
            $table->text('detalhes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
