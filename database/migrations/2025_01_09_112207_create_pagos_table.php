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
    Schema::create('pagos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('prestamo_id')->constrained()->onDelete('cascade'); // Relación con el préstamo
        $table->decimal('monto', 10, 2); // Monto pagado
        $table->date('fecha_pago'); // Fecha del pago
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
