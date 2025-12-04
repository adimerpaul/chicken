<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Insumo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AlmacenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        $insumos = [
//            [
//                'nombre'      => 'Pollo (presa)',
//                'unidad'      => 'UND',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 0,
//                'descripcion' => 'Piezas de pollo usadas en combos (Junior, Doble, Familiar).',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Arroz',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 0,
//                'descripcion' => 'Acompañamiento por porción en combos.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Papa',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 0,
//                'descripcion' => 'Porción de papas fritas.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Plátano',
//                'unidad'      => 'UND',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 0,
//                'descripcion' => 'Porción de plátano frito.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Gaseosa 2 litros',
//                'unidad'      => 'UND',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 0,
//                'descripcion' => 'Botella 2L.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Gaseosa 1 litro',
//                'unidad'      => 'UND',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 0,
//                'descripcion' => 'Botella 1L.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//        ];

        $insumos = Insumo::all()->map(function ($insumo) {
            return [
                'nombre'      => $insumo->nombre,
                'unidad'      => $insumo->unidad,
                'stock'       => 0,
                'costo'       => $insumo->costo,
                'min_stock'   => $insumo->min_stock,
                'descripcion' => $insumo->descripcion,
            ];
        })->toArray();

        // Inserción idempotente (evita duplicados al reseedear)
        foreach ($insumos as $data) {
//            Insumo::firstOrCreate(
//                ['nombre' => $data['nombre']],
//                $data
//            );
            Almacen::firstOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }
    }
}
