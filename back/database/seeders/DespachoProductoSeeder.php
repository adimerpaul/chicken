<?php

namespace Database\Seeders;

use App\Models\DespachoProducto;
use Illuminate\Database\Seeder;

class DespachoProductoSeeder extends Seeder
{
    public function run(): void
    {
        // productos que llegan en la nota de despacho de A.L.G. Pollos
        $productos = [
            ['nombre' => 'POLLO BRASA LIMPIO S/C', 'unidad' => 'KGS', 'precio' => 17.5],
            ['nombre' => 'FILETE DE POLLO', 'unidad' => 'KGS', 'precio' => 30],
            ['nombre' => 'ALA DE POLLO', 'unidad' => 'KGS', 'precio' => 20],
        ];

        foreach ($productos as $p) {
            DespachoProducto::firstOrCreate(['nombre' => $p['nombre']], $p + ['activo' => true]);
        }
    }
}
