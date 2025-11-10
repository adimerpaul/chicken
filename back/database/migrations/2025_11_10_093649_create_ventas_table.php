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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            $table->date('date')->index();
            $table->time('time')->index();

            $table->double('total')->default(0);

            // snapshot de nombre del cliente
            $table->string('name')->default('SN');

            // referenciales (opcionales)
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('client_id')->nullable()->index();

            // metadata para operación en salón/delivery
            $table->string('type', 20)->default('INGRESO');   // INGRESO | EGRESO | CAJA
            $table->string('status', 20)->default('ACTIVO');  // ACTIVO | ANULADO | ...
            $table->string('mesa', 40)->default('MESA');      // MESA | LLEVAR | DELIVERY | PEDIDOS YA
            $table->string('pago', 40)->default('EFECTIVO');  // EFECTIVO | TARJETA | ONLINE | QR
            $table->integer('numero')->default(0);            // correlativo diario
            $table->text('comment')->nullable();
            $table->integer('llamada')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
