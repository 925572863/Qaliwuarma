<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PecosaInicialSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pecosa_inicial')->truncate();

        $productos = [
            ['cant'=>10,  'unid'=>'BOTELLA', 'descripcion'=>'ACEITE VEGETAL COMESTIBLE',                 'marca'=>'SAO',               'presentacion'=>1.000, 'lote'=>'0032026'],
            ['cant'=>49,  'unid'=>'BOLSA',   'descripcion'=>'ARROZ FORTIFICADO',                          'marca'=>'ESPIGA PIURANA',    'presentacion'=>1.000, 'lote'=>'0012026'],
            ['cant'=>25,  'unid'=>'BOLSA',   'descripcion'=>'AZUCAR RUBIA DOMESTICA',                     'marca'=>'DULCE MAS',         'presentacion'=>1.000, 'lote'=>'LT.05-2'],
            ['cant'=>970, 'unid'=>'BOLSA',   'descripcion'=>'CEREAL EXTRUIDO',                            'marca'=>'AQUIMA',            'presentacion'=>0.030, 'lote'=>'050126'],
            ['cant'=>33,  'unid'=>'BOLSA',   'descripcion'=>'CHOCOLATE PARA TAZA',                        'marca'=>'B & H',             'presentacion'=>0.090, 'lote'=>'GCRFBO1FP:31.07.2025FV:31.07.2029 / GCRFBO2FP:31.07.2025FV:31.07.2029'],
            ['cant'=>286, 'unid'=>'HOJALAT', 'descripcion'=>'CONSERVA DE PESCADO EN ACEITE VEGETAL',      'marca'=>'LATINO',            'presentacion'=>0.170, 'lote'=>'0012026'],
            ['cant'=>20,  'unid'=>'BOLSA',   'descripcion'=>'FRIJOL',                                     'marca'=>'SABORES DEL NORTE', 'presentacion'=>1.000, 'lote'=>'02'],
            ['cant'=>970, 'unid'=>'BOLSA',   'descripcion'=>'GALLETA KIWICHA',                            'marca'=>'TASTY COOKIE',      'presentacion'=>0.030, 'lote'=>'02 / 03'],
            ['cant'=>970, 'unid'=>'BOLSA',   'descripcion'=>'GALLETA QUINUA',                             'marca'=>'TASTY COOKIE',      'presentacion'=>0.030, 'lote'=>'LT.04'],
            ['cant'=>9,   'unid'=>'BOLSA',   'descripcion'=>'HARINA DE PLATANO',                          'marca'=>'VITALINKA',         'presentacion'=>1.000, 'lote'=>'LT.03'],
            ['cant'=>12,  'unid'=>'BOLSA',   'descripcion'=>'HARINA INSTANTANEA/EXTRUIDA DE MAIZ AMILAC', 'marca'=>'VITALINKA',         'presentacion'=>0.750, 'lote'=>'LT.04'],
            ['cant'=>11,  'unid'=>'BOLSA',   'descripcion'=>'HOJUELA PRECOCIDA DE AVENA CON KIWICHA',     'marca'=>'SHAQUMA FOOD',      'presentacion'=>1.000, 'lote'=>'LT.04'],
            ['cant'=>22,  'unid'=>'BOLSA',   'descripcion'=>'HOJUELA PRECOCIDA DE AVENA CON QUINUA',      'marca'=>'SHAQUMA FOOD',      'presentacion'=>1.000, 'lote'=>'LT.04'],
            ['cant'=>20,  'unid'=>'BOLSA',   'descripcion'=>'LENTEJA CALIDAD 2 - SUPERIOR',               'marca'=>'SABORES DEL NORTE', 'presentacion'=>1.000, 'lote'=>'0012026'],
            ['cant'=>324, 'unid'=>'LATA',    'descripcion'=>'PRODUCTO LACTEO RECONSTITUIDO',              'marca'=>'GLORIA',            'presentacion'=>0.390, 'lote'=>'035 / 036'],
        ];

        $now = now();
        foreach ($productos as &$p) {
            $p['volumen']    = round($p['cant'] * $p['presentacion'], 3);
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
        }

        DB::table('pecosa_inicial')->insert($productos);
    }
}
