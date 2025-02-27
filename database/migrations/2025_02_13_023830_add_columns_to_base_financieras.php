<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('base_financieras', function (Blueprint $table) {
            $table->decimal('base_inicial', 15, 2)->default(0); // 🏦 Monto con el que se empezó a prestar
            $table->decimal('total_gastos_salidas', 15, 2)->default(0); // 💸 Total de gastos y salidas
            $table->decimal('balance_ajustado', 15, 2)->default(0); // 📊 Balance ajustado
        });
    }

    public function down()
    {
        Schema::table('base_financieras', function (Blueprint $table) {
            $table->dropColumn(['base_inicial', 'total_gastos_salidas', 'balance_ajustado']);
        });
    }
};
