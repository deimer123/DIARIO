<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('movimiento_financieros', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto', 15, 2);
            $table->enum('tipo', ['entrada', 'salida', 'gasto']);
            $table->string('motivo')->nullable();
            $table->date('fecha');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimiento_financieros');
    }
};
