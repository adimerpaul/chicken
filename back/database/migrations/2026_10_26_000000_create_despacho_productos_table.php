<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despacho_productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('unidad')->default('KGS');
            $table->decimal('precio', 12, 4)->default(0); // precio referencial por unidad
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despacho_productos');
    }
};
