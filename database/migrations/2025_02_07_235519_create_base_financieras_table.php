<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('base_financieras', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto_disponible', 15, 2)->default(0); // 💰 Dinero en caja
            $table->decimal('total_prestado', 15, 2)->default(0); // 💸 Monto total prestado
            $table->decimal('total_pendiente', 15, 2)->default(0); // 🔴 Deuda pendiente
            $table->decimal('ganancia', 15, 2)->default(0); // 📈 Ganancias
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('base_financieras');
    }
};
