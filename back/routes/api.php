<?php

use App\Http\Controllers\AlmacenCompraController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\AlmacenInsumoMovimientoController;
use App\Http\Controllers\CierreCajaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\VentaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CajaAjusteController;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

//login
Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);
Route::middleware('auth:sanctum')->group(callback: function () {
    Route::post('/logout', [App\Http\Controllers\UserController::class, 'logout']);
    Route::get('/me', [App\Http\Controllers\UserController::class, 'me']);

    Route::get('/users', [App\Http\Controllers\UserController::class, 'index']);
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store']);
    Route::put('/users/{user}', [App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy']);
    Route::put('/updatePassword/{user}', [App\Http\Controllers\UserController::class, 'updatePassword']);
    Route::post('/{user}/avatar', [App\Http\Controllers\UserController::class, 'updateAvatar']);

    Route::get('/permissions', [App\Http\Controllers\UserController::class, 'permissions']);
    Route::get('/users/{user}/permissions', [App\Http\Controllers\UserController::class, 'getPermissions']);
    Route::put('/users/{user}/permissions', [App\Http\Controllers\UserController::class, 'syncPermissions']);

//    insumos
    Route::get('/insumos', [App\Http\Controllers\InsumoController::class, 'index']);
    Route::post('/insumos', [App\Http\Controllers\InsumoController::class, 'store']);
    Route::get('/insumos/{id}', [App\Http\Controllers\InsumoController::class, 'show']);
    Route::put('/insumos/{id}', [App\Http\Controllers\InsumoController::class, 'update']);
    Route::delete('/insumos/{id}', [App\Http\Controllers\InsumoController::class, 'destroy']);

    Route::apiResource('compras', CompraController::class);
    Route::put('compras/{compra}/anular', [CompraController::class, 'anular']);

// nuevos reportes
    Route::post('compras/report', [CompraController::class, 'report']);
    Route::post('compras/resumen-insumos', [CompraController::class, 'resumenInsumos']);


    Route::get('/productos',          [ProductoController::class, 'index']);
    Route::post('/productos',         [ProductoController::class, 'store']);
    Route::get('/productos/{product}',[ProductoController::class, 'show']);
    Route::put('/productos/{product}',[ProductoController::class, 'update']);
    Route::delete('/productos/{product}',[ProductoController::class, 'destroy']);

    Route::get('/productos/{product}/insumos', [ProductoController::class, 'insumosIndex']);
    Route::post('/productos/{product}/insumos', [ProductoController::class, 'insumosStore']);
    Route::put('/productos/{product}/insumos/{insumoProducto}', [ProductoController::class, 'insumosUpdate']);
    Route::delete('/productos/{product}/insumos/{insumoProducto}', [ProductoController::class, 'insumosDestroy']);

    Route::apiResource('sales', App\Http\Controllers\VentaController::class);
    Route::post('sales/{sale}/anular', [App\Http\Controllers\VentaController::class, 'anular']);

    Route::get('reportes/ventas', [ReporteController::class, 'ventas']);
    Route::get('reportes/insumos', [ReporteController::class, 'insumos']);

    Route::post('cierres-caja', [CierreCajaController::class, 'store']);
    Route::get('cierres-caja/{cierreCaja}', [CierreCajaController::class, 'show']);
    Route::get('cierres-caja-ultimo', [CierreCajaController::class, 'ultimo']);

    Route::get('sales/report/by-user', [VentaController::class, 'resumenPorUsuario']);

    Route::get('/almacenes', [AlmacenController::class, 'index']);
    Route::post('/almacenes', [AlmacenController::class, 'store']);
    Route::get('/almacenes/{id}', [AlmacenController::class, 'show']);
    Route::put('/almacenes/{id}', [AlmacenController::class, 'update']);
    Route::delete('/almacenes/{id}', [AlmacenController::class, 'destroy']);

    Route::apiResource('compras-almacen', AlmacenCompraController::class);

    Route::put('compras-almacen/{compra}/anular', [AlmacenCompraController::class, 'anular']);
    Route::post('compras-almacen/report', [AlmacenCompraController::class, 'report']);

    Route::get('movimientos-almacen-insumos', [AlmacenInsumoMovimientoController::class, 'index']);
    Route::post('movimientos-almacen-insumos', [AlmacenInsumoMovimientoController::class, 'store']);
    Route::get('movimientos-almacen-insumos/{movimiento}', [AlmacenInsumoMovimientoController::class, 'show']);
    Route::put('movimientos-almacen-insumos/{movimiento}/anular', [AlmacenInsumoMovimientoController::class, 'anular']);
    Route::post('movimientos-almacen-insumos/report', [AlmacenInsumoMovimientoController::class, 'report']);

    Route::get('salesResumen', [SaleController::class, 'resumen']);
    Route::get('cierres-caja/reporte/ultimo', [CierreCajaController::class, 'reporteUltimo']);
    Route::post('gastos', [App\Http\Controllers\VentaController::class, 'storeGasto']);
    Route::get('reportes/ajuste-caja', [CajaAjusteController::class, 'index']);


});
Route::get('reportes/ajuste-caja/excel', [CajaAjusteController::class, 'excel']); // opcional
Route::get('compras-almacen/{compra}/pdf', [AlmacenCompraController::class, 'pdf']);
