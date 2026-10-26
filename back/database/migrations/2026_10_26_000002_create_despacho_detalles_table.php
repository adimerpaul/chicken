<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despacho_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('despacho_id')
                ->constrained('despachos')
                ->onDelete('cascade');
            $table->foreignId('despacho_producto_id')
                ->constrained('despacho_productos');

            // lo llena quien recibe
            $table->decimal('cantidad', 12, 2)->default(0);       // unidades / pollos
            $table->decimal('peso_recibido', 12, 2)->default(0);  // kg pesados al recibir
            $table->text('comentario')->nullable();

            // lo llena el administrador (según la nota del proveedor)
            $table->string('envase')->nullable();
            $table->decimal('peso_envase', 12, 2)->default(0);
            $table->decimal('tara', 12, 2)->default(0);
            $table->decimal('peso_bruto', 12, 4)->default(0);
            $table->decimal('peso_neto', 12, 4)->default(0);
            $table->decimal('precio', 12, 4)->default(0);
            $table->decimal('importe', 14, 2)->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despacho_detalles');
    }
};
