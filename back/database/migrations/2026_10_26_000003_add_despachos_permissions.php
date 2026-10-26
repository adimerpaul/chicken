<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Permission::firstOrCreate(['name' => 'Despachos']);       // registrar lo que llega
        Permission::firstOrCreate(['name' => 'Despachos admin']); // completar, anular, productos
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', ['Despachos', 'Despachos admin'])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
