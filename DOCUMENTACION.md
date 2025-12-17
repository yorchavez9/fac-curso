# Guía Completa: Laravel Sanctum + Laravel Tenancy (Multi-Tenant API)

## Índice

1. [Requisitos previos](#requisitos-previos)
2. [Instalación de Laravel](#instalación-de-laravel)
3. [Instalación y configuración de Laravel Sanctum](#instalación-y-configuración-de-laravel-sanctum)
4. [Instalación y configuración de Laravel Tenancy](#instalación-y-configuración-de-laravel-tenancy)
5. [Integración Sanctum + Tenancy](#integración-sanctum--tenancy)
6. [Ejemplos de uso](#ejemplos-de-uso)
7. [Troubleshooting](#troubleshooting)

---

## Requisitos previos

Antes de comenzar, asegúrate de tener instalado:

- PHP >= 8.2
- Composer
- MySQL o PostgreSQL
- Node.js y NPM (opcional para frontend)
- Postman o Thunder Client (para pruebas)

---

## Instalación de Laravel

### Paso 1: Crear un nuevo proyecto Laravel

```bash
composer create-project laravel/laravel mi-api-multitenant
cd mi-api-multitenant
```

### Paso 2: Configurar base de datos

Edita el archivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_multitenant
DB_USERNAME=root
DB_PASSWORD=
```

### Paso 3: Generar clave de aplicación

```bash
php artisan key:generate
```

### Paso 4: Ejecutar migraciones iniciales

```bash
php artisan migrate
```

### Paso 5: Iniciar servidor

```bash
php artisan serve
```

Verifica que funcione visitando: http://localhost:8000

---

## Instalación y configuración de Laravel Sanctum

### Paso 1: Instalar Laravel Sanctum

```bash
composer require laravel/sanctum
```

### Paso 2: Publicar configuración de Sanctum

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

Esto creará el archivo `config/sanctum.php` y la migración de `personal_access_tokens`.

### Paso 3: Ejecutar migraciones de Sanctum

```bash
php artisan migrate
```

Esto creará la tabla `personal_access_tokens` en tu base de datos.

### Paso 4: Agregar el trait HasApiTokens al modelo User

Edita `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

### Paso 5: Crear controlador de autenticación

```bash
php artisan make:controller AuthController
```

Edita `app/Http/Controllers/AuthController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => $user,
            'token' => $token,
            'message' => 'User registered successfully'
        ], 201);
    }

    /**
     * Iniciar sesión
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => $user,
            'token' => $token,
            'message' => 'Login successful'
        ], 200);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful'
        ], 200);
    }

    /**
     * Obtener usuario autenticado
     */
    public function user(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ], 200);
    }
}
```

### Paso 6: Configurar rutas de API

Edita `routes/api.php`:

```php
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Rutas públicas (sin autenticación)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

### Paso 7: Probar la autenticación

#### 1. Registrar un usuario

```http
POST http://localhost:8000/api/register
Content-Type: application/json

{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Respuesta:
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com"
    },
    "token": "1|abc123xyz...",
    "message": "User registered successfully"
}
```

#### 2. Iniciar sesión

```http
POST http://localhost:8000/api/login
Content-Type: application/json

{
    "email": "juan@example.com",
    "password": "password123"
}
```

#### 3. Acceder a ruta protegida

```http
GET http://localhost:8000/api/user
Authorization: Bearer 1|abc123xyz...
```

---

## Instalación y configuración de Laravel Tenancy

### ¿Qué es Multi-Tenancy?

Multi-tenancy permite que múltiples clientes (tenants) compartan la misma aplicación pero con datos completamente aislados. Cada tenant tiene su propia base de datos.

**Casos de uso:**
- SaaS (Software as a Service)
- Plataformas de e-commerce para múltiples tiendas
- Sistemas de gestión para múltiples empresas

### Paso 1: Instalar Laravel Tenancy

```bash
composer require stancl/tenancy
```

### Paso 2: Ejecutar instalador de Tenancy

```bash
php artisan tenancy:install
```

Esto creará:
- `config/tenancy.php` - Archivo de configuración
- `routes/tenant.php` - Rutas para tenants
- `app/Providers/TenancyServiceProvider.php` - Service Provider
- Migraciones para tablas `tenants` y `domains`
- Carpeta `database/migrations/tenant/` para migraciones de tenants

### Paso 3: Registrar TenancyServiceProvider

Edita `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
];
```

### Paso 4: Ejecutar migraciones centrales

```bash
php artisan migrate
```

Esto creará las tablas:
- `tenants` - Almacena información de cada tenant
- `domains` - Almacena dominios/identificadores de cada tenant

### Paso 5: Crear modelo Tenant personalizado

Es necesario crear un modelo Tenant personalizado que implemente la interfaz `TenantWithDatabase`.

Crea el archivo `app/Models/Tenant.php`:

```php
<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'data',
        ];
    }
}
```

### Paso 6: Actualizar configuración de tenancy

Edita `config/tenancy.php` (líneas 5-8):

```php
<?php

declare(strict_types=1);

use Stancl\Tenancy\Database\Models\Domain;

return [
    'tenant_model' => \App\Models\Tenant::class, // Usa tu modelo personalizado
    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,

    'domain_model' => Domain::class,
    // ...
];
```

### Paso 7: Mover migraciones a la carpeta tenant

Las migraciones que deben ejecutarse en cada base de datos de tenant (como users, products, etc.) deben estar en `database/migrations/tenant/`.

```bash
# Mover migración de users
mv database/migrations/*_create_users_table.php database/migrations/tenant/

# Mover migración de personal_access_tokens (Sanctum)
mv database/migrations/*_create_personal_access_tokens_table.php database/migrations/tenant/
```

### Paso 8: Configurar identificación de tenants

Laravel Tenancy puede identificar tenants de varias formas:

1. **Por dominio**: `empresa1.tuapp.com`, `empresa2.tuapp.com`
2. **Por header HTTP**: `X-Tenant: empresa1`
3. **Por subdirectorio**: `tuapp.com/empresa1`, `tuapp.com/empresa2`

Para APIs, usaremos **identificación por header HTTP** (la más común).

Edita `routes/tenant.php`:

```php
<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes (API Multi-Tenant)
|--------------------------------------------------------------------------
|
| Estas rutas son específicas para cada tenant. Se identifican por:
| Header: X-Tenant (tenant_id)
|
*/

Route::middleware([
    'api',
    InitializeTenancyByRequestData::class, // Identifica tenant por header X-Tenant
    PreventAccessFromCentralDomains::class,
])->prefix('api')->group(function () {

    // Rutas públicas del tenant
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Rutas protegidas del tenant
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
```

### Paso 9: Crear TenantController

```bash
php artisan make:controller TenantController --api
```

Edita `app/Http/Controllers/TenantController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Listar todos los tenants
     */
    public function index()
    {
        $tenants = Tenant::with('domains')->get();

        return response()->json([
            'status' => 'success',
            'data' => $tenants
        ], 200);
    }

    /**
     * Crear un nuevo tenant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'sometimes|string|unique:tenants,id',
            'domain' => 'required|string|unique:domains,domain',
            'data' => 'sometimes|array',
        ]);

        // Crear el tenant
        $tenant = Tenant::create([
            'id' => $validated['id'] ?? null,
            'data' => $validated['data'] ?? [],
        ]);

        // Crear el dominio asociado
        $domain = $tenant->domains()->create([
            'domain' => $validated['domain'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'tenant' => $tenant,
                'domain' => $domain
            ],
            'message' => 'Tenant created successfully'
        ], 201);
    }

    /**
     * Mostrar un tenant específico
     */
    public function show(string $id)
    {
        $tenant = Tenant::with('domains')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $tenant
        ], 200);
    }

    /**
     * Eliminar un tenant
     */
    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tenant deleted successfully'
        ], 200);
    }
}
```

### Paso 10: Configurar rutas centrales

Edita `routes/api.php`:

```php
<?php

use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API Centrales (NO Multi-Tenant)
|--------------------------------------------------------------------------
|
| Estas rutas son para la aplicación central, NO para tenants.
| Aquí se gestionan los tenants (crear, listar, eliminar).
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'API Multi-Tenant',
        'version' => '1.0.0'
    ]);
});

// Gestión de tenants (aplicación central)
Route::apiResource('tenants', TenantController::class);
```

### Paso 11: Configurar base de datos

Edita `config/tenancy.php` (línea 19-22):

```php
'central_domains' => [
    '127.0.0.1',
    'localhost',
    // Agrega aquí tus dominios centrales
],
```

---

## Integración Sanctum + Tenancy

### Cómo funciona la arquitectura

```
┌─────────────────────────────────────────────┐
│         APLICACIÓN CENTRAL                  │
│  Base de datos: api_multitenant             │
│  Tablas: tenants, domains                   │
│                                             │
│  Rutas: /api/tenants                        │
├─────────────────────────────────────────────┤
│         TENANT 1 (Empresa A)                │
│  Base de datos: tenant{uuid1}               │
│  Tablas: users, products, etc.              │
│  Auth: Sanctum (tokens propios)             │
│                                             │
│  Header: X-Tenant: {uuid1}                  │
├─────────────────────────────────────────────┤
│         TENANT 2 (Empresa B)                │
│  Base de datos: tenant{uuid2}               │
│  Tablas: users, products, etc.              │
│  Auth: Sanctum (tokens propios)             │
│                                             │
│  Header: X-Tenant: {uuid2}                  │
└─────────────────────────────────────────────┘
```

### Flujo de trabajo

1. **Crear un tenant** (aplicación central)
2. **Registrar usuarios** en el tenant específico
3. **Autenticar usuarios** con Sanctum dentro de cada tenant
4. **Acceder a recursos** usando token de Sanctum + header de tenant

### Ventajas de esta arquitectura

- ✅ **Aislamiento completo de datos** - Cada tenant tiene su BD
- ✅ **Seguridad** - Imposible acceder a datos de otro tenant
- ✅ **Escalabilidad** - Fácil agregar nuevos tenants
- ✅ **Autenticación independiente** - Cada tenant maneja sus usuarios

---

## Ejemplos de uso

### 1. Crear un tenant (Aplicación Central)

```http
POST http://localhost:8000/api/tenants
Content-Type: application/json

{
    "domain": "empresa_a",
    "data": {
        "name": "Empresa A",
        "plan": "premium",
        "email": "contacto@empresaa.com"
    }
}
```

**Respuesta:**
```json
{
    "status": "success",
    "data": {
        "tenant": {
            "id": "abc123-def456-ghi789",
            "data": {
                "name": "Empresa A",
                "plan": "premium",
                "email": "contacto@empresaa.com"
            }
        },
        "domain": {
            "id": 1,
            "domain": "empresa_a",
            "tenant_id": "abc123-def456-ghi789"
        }
    },
    "message": "Tenant created successfully"
}
```

**Importante:** Guarda el `tenant.id`, lo necesitarás para todas las peticiones.

### 2. Listar tenants

```http
GET http://localhost:8000/api/tenants
```

### 3. Registrar un usuario en el tenant

Ahora usamos el header `X-Tenant` con el ID del tenant:

```http
POST http://localhost:8000/api/register
Content-Type: application/json
X-Tenant: abc123-def456-ghi789

{
    "name": "María García",
    "email": "maria@empresaa.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Respuesta:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "María García",
        "email": "maria@empresaa.com"
    },
    "token": "2|xyz789abc...",
    "message": "User registered successfully"
}
```

### 4. Iniciar sesión en el tenant

```http
POST http://localhost:8000/api/login
Content-Type: application/json
X-Tenant: abc123-def456-ghi789

{
    "email": "maria@empresaa.com",
    "password": "password123"
}
```

### 5. Acceder a rutas protegidas del tenant

Ahora necesitas dos cosas:
1. Header `Authorization` con el token de Sanctum
2. Header `X-Tenant` con el ID del tenant

```http
GET http://localhost:8000/api/user
Authorization: Bearer 2|xyz789abc...
X-Tenant: abc123-def456-ghi789
```

**Respuesta:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "María García",
        "email": "maria@empresaa.com"
    }
}
```

### 6. Cerrar sesión

```http
POST http://localhost:8000/api/logout
Authorization: Bearer 2|xyz789abc...
X-Tenant: abc123-def456-ghi789
```

### 7. Crear otro tenant (Empresa B)

```http
POST http://localhost:8000/api/tenants
Content-Type: application/json

{
    "domain": "empresa_b",
    "data": {
        "name": "Empresa B",
        "plan": "basic",
        "email": "contacto@empresab.com"
    }
}
```

Respuesta: Obtienes un nuevo `tenant.id` (ej: `xyz789-abc123-def456`)

### 8. Registrar usuario en Empresa B

```http
POST http://localhost:8000/api/register
Content-Type: application/json
X-Tenant: xyz789-abc123-def456

{
    "name": "Carlos López",
    "email": "carlos@empresab.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Nota:** Aunque el email sea el mismo, los usuarios están en bases de datos diferentes, por lo que no hay conflicto.

---

## Verificar base de datos

### Ver base de datos central

```sql
USE api_multitenant;

-- Ver tenants creados
SELECT * FROM tenants;

-- Ver dominios
SELECT * FROM domains;
```

### Ver base de datos de un tenant

```sql
-- Reemplaza {tenant_id} con el ID de tu tenant
USE tenant{tenant_id};

-- Ver usuarios del tenant
SELECT * FROM users;

-- Ver tokens de acceso
SELECT * FROM personal_access_tokens;
```

---

## Comandos útiles de Tenancy

### Ejecutar migraciones en todos los tenants

```bash
php artisan tenants:migrate
```

### Ejecutar migración en un tenant específico

```bash
php artisan tenants:migrate --tenants=abc123-def456-ghi789
```

### Ejecutar seeders en todos los tenants

```bash
php artisan tenants:seed
```

### Listar todos los tenants

```bash
php artisan tenants:list
```

### Ejecutar comando en un tenant específico

```bash
php artisan tenants:run "db:seed --class=UserSeeder" --tenants=abc123-def456-ghi789
```

---

## Troubleshooting

### Error: "Tenant could not be identified on domain"

**Causa:** No se está enviando el header `X-Tenant` o el tenant_id es incorrecto.

**Solución:**
```http
X-Tenant: abc123-def456-ghi789
```

### Error: "Unauthenticated"

**Causa:** Falta el token de Sanctum o es inválido.

**Solución:**
```http
Authorization: Bearer 2|xyz789abc...
```

### Error: "SQLSTATE[42S02]: Base table or view not found: 'users'"

**Causa:** No se ejecutaron las migraciones en el tenant.

**Solución:**
```bash
php artisan tenants:migrate
```

### Error: "Call to a member function createToken() on null"

**Causa:** El modelo User no tiene el trait `HasApiTokens`.

**Solución:** Verifica que `app/Models/User.php` tenga:
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}
```

### Los tokens no funcionan en el tenant

**Causa:** La migración de `personal_access_tokens` está en la carpeta incorrecta.

**Solución:** Mueve la migración a `database/migrations/tenant/`:
```bash
mv database/migrations/*_create_personal_access_tokens_table.php database/migrations/tenant/
```

Luego ejecuta:
```bash
php artisan tenants:migrate
```

---

## Configuración adicional recomendada

### 1. Agregar middleware de CORS

```bash
php artisan install:api
```

Edita `bootstrap/app.php`:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### 2. Configurar rate limiting por tenant

Edita `app/Providers/RouteServiceProvider.php` o crea middleware personalizado.

### 3. Agregar validación de plan/límites

Puedes crear middleware que verifique el plan del tenant:

```php
if (tenant()->data['plan'] === 'free' && User::count() >= 5) {
    return response()->json(['error' => 'User limit reached'], 403);
}
```

---

## Recursos adicionales

- [Documentación oficial de Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Documentación oficial de Laravel Tenancy](https://tenancyforlaravel.com/docs)
- [GitHub de Laravel Tenancy](https://github.com/stancl/tenancy)

---

## Resumen de comandos

```bash
# Instalación inicial
composer create-project laravel/laravel mi-api-multitenant
cd mi-api-multitenant
php artisan key:generate
php artisan migrate

# Instalar Sanctum
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

# Instalar Tenancy
composer require stancl/tenancy
php artisan tenancy:install
php artisan migrate

# Mover migraciones a tenant
mv database/migrations/*_create_users_table.php database/migrations/tenant/
mv database/migrations/*_create_personal_access_tokens_table.php database/migrations/tenant/

# Crear controladores
php artisan make:controller AuthController
php artisan make:controller TenantController --api

# Ejecutar migraciones en tenants
php artisan tenants:migrate

# Iniciar servidor
php artisan serve
```

---

## Checklist final

- [ ] Laravel instalado y funcionando
- [ ] Sanctum instalado y configurado
- [ ] Trait `HasApiTokens` agregado al modelo User
- [ ] Tenancy instalado y configurado
- [ ] `TenancyServiceProvider` registrado en `bootstrap/providers.php`
- [ ] Migraciones de users y tokens movidas a `database/migrations/tenant/`
- [ ] `AuthController` creado con métodos register, login, logout
- [ ] `TenantController` creado para gestionar tenants
- [ ] Rutas configuradas en `routes/api.php` y `routes/tenant.php`
- [ ] Migraciones ejecutadas en aplicación central: `php artisan migrate`
- [ ] Probado crear tenant desde Postman
- [ ] Probado registrar usuario en tenant con header `X-Tenant`
- [ ] Probado login y obtener token
- [ ] Probado acceso a rutas protegidas con token + header

---

**¡Felicidades!** Ahora tienes una API multi-tenant completamente funcional con Laravel Sanctum y Laravel Tenancy.

Cada tenant tiene:
- ✅ Su propia base de datos
- ✅ Sus propios usuarios
- ✅ Su propio sistema de autenticación con Sanctum
- ✅ Aislamiento completo de datos

**Creado por:** Tu nombre
**Fecha:** 2024
**Versión:** 1.0
