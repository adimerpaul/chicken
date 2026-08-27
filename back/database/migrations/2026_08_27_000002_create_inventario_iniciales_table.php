<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_iniciales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('insumo_id')->index();
            $table->date('fecha')->index();
            $table->decimal('cantidad', 12, 2)->default(0);
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['insumo_id', 'fecha']);
            $table->foreign('insumo_id')->references('id')->on('insumos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_iniciales');
    }
};
