<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\User;
use App\Models\Insumo;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // --- Usuario admin
        $userAdmin = User::create([
            'name'     => 'Admin User',
            'username' => 'admin',
            'role'     => 'Administrador',
            'avatar'   => 'default.png',
            'email'    => '',
            'password' => bcrypt('admin123Admin'), // hash
        ]);

        // --- Permisos básicos
        $permisos = [
            'Usuarios',
            'Insumos',
            'Productos',
            'Clientes',
            'Ventas',
            'Compras',
            'Reportes',
        ];
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }
        $userAdmin->givePermissionTo(Permission::all());

        // --- Insumos del texto (menú)
        // Notas de unidad sugerida:
        // - Pollo (presa): UND (unidad/presa)
        // - Arroz: KG
        // - Papa: KG
        // - Plátano: UND
        // - Gaseosa 2 litros: UND (botella 2L)
        // - Gaseosa 1 litro: UND (botella 1L)
        $insumos = [
            [
                'nombre'      => 'Pollo (presa)',
                'unidad'      => 'UND',
                'stock'       => 0,
                'costo'       => 0,
                'min_stock'   => 0,
                'descripcion' => 'Piezas de pollo usadas en combos (Junior, Doble, Familiar).',
                'created_at'  => now(), 'updated_at' => now(),
            ],
            [
                'nombre'      => 'Arroz',
                'unidad'      => 'KG',
                'stock'       => 0,
                'costo'       => 0,
                'min_stock'   => 0,
                'descripcion' => 'Acompañamiento por porción en combos.',
                'created_at'  => now(), 'updated_at' => now(),
            ],
            [
                'nombre'      => 'Papa',
                'unidad'      => 'KG',
                'stock'       => 0,
                'costo'       => 0,
                'min_stock'   => 0,
                'descripcion' => 'Porción de papas fritas.',
                'created_at'  => now(), 'updated_at' => now(),
            ],
            [
                'nombre'      => 'Plátano',
                'unidad'      => 'UND',
                'stock'       => 0,
                'costo'       => 0,
                'min_stock'   => 0,
                'descripcion' => 'Porción de plátano frito.',
                'created_at'  => now(), 'updated_at' => now(),
            ],
            [
                'nombre'      => 'Gaseosa 2 litros',
                'unidad'      => 'UND',
                'stock'       => 0,
                'costo'       => 0,
                'min_stock'   => 0,
                'descripcion' => 'Botella 2L.',
                'created_at'  => now(), 'updated_at' => now(),
            ],
            [
                'nombre'      => 'Gaseosa 1 litro',
                'unidad'      => 'UND',
                'stock'       => 0,
                'costo'       => 0,
                'min_stock'   => 0,
                'descripcion' => 'Botella 1L.',
                'created_at'  => now(), 'updated_at' => now(),
            ],
        ];

        // Inserción idempotente (evita duplicados al reseedear)
        foreach ($insumos as $data) {
            Insumo::firstOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }

        $ord = 1;

        $items = [
            // === Combos (Pollos) ===
            [
                'categoria'   => 'Pollos',
                'name'        => 'Junior',
                'description' => '1 presa + arroz + papa',
                'price'       => 20,
                'unit'        => 'UND',
                'image'       => null,
                'active'      => true,
                'ord'         => $ord++,
            ],
            [
                'categoria'   => 'Pollos',
                'name'        => 'Doble',
                'description' => '2 presas + arroz + papa',
                'price'       => 28,
                'unit'        => 'UND',
                'image'       => null,
                'active'      => true,
                'ord'         => $ord++,
            ],
            [
                'categoria'   => 'Pollos',
                'name'        => 'Familiar',
                'description' => '5 presas + 2 arroz + 2 papa',
                'price'       => 70,
                'unit'        => 'UND',
                'image'       => null,
                'active'      => true,
                'ord'         => $ord++,
            ],

            // === Acompañamientos ===
            [
                'categoria'   => 'Acompañamientos',
                'name'        => 'Porción de papas',
                'description' => 'Papas fritas por porción',
                'price'       => 10,
                'unit'        => 'UND',
                'image'       => null,
                'active'      => true,
                'ord'         => $ord++,
            ],
            [
                'categoria'   => 'Acompañamientos',
                'name'        => 'Porción de arroz',
                'description' => 'Arroz por porción',
                'price'       => 10,
                'unit'        => 'UND',
                'image'       => null,
                'active'      => true,
                'ord'         => $ord++,
            ],
            [
                'categoria'   => 'Acompañamientos',
                'name'        => 'Porción de plátano',
                'description' => 'Plátano frito por porción',
                'price'       => 10,
                'unit'        => 'UND',
                'image'       => null,
                'active'      => true,
                'ord'         => $ord++,
            ],

            // === Bebidas ===
            [
                'categoria'   => 'Refrescos y Bebidas',
                'name'        => 'Gaseosa 2 litros',
                'description' => 'Botella 2L',
                'price'       => 15,
                'unit'        => 'UND',
                'image'       => null,
                'active'      => true,
                'ord'         => $ord++,
            ],
            [
                'categoria'   => 'Refrescos y Bebidas',
                'name'        => 'Gaseosa 1 litro',
                'description' => 'Botella 1L',
                'price'       => 10,
                'unit'        => 'UND',
                'image'       => null,
                'active'      => true,
                'ord'         => $ord++,
            ],
        ];

        foreach ($items as $p) {
            // Usa nombre + categoría como “clave” para evitar duplicados
            Producto::updateOrCreate(
                ['name' => $p['name'], 'categoria' => $p['categoria']],
                $p
            );
        }
    }
}
