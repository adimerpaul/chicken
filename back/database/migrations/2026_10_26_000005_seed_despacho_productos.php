<?php

use Database\Seeders\DespachoProductoSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // carga los productos de ejemplo en bases que ya existen (producción)
    public function up(): void
    {
        (new DespachoProductoSeeder())->run();
    }

    public function down(): void
    {
        //
    }
};
