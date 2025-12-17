<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Stancl\Tenancy\Database\Models\Domain;

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
            'data' => $tenants,
            'message' => 'Tenants retrieved successfully'
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
            'data' => 'sometimes|array', // Datos adicionales del tenant (nombre, plan, etc)
        ]);

        // Crear el tenant
        $tenant = Tenant::create([
            'id' => $validated['id'] ?? null, // Si no se proporciona, se generará automáticamente
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
            'data' => $tenant,
            'message' => 'Tenant retrieved successfully'
        ], 200);
    }

    /**
     * Actualizar un tenant
     */
    public function update(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'data' => 'sometimes|array',
        ]);

        $tenant->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $tenant->fresh(),
            'message' => 'Tenant updated successfully'
        ], 200);
    }

    /**
     * Eliminar un tenant
     */
    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete(); // Esto también eliminará la base de datos del tenant

        return response()->json([
            'status' => 'success',
            'message' => 'Tenant deleted successfully'
        ], 200);
    }
}
