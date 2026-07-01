<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@qualiwuarma.com'],
            [
                'name'     => 'Administrador',
                'password' => 'password',
            ]
        );

        $this->call([
            AlumnosPrimariaSeeder::class,
            AlumnosInicialSeeder::class,
        ]);
    }
}
