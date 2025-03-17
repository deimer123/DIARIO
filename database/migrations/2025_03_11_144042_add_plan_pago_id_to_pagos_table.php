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
    Schema::table('pagos', function (Blueprint $table) {
        $table->foreignId('plan_pago_id')->nullable()->constrained('plan_pagos')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('pagos', function (Blueprint $table) {
        $table->dropForeign(['plan_pago_id']);
        $table->dropColumn('plan_pago_id');
    });
}
};
