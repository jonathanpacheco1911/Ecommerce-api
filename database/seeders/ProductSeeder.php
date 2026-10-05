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
                'image_url' => 'https://tse4.mm.bing.net/th/id/OIP.D4a8qV79KcApS-ydqnyZDAHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3',
                'is_active' => true,
            ],
            [
                'name' => 'Zapatillas Running Pro',
                'description' => 'Zapatillas ligeras con amortiguación para correr largas distancias.',
                'price' => 79.90,
                'stock' => 60,
                'sku' => 'ZAP-RUN-002',
                'image_url' => 'https://tse4.mm.bing.net/th/id/OIP.DHIm8B5YIXwHGKTU3GjPEAHaEJ?r=0&rs=1&pid=ImgDetMain&o=7&rm=3',
                'is_active' => true,
            ],
            [
                'name' => 'Mochila Urbana 20L',
                'description' => 'Mochila resistente al agua con compartimento acolchado para laptop.',
                'price' => 45.50,
                'stock' => 80,
                'sku' => 'MOC-URB-003',
                'image_url' => 'https://http2.mlstatic.com/D_NQ_NP_644845-MLU70079438986_062023-O.webp',
                'is_active' => true,
            ],
            [
                'name' => 'Audífonos Bluetooth Over-Ear',
                'description' => 'Audífonos inalámbricos con cancelación de ruido y 30 horas de batería.',
                'price' => 59.99,
                'stock' => 40,
                'sku' => 'AUD-BT-004',
                'image_url' => 'https://i5.walmartimages.cl/asr/9e15e4a4-2e72-45ec-810e-d3e0e70d225c.5953cba9616ffac9b1010f56ed76fe4b.jpeg',
                'is_active' => true,
            ],
            [
                'name' => 'Botella Térmica de Acero 1L',
                'description' => 'Mantiene bebidas frías o calientes por hasta 12 horas.',
                'price' => 15.00,
                'stock' => 200,
                'sku' => 'BOT-TER-005',
                'image_url' => 'https://bachaaparty.com/cdn/shop/files/IMG_9708_1ba3be52-f1ed-443f-bfe7-c09b030fa930.jpg?v=1691578082&width=1080',
                'is_active' => true,
            ],
            [
                'name' => 'Reloj Inteligente Fit Band',
                'description' => 'Monitorea tu ritmo cardiaco, pasos y calidad de sueño.',
                'price' => 99.00,
                'stock' => 35,
                'sku' => 'REL-FIT-006',
                'image_url' => 'https://http2.mlstatic.com/D_NQ_NP_707304-MLU73981461822_012024-O.webp',
                'is_active' => true,
            ],
            [
                'name' => 'Gorra Ajustable Classic',
                'description' => 'Gorra unisex ajustable, disponible en color negro.',
                'price' => 12.50,
                'stock' => 250,
                'sku' => 'GOR-CLA-007',
                'image_url' => 'https://cdn11.bigcommerce.com/s-ecrsdtq42/images/stencil/1280x1280/products/5423/11070/Recycled_66_Hat_TNF_BLACK_TNF_White__89256.1678376471.png?c=1',
                'is_active' => true,
            ],
            [
                'name' => 'Set de Mancuernas Ajustables 20kg',
                'description' => 'Par de mancuernas ajustables ideales para entrenamiento en casa.',
                'price' => 120.00,
                'stock' => 20,
                'sku' => 'MAN-AJU-008',
                'image_url' => 'https://resources.sears.com.mx/medios-plazavip/mkt/61e712a5ac7f6_00-portadajpg.jpg',
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
