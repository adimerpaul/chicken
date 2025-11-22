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
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('venta_id')->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();

            // snapshot de datos del producto al momento de vender
            $table->string('name');
            $table->double('price')->default(0);
            $table->double('qty')->default(1);
            $table->double('subtotal')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('venta_id')->references('id')->on('ventas')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('productos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_detalles');
    }
};
