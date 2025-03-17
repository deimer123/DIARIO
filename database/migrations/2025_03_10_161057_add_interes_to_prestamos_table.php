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
        $table->decimal('interes', 5, 2)->default(20); // 📌 Interés como porcentaje (ej. 20%)
    });
}

public function down()
{
    Schema::table('prestamos', function (Blueprint $table) {
        $table->dropColumn('interes');
    });
}
};
