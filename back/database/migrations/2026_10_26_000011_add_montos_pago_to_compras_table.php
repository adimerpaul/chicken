<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            // desglose del pago; en MIXTO se reparte el total entre efectivo y QR
            $table->double('monto_efectivo')->default(0)->after('pago');
            $table->double('monto_qr')->default(0)->after('monto_efectivo');
        });

        DB::table('compras')->where('pago', 'EFECTIVO')->update(['monto_efectivo' => DB::raw('total')]);
        DB::table('compras')->where('pago', 'QR')->update(['monto_qr' => DB::raw('total')]);
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn(['monto_efectivo', 'monto_qr']);
        });
    }
};
