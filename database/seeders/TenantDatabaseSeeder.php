<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed de la base de datos del tenant
     *
     * Este seeder se ejecuta automáticamente cuando se crea un nuevo tenant
     * o cuando se ejecuta el comando: php artisan tenants:seed
     */
    public function run(): void
    {
        // Ejecutar seeders en orden
        $this->call([
            TenantUserSeeder::class,
            TenantProductSeeder::class,
        ]);
    }
}
