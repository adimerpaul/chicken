<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('numero_nota')->nullable();   // número de la nota del proveedor
            $table->string('proveedor')->nullable();
            $table->string('cobrador')->nullable();
            $table->text('comentario')->nullable();       // comentario de quien recibe
            $table->text('comentario_admin')->nullable(); // comentario del administrador
            $table->decimal('total_peso_recibido', 12, 2)->default(0);
            $table->decimal('total_peso_bruto', 12, 2)->default(0);
            $table->decimal('total_peso_neto', 12, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('estado')->default('PENDIENTE'); // PENDIENTE / COMPLETADO / ANULADO
            $table->unsignedBigInteger('user_id')->nullable()->index();  // quien registra
            $table->unsignedBigInteger('admin_id')->nullable()->index(); // quien completa
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};
