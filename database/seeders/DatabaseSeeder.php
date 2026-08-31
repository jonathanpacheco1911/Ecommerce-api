<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cliente de prueba para facilitar pruebas manuales / Postman / Swagger UI
        User::updateOrCreate(
            ['email' => 'cliente@demo.com'],
            [
                'name' => 'Cliente Demo',
                'password' => bcrypt('password123'),
                'phone' => '+50370000000',
                'address' => 'San Salvador, El Salvador',
            ]
        );

        $this->call([
            ProductSeeder::class,
        ]);
    }
}
