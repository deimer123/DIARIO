<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_pagos', function (Blueprint $table) {
            $table->id();
    $table->foreignId('prestamo_id')->constrained()->onDelete('cascade'); // Relación con préstamos
    $table->date('fecha'); // Fecha del pago
    $table->string('estado')->default('Pendiente'); // Estado: "Pendiente" o "Pagado"   
    $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_pagos');
    }
};

