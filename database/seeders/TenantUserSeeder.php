<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    /**
     * Seed usuarios de prueba para el tenant
     */
    public function run(): void
    {
        // Usuario administrador del tenant
        User::create([
            'name' => 'Admin Usuario',
            'email' => 'admin12113@tenant.com',
            'password' => Hash::make('password123'),
        ]);

        // Usuario regular del tenant
        User::create([
            'name' => 'Usuario Regular',
            'email' => 'user12313@tenant.com',
            'password' => Hash::make('password123'),
        ]);

        // Crear 5 usuarios adicionales con factory (si existe)
        if (class_exists(\Database\Factories\UserFactory::class)) {
            User::factory(5)->create();
        }
    }
}
