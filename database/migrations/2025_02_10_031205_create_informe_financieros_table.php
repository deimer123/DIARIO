<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('informe_financieros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade'); // Relación con Cliente
            $table->decimal('monto_prestado', 15, 2);  // Total de préstamos
            $table->decimal('monto_pagado', 15, 2)->default(0); // Total pagado
            $table->decimal('ganancia', 15, 2)->default(0); // Ganancia
            $table->enum('estado', ['Pendiente', 'Pagado'])->default('Pendiente');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('informe_financieros');
    }
};
