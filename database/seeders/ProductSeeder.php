<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Camiseta Deportiva Dry-Fit',
                'description' => 'Camiseta transpirable ideal para entrenamientos, disponible en talla M.',
                'price' => 19.99,
                'stock' => 150,
                'sku' => 'CAM-DRY-001',
                'image_url' => 'https://picsum.photos/seed/camiseta/400',
                'is_active' => true,
            ],
            [
                'name' => 'Zapatillas Running Pro',
                'description' => 'Zapatillas ligeras con amortiguación para correr largas distancias.',
                'price' => 79.90,
                'stock' => 60,
                'sku' => 'ZAP-RUN-002',
                'image_url' => 'https://picsum.photos/seed/zapatillas/400',
                'is_active' => true,
            ],
            [
                'name' => 'Mochila Urbana 20L',
                'description' => 'Mochila resistente al agua con compartimento acolchado para laptop.',
                'price' => 45.50,
                'stock' => 80,
                'sku' => 'MOC-URB-003',
                'image_url' => 'https://picsum.photos/seed/mochila/400',
                'is_active' => true,
            ],
            [
                'name' => 'Audífonos Bluetooth Over-Ear',
                'description' => 'Audífonos inalámbricos con cancelación de ruido y 30 horas de batería.',
                'price' => 59.99,
                'stock' => 40,
                'sku' => 'AUD-BT-004',
                'image_url' => 'https://picsum.photos/seed/audifonos/400',
                'is_active' => true,
            ],
            [
                'name' => 'Botella Térmica de Acero 1L',
                'description' => 'Mantiene bebidas frías o calientes por hasta 12 horas.',
                'price' => 15.00,
                'stock' => 200,
                'sku' => 'BOT-TER-005',
                'image_url' => 'https://picsum.photos/seed/botella/400',
                'is_active' => true,
            ],
            [
                'name' => 'Reloj Inteligente Fit Band',
                'description' => 'Monitorea tu ritmo cardiaco, pasos y calidad de sueño.',
                'price' => 99.00,
                'stock' => 35,
                'sku' => 'REL-FIT-006',
                'image_url' => 'https://picsum.photos/seed/reloj/400',
                'is_active' => true,
            ],
            [
                'name' => 'Gorra Ajustable Classic',
                'description' => 'Gorra unisex ajustable, disponible en color negro.',
                'price' => 12.50,
                'stock' => 250,
                'sku' => 'GOR-CLA-007',
                'image_url' => 'https://picsum.photos/seed/gorra/400',
                'is_active' => true,
            ],
            [
                'name' => 'Set de Mancuernas Ajustables 20kg',
                'description' => 'Par de mancuernas ajustables ideales para entrenamiento en casa.',
                'price' => 120.00,
                'stock' => 20,
                'sku' => 'MAN-AJU-008',
                'image_url' => 'https://picsum.photos/seed/mancuernas/400',
                'is_active' => true,
            ],
            [
                'name' => 'Producto Descontinuado',
                'description' => 'Producto de ejemplo marcado como inactivo (no aparece en catálogo público).',
                'price' => 9.99,
                'stock' => 0,
                'sku' => 'DISC-000',
                'image_url' => null,
                'is_active' => false,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['sku' => $product['sku']], $product);
        }
    }
}
