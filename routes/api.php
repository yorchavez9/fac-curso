<?php

use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API Centrales (NO Multi-Tenant)
|--------------------------------------------------------------------------
|
| Estas rutas son para la aplicación central, NO para tenants.
| Aquí se gestionan los tenants (crear, listar, actualizar, eliminar).
|
| Las rutas de los tenants están en routes/tenant.php
|
*/

// Ruta de bienvenida
Route::get('/', function () {
    return response()->json([
        'message' => 'API Multi-Tenant con Laravel Tenancy',
        'version' => '1.0.0',
        'endpoints' => [
            'central' => [
                'tenants' => '/api/tenants',
            ],
            'tenant' => [
                'info' => 'Usa header X-Tenant con el ID del tenant para acceder a sus rutas',
                'register' => '/api/register',
                'login' => '/api/login',
                'user' => '/api/user (requiere auth)',
            ]
        ]
    ]);
});

// Gestión de tenants (aplicación central)
Route::apiResource('tenants', TenantController::class);

