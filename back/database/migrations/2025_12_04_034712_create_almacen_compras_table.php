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
        Schema::create('almacen_compras', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('proveedor')->nullable();
            $table->text('nota')->nullable();
            $table->decimal('total', 14, 2)->default(0);
            $table->string('estado')->default('ACTIVO'); // ACTIVO / ANULADO
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacen_compras');
    }
};
