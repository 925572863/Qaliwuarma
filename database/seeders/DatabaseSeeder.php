<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@qualiwuarma.com'],
            [
                'name'     => 'Administrador',
                'password' => Hash::make('admin2024'),
            ]
        );

        $this->call([
            AlumnosPrimariaSeeder::class,
            AlumnosInicialSeeder::class,
        ]);
    }
}
