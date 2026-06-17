<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PecosaPrimariaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pecosa_primaria')->truncate();

        $now = now();

        $productos = [
            [
                'cant'         => 38,
                'unid'         => 'BOTELLA',
                'descripcion'  => 'ACEITE VEGETAL COMESTIBLE',
                'marca'        => 'SAO',
                'presentacion' => 1.000,
                'volumen'      => 38.000,
                'lote'         => '13/03/27',
            ],
            [
                'cant'         => 249,
                'unid'         => 'BOLSA',
                'descripcion'  => 'ARROZ FORTFICADO',
                'marca'        => 'ESPIGA PIURANA',
                'presentacion' => 1.000,
                'volumen'      => 249.000,
                'lote'         => '0032026',
            ],
            [
                'cant'         => 94,
                'unid'         => 'BOLSA',
                'descripcion'  => 'AZUCAR RUBIA DOMESTICA',
                'marca'        => 'DULCE MAS',
                'presentacion' => 1.000,
                'volumen'      => 94.000,
                'lote'         => '0012025',
            ],
            [
                'cant'         => 3105,
                'unid'         => 'BOLSA',
                'descripcion'  => 'CEREAL EXTRUIDO',
                'marca'        => 'AQUIMA',
                'presentacion' => 0.060,
                'volumen'      => 186.300,
                'lote'         => 'LT.08.3',
            ],
            [
                'cant'         => 138,
                'unid'         => 'BOLSA',
                'descripcion'  => 'CHOCOLATE PARA TAZA',
                'marca'        => 'B & H',
                'presentacion' => 0.090,
                'volumen'      => 12.420,
                'lote'         => '050126',
            ],
            [
                'cant'         => 1096,
                'unid'         => 'HOJALATA',
                'descripcion'  => 'CONSERVA DE PESCADO EN ACEITE VEGETAL',
                'marca'        => 'LATINO',
                'presentacion' => 0.170,
                'volumen'      => 186.320,
                'lote'         => 'GCRFBO1FP.31.07.2025FV:31.07.2029 / GCRFBO2FP.31.07.2025FV:31.07.2029',
            ],
            [
                'cant'         => 84,
                'unid'         => 'BOLSA',
                'descripcion'  => 'FRIJOL',
                'marca'        => 'SABORES DEL NORTE',
                'presentacion' => 1.000,
                'volumen'      => 84.000,
                'lote'         => '0012025',
            ],
            [
                'cant'         => 6210,
                'unid'         => 'BOLSA',
                'descripcion'  => 'GALLETA KIWICHA',
                'marca'        => 'TASTY COOKIE',
                'presentacion' => 0.030,
                'volumen'      => 186.300,
                'lote'         => '02',
            ],
            [
                'cant'         => 6210,
                'unid'         => 'BOLSA',
                'descripcion'  => 'GALLETA QUINUA',
                'marca'        => 'TASTY COOKIE',
                'presentacion' => 0.030,
                'volumen'      => 186.300,
                'lote'         => '02103',
            ],
            [
                'cant'         => 32,
                'unid'         => 'BOLSA',
                'descripcion'  => 'HARINA DE PLATANO',
                'marca'        => 'VITALINKA',
                'presentacion' => 1.000,
                'volumen'      => 32.000,
                'lote'         => 'LT03',
            ],
            [
                'cant'         => 42,
                'unid'         => 'BOLSA',
                'descripcion'  => 'HARINA INSTANTANEA EXTRUIDA DE MAIZ AMILAC',
                'marca'        => 'VITALINKA',
                'presentacion' => 0.750,
                'volumen'      => 31.500,
                'lote'         => 'LT04',
            ],
            [
                'cant'         => 41,
                'unid'         => 'BOLSA',
                'descripcion'  => 'HOJUELA PRECOCIDA DE AVENA CON KIWICHA',
                'marca'        => 'SHAQ\'UMA FOOD',
                'presentacion' => 1.000,
                'volumen'      => 41.000,
                'lote'         => 'LT04',
            ],
            [
                'cant'         => 81,
                'unid'         => 'BOLSA',
                'descripcion'  => 'HOJUELA PRECOCIDA DE AVENA CON QUINUA',
                'marca'        => 'SHAQ\'UMA FOOD',
                'presentacion' => 1.000,
                'volumen'      => 81.000,
                'lote'         => '0012025',
            ],
            [
                'cant'         => 34,
                'unid'         => 'BOLSA',
                'descripcion'  => 'LENTEJA CALIDAD 2 - SUPERIOR',
                'marca'        => 'SABORES DEL NORTE',
                'presentacion' => 1.000,
                'volumen'      => 34.000,
                'lote'         => '0012026',
            ],
            [
                'cant'         => 1274,
                'unid'         => 'LATA',
                'descripcion'  => 'PRODUCTO LACTEO RECONSTITUIDO',
                'marca'        => 'GLORIA',
                'presentacion' => 0.390,
                'volumen'      => 496.860,
                'lote'         => '0351036',
            ],
        ];

        foreach ($productos as &$p) {
            $p['stock_actual'] = $p['volumen'];
            $p['created_at']   = $now;
            $p['updated_at']   = $now;
        }

        DB::table('pecosa_primaria')->insert($productos);
    }
}

