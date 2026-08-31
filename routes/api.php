<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de la API - E-commerce
|--------------------------------------------------------------------------
| Este archivo se registra automáticamente si el proyecto fue inicializado
| con `php artisan install:api` (Laravel 12). Todas las rutas quedan bajo
| el prefijo /api automáticamente.
*/

// -------------------- Rutas públicas --------------------

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Catálogo público de productos (lectura sin autenticación)
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);

// Webhook de Stripe (Stripe no envía un token de usuario, se valida por firma)
Route::post('stripe/webhook', [PaymentController::class, 'webhook']);

// -------------------- Rutas protegidas (Sanctum) --------------------

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Gestión de productos (crear, editar, eliminar)
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);

    // Órdenes de compra
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders', [OrderController::class, 'store']);

    // Pagos con Stripe
    Route::post('orders/{order}/pay', [PaymentController::class, 'initiate']);
    Route::get('orders/{order}/payment/status', [PaymentController::class, 'status']);
});
