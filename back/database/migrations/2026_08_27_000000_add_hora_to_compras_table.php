<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->time('hora')->nullable()->after('fecha');
        });

        // Rellena las compras existentes con la hora de created_at
        $expr = DB::connection()->getDriverName() === 'pgsql'
            ? 'created_at::time'
            : 'TIME(created_at)';

        DB::table('compras')
            ->whereNull('hora')
            ->whereNotNull('created_at')
            ->update(['hora' => DB::raw($expr)]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn('hora');
        });
    }
};
