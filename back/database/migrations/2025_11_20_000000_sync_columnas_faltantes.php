<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sincroniza bases creadas con versiones antiguas de las migraciones:
 * agrega las columnas que ya están declaradas en create_insumos_table /
 * create_venta_detalles_table pero que faltan en instalaciones existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            if (!Schema::hasColumn('insumos', 'no_contar')) {
                $table->boolean('no_contar')->default(false)->after('descripcion');
            }
            if (!Schema::hasColumn('insumos', 'es_mesa')) {
                $table->boolean('es_mesa')->default(false)->after('no_contar');
            }
            if (!Schema::hasColumn('insumos', 'es_llevar')) {
                $table->boolean('es_llevar')->default(false)->after('es_mesa');
            }
        });

        Schema::table('venta_detalles', function (Blueprint $table) {
            if (!Schema::hasColumn('venta_detalles', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        // No revierte nada: las columnas pertenecen a las migraciones originales.
    }
};
