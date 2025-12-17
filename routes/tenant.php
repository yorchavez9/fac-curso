<?php

declare(strict_types=1);

use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes (API Multi-Tenant)
|--------------------------------------------------------------------------
|
| Estas rutas son específicas para cada tenant. Se identifican por:
| 1. Header: X-Tenant (tenant_id)
| 2. Dominio/Subdominio
|
*/

// Rutas API para tenants (identificadas por header X-Tenant o dominio)
Route::middleware([
    'api',
    InitializeTenancyByRequestData::class, // Identifica tenant por header X-Tenant
    // O usa InitializeTenancyByDomain::class para identificar por dominio
    PreventAccessFromCentralDomains::class,
])->prefix('api')->group(function () {

    // Rutas públicas del tenant (sin autenticación)
    Route::post('/register', [AuthUserController::class, 'register']);
    Route::post('/login', [AuthUserController::class, 'login']);

    // Rutas protegidas del tenant (requieren autenticación)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (\Illuminate\Http\Request $request) {
            return response()->json([
                'user' => $request->user(),
                'tenant_id' => tenant('id'),
                'tenant' => tenant()
            ]);
        });

        Route::post('/logout', [AuthUserController::class, 'logout']);
        Route::apiResource('users', AuthUserController::class);
        Route::apiResource('products', ProductController::class);
    });
});
