<?php

use App\Http\Controllers\CategoriasMenuController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RestauranteController;
use Illuminate\Support\Facades\Route;

// Rutas para Restaurantes
Route::apiResource('restaurantes', RestauranteController::class);
Route::get('restaurantes/usuario/{usuarioAdminId}', [RestauranteController::class, 'porUsuarioAdmin']);

// Rutas para Categorías Menu
Route::apiResource('categorias-menu', CategoriasMenuController::class);
Route::get('categorias-menu/restaurante/{restauranteId}', [CategoriasMenuController::class, 'porRestaurante']);

// Rutas para Productos
Route::apiResource('productos', ProductoController::class);
Route::get('productos/restaurante/{restauranteId}', [ProductoController::class, 'porRestaurante']);
Route::get('productos/categoria/{categoriaId}', [ProductoController::class, 'porCategoria']);