<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // desglose del cobro; en pago MIXTO se reparte el total entre estos montos
            $table->double('monto_efectivo')->default(0)->after('pago');
            $table->double('monto_qr')->default(0)->after('monto_efectivo');
            $table->double('monto_tarjeta')->default(0)->after('monto_qr');
        });

        // las ventas existentes van completas al monto de su tipo de pago
        DB::table('ventas')->where('pago', 'EFECTIVO')->update(['monto_efectivo' => DB::raw('total')]);
        DB::table('ventas')->where('pago', 'QR')->update(['monto_qr' => DB::raw('total')]);
        DB::table('ventas')->where('pago', 'TARJETA')->update(['monto_tarjeta' => DB::raw('total')]);
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['monto_efectivo', 'monto_qr', 'monto_tarjeta']);
        });
    }
};
