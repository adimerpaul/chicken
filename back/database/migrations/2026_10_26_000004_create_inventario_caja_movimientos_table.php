<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventario de refrescos por vendedor y día:
        // INICIAL = lo que contó al abrir su caja, AUMENTO = lo que repuso durante el turno.
        // Lo vendido se calcula de las ventas del usuario, no se guarda aquí.
        Schema::create('inventario_caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->date('date')->index();
            $table->unsignedBigInteger('producto_id')->index();
            $table->string('tipo', 20)->default('INICIAL'); // INICIAL | AUMENTO
            $table->decimal('cantidad', 10, 2)->default(0);
            $table->string('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_caja_movimientos');
    }
};
