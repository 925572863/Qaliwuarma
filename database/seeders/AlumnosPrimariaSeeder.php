<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AlumnosPrimariaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AlumnosPrimerGradoASeeder::class,
            AlumnosPrimerGradoBSeeder::class,
            AlumnosPrimerGradoCSeeder::class,
            AlumnosSegundoGradoASeeder::class,
            AlumnosSegundoGradoBSeeder::class,
            AlumnosSegundoGradoCSeeder::class,
            AlumnosTercerGradoASeeder::class,
            AlumnosTercerGradoBSeeder::class,
            AlumnosTercerGradoCSeeder::class,
            AlumnosTercerGradoDSeeder::class,
            AlumnosCuartoGradoASeeder::class,
            AlumnosCuartoGradoBSeeder::class,
            AlumnosCuartoGradoCSeeder::class,
            AlumnosQuintoGradoASeeder::class,
            AlumnosQuintoGradoBSeeder::class,
            AlumnosQuintoGradoCSeeder::class,
            AlumnosSextoGradoASeeder::class,
            AlumnosSextoGradoBSeeder::class,
            AlumnosSextoGradoCSeeder::class,
            AlumnosSextoGradoDSeeder::class,
        ]);
    }
}
