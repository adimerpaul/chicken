<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('almacen_insumo_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('almacen_insumo_movimiento_id')
                ->constrained('almacen_insumo_movimientos')
                ->onDelete('cascade');

            $table->foreignId('almacen_id')
                ->constrained('almacenes');

            $table->foreignId('insumo_id')
                ->constrained('insumos');

            $table->decimal('cantidad', 12, 2);
            $table->decimal('costo', 12, 2)->default(0); // costo de referencia
            $table->decimal('subtotal', 14, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacen_insumo_detalles');
    }
};
