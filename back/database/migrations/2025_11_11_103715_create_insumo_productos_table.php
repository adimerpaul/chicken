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
        Schema::create('insumo_productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('insumo_id')->index();
            $table->unsignedBigInteger('producto_id')->index();
            $table->double('cantidad')->default(1);
            $table->foreign('insumo_id')->references('id')->on('insumos')->cascadeOnDelete();
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumo_productos');
    }
};
