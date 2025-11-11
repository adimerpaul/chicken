<?php

use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    Route::apiResource('sales', App\Http\Controllers\VentaController::class);
//    http://localhost:8000/api/sales/3/anular
    Route::post('sales/{sale}/anular', [App\Http\Controllers\VentaController::class, 'anular']);


    Route::get('reportes/ventas', [ReporteController::class, 'ventas']);      // KPIs, pagos, mesas, por_dia, por_usuario
    Route::get('reportes/insumos', [ReporteController::class, 'insumos']);    // consumo y costo de insumos

});
