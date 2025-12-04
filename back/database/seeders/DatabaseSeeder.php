<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\InsumoProducto;
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
            Almacen::firstOrCreate(
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
//        1. Los insumos de papá y arroz,  por ahora colócalo 200 gramos de papá y 100 gramos de arroz, ya cuando sirvamos loedimos biencito.
//    2. Las presas son junior=1presa, duo= 2 presas  familiar = 5 presas
        $insumoProductos = [
            [
                "producto_id" => Producto::where('name', 'Junior')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Pollo (presa)')->first()->id,
                "cantidad"    => 1,
            ],
            [
                "producto_id" => Producto::where('name', 'Junior')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Arroz')->first()->id,
                "cantidad"    => 0.1,
            ],
            [
                "producto_id" => Producto::where('name', 'Junior')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Papa')->first()->id,
                "cantidad"    => 0.2,
            ],
            [
                "producto_id" => Producto::where('name', 'Doble')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Pollo (presa)')->first()->id,
                "cantidad"    => 2,
            ],
            [
                "producto_id" => Producto::where('name', 'Doble')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Arroz')->first()->id,
                "cantidad"    => 0.2,
            ],
            [
                "producto_id" => Producto::where('name', 'Doble')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Papa')->first()->id,
                "cantidad"    => 0.2,
            ],
            [
                "producto_id" => Producto::where('name', 'Familiar')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Pollo (presa)')->first()->id,
                "cantidad"    => 5,
            ],
            [
                "producto_id" => Producto::where('name', 'Familiar')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Arroz')->first()->id,
                "cantidad"    => 0.4,
            ],
            [
                "producto_id" => Producto::where('name', 'Familiar')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Papa')->first()->id,
                "cantidad"    => 0.4,
            ],
            [
                "producto_id" => Producto::where('name', 'Porción de papas')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Papa')->first()->id,
                "cantidad"    => 0.2,
            ],
            [
                "producto_id" => Producto::where('name', 'Porción de arroz')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Arroz')->first()->id,
                "cantidad"    => 0.1,
            ],
            [
                "producto_id" => Producto::where('name', 'Porción de plátano')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Plátano')->first()->id,
                "cantidad"    => 1,
            ],
            [
                "producto_id" => Producto::where('name', 'Gaseosa 2 litros')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Gaseosa 2 litros')->first()->id,
                "cantidad"    => 1,
            ],
            [
                "producto_id" => Producto::where('name', 'Gaseosa 1 litro')->first()->id,
                "insumo_id"   => Insumo::where('nombre', 'Gaseosa 1 litro')->first()->id,
                "cantidad"    => 1,
            ],
        ];

        foreach ($insumoProductos as $ip) {
            InsumoProducto::create(
                [
                    'producto_id' => $ip['producto_id'],
                    'insumo_id'   => $ip['insumo_id'],
                    'cantidad'    => $ip['cantidad'],
                ],
            );
        }
//        $almacenes = [
//            // Ingredientes y abarrotes
//            [
//                'nombre'      => 'Sal fina',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 2,
//                'descripcion' => 'Sal fina para sazonar pollos y guarniciones.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Sal gruesa',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 1,
//                'descripcion' => 'Sal gruesa para marinados y parrilla.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Aceite vegetal (bidón 20L)',
//                'unidad'      => 'LT',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 20,
//                'descripcion' => 'Aceite para freidoras, presentación en bidón grande.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Aceite vegetal (botella 900ml)',
//                'unidad'      => 'LT',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Aceite para uso menor o complementar.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Harina de trigo',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Para rebozados, empanizados u otras preparaciones.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Maicena',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 2,
//                'descripcion' => 'Espesante para salsas y preparaciones.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Azúcar',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Para refrescos, postres o preparaciones especiales.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Ajo en grano',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 2,
//                'descripcion' => 'Ajo fresco o deshidratado para aderezos.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Comino molido',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 1,
//                'descripcion' => 'Condimento típico para sazonar pollo y arroz.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Orégano seco',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 1,
//                'descripcion' => 'Hierba seca para aderezos y marinados.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Pimienta negra molida',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 1,
//                'descripcion' => 'Pimienta para sazonar pollo y guarniciones.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Sazonador completo',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 3,
//                'descripcion' => 'Sazonador comercial para marinar pollo.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Caldo de pollo en cubos',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Cubitos de caldo para caldos y aderezos.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//
//            // Salsas y aderezos
//            [
//                'nombre'      => 'Mayonesa galón',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 3,
//                'descripcion' => 'Mayonesa para servir con combos y papas.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Ketchup galón',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 3,
//                'descripcion' => 'Ketchup a granel para dispensadores.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Mostaza galón',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 2,
//                'descripcion' => 'Mostaza para acompañar las papas y pollos.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Salsa soya',
//                'unidad'      => 'LT',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 2,
//                'descripcion' => 'Salsa soya para aderezos especiales.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Vinagre',
//                'unidad'      => 'LT',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 2,
//                'descripcion' => 'Vinagre blanco para aderezos y limpieza secundaria.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//
//            // Plásticos y descartables
//            [
//                'nombre'      => 'Bolsas plásticas pequeñas',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Bolsas pequeñas para porciones y salsas.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Bolsas plásticas grandes',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Bolsas grandes para llevar combos familiares.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Envases plásticos para llevar (chicos)',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Envases desechables pequeños para porción individual.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Envases plásticos para llevar (familiares)',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 8,
//                'descripcion' => 'Envases para combos familiares y porciones grandes.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Tapas para envases plásticos',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Tapas para envases de comida para llevar.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Vasos desechables 200 ml',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Vasos pequeños para refrescos o agua.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Vasos desechables 500 ml',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Vasos grandes para bebidas grandes.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Servilletas de papel',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 15,
//                'descripcion' => 'Servilletas para mesas y pedidos para llevar.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Cubiertos desechables (tenedor/cuchillo)',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Cubiertos plásticos para pedidos para llevar.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Palillos de dientes',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Palillos de dientes para mesas o mostrador.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Film plástico (rollo)',
//                'unidad'      => 'UND',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 3,
//                'descripcion' => 'Film para envolver alimentos y contenedores.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Papel aluminio (rollo)',
//                'unidad'      => 'UND',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 3,
//                'descripcion' => 'Papel aluminio para horno, parrilla y conservación.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//
//            // Limpieza e higiene
//            [
//                'nombre'      => 'Lavavajilla líquido',
//                'unidad'      => 'LT',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Detergente líquido para lavar platos y utensilios.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Detergente para pisos',
//                'unidad'      => 'LT',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Detergente líquido para limpieza de pisos de cocina y salón.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Cloro',
//                'unidad'      => 'LT',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Cloro para desinfección de pisos, mesones y baños.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Desinfectante aromático',
//                'unidad'      => 'LT',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Desinfectante con aroma para salón y baños.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Jabón líquido para manos',
//                'unidad'      => 'LT',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Jabón para dispensadores en baños y cocina.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Esponjas para lavar platos',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Esponjas y fibras para limpieza de vajilla.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Trapos de piso',
//                'unidad'      => 'UND',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Trapos para limpieza de pisos y derrames.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Guantes de limpieza',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Guantes reutilizables para limpieza pesada.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Bolsas de basura negras',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 15,
//                'descripcion' => 'Bolsas de basura grandes para cocina y salón.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Bolsas de basura verdes',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Bolsas para clasificación (orgánico/inorgánico).',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//
//            // Otros insumos generales
//            [
//                'nombre'      => 'Guantes desechables cocina',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 10,
//                'descripcion' => 'Guantes descartables para manipulación de alimentos.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Mascarillas desechables',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Mascarillas para personal de cocina y atención.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Garrafa de gas 10kg',
//                'unidad'      => 'UND',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 2,
//                'descripcion' => 'Garrafa de gas para cocina.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Garrafa de gas 45kg',
//                'unidad'      => 'UND',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 1,
//                'descripcion' => 'Garrafa de gas industrial para cocina grande/parrilla.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Carbón vegetal',
//                'unidad'      => 'KG',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 20,
//                'descripcion' => 'Carbón para parrilla (si aplica en el local).',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Fósforos',
//                'unidad'      => 'PAQ',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 5,
//                'descripcion' => 'Cajas de fósforos para encender cocina/parrilla.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//            [
//                'nombre'      => 'Encendedores de cocina',
//                'unidad'      => 'UND',
//                'stock'       => 0,
//                'costo'       => 0,
//                'min_stock'   => 3,
//                'descripcion' => 'Encendedores largos para cocina industrial.',
//                'created_at'  => now(), 'updated_at' => now(),
//            ],
//        ];
//
//        foreach ($almacenes as $data) {
//            Almacen::firstOrCreate(
//                ['nombre' => $data['nombre']],
//                $data
//            );
//        }
    }
}
