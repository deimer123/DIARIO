<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('prestamos', function (Blueprint $table) {
        $table->string('tipo_pago')->default('diario'); // Puede ser 'diario' o 'semanal'
    });
}

public function down()
{
    Schema::table('prestamos', function (Blueprint $table) {
        $table->dropColumn('tipo_pago');
    });
}
};
