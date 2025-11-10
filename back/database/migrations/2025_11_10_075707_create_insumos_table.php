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
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                 // ej: Arroz, Papa, Aceite
            $table->string('unidad')->default('UND'); // UND, KG, LT, PAQ, etc.
            $table->decimal('stock', 12, 2)->default(0); // cantidad disponible
            $table->decimal('costo', 12, 2)->default(0); // costo unitario
            $table->decimal('min_stock', 12, 2)->nullable(); // stock mínimo (alertas)
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
