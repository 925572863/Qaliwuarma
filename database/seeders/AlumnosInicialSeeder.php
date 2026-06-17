<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AlumnosInicialSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AlumnosInicialTresAnosASeeder::class,
            AlumnosInicialTresAnosBSeeder::class,
            AlumnosInicialCuatroAnosASeeder::class,
            AlumnosInicialCuatroAnosBSeeder::class,
            AlumnosInicialCuatroAnosCSeeder::class,
            AlumnosInicialCuatroAnosDSeeder::class,
            AlumnosInicialCincoAnosASeeder::class,
            AlumnosInicialCincoAnosBSeeder::class,
            AlumnosInicialCincoAnosCSeeder::class,
        ]);
    }
}
