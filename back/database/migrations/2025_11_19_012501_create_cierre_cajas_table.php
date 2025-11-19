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
        Schema::create('cierre_cajas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->date('date');
            // Totales calculados por el sistema
            $table->decimal('total_ingresos', 10, 2)->default(0);
            $table->decimal('total_egresos', 10, 2)->default(0);
            $table->decimal('total_caja_inicial', 10, 2)->default(0);
            $table->integer('tickets')->default(0);

            // Monto que contó el usuario en efectivo
            $table->decimal('monto_efectivo', 10, 2)->default(0);
            // Monto que debería haber según el sistema
            $table->decimal('monto_sistema', 10, 2)->default(0);
            // Diferencia = efectivo - sistema (positivo o negativo)
            $table->decimal('diferencia', 10, 2)->default(0);

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cierre_cajas');
    }
};
