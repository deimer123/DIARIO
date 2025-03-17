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
        $table->enum('cobrar_domingo', ['si', 'no']); // 📌 Agregar opción de cobrar domingos
    });
}

public function down()
{
    Schema::table('prestamos', function (Blueprint $table) {
        $table->dropColumn('cobrar_domingo');
    });
}
};
