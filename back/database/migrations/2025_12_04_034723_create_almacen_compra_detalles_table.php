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
        Schema::create('almacen_compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_compra_id')
                ->constrained('almacen_compras')
                ->onDelete('cascade');

            $table->foreignId('almacen_id')
                ->constrained('almacenes'); // asegúrate que tu tabla sea 'almacenes'

            $table->decimal('cantidad', 12, 2);
            $table->decimal('costo', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacen_compra_detalles');
    }
};
