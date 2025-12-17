<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class TenantProductSeeder extends Seeder
{
    /**
     * Seed productos de prueba para el tenant
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop Dell Inspiron',
                'price' => 899.99,
                'description' => 'Laptop de alto rendimiento para trabajo y entretenimiento',
            ],
            [
                'name' => 'Mouse Logitech MX Master 3',
                'price' => 99.99,
                'description' => 'Mouse ergonómico inalámbrico de precisión',
            ],
            [
                'name' => 'Teclado Mecánico RGB',
                'price' => 149.99,
                'description' => 'Teclado mecánico con iluminación RGB personalizable',
            ],
            [
                'name' => 'Monitor LG 27 pulgadas 4K',
                'price' => 449.99,
                'description' => 'Monitor 4K UHD con tecnología IPS',
            ],
            [
                'name' => 'Webcam HD 1080p',
                'price' => 79.99,
                'description' => 'Webcam de alta definición para videoconferencias',
            ],
            [
                'name' => 'Auriculares Sony WH-1000XM5',
                'price' => 399.99,
                'description' => 'Auriculares con cancelación de ruido premium',
            ],
            [
                'name' => 'SSD Samsung 1TB',
                'price' => 129.99,
                'description' => 'Disco sólido de alta velocidad para almacenamiento',
            ],
            [
                'name' => 'Hub USB-C 7 en 1',
                'price' => 49.99,
                'description' => 'Adaptador multipuerto USB-C con múltiples conexiones',
            ],
            [
                'name' => 'Micrófono Blue Yeti',
                'price' => 129.99,
                'description' => 'Micrófono USB profesional para streaming y podcasts',
            ],
            [
                'name' => 'Soporte para Laptop Ajustable',
                'price' => 39.99,
                'description' => 'Soporte ergonómico ajustable en altura para laptops',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
