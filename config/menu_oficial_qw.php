<?php

// Menú escolar oficial Qali Warma (3 y 4 entrega) tal como figura en las
// actas oficiales que maneja el CAE. Gramos por ración, por nivel.
// Se usa para autocompletar la "Receta del día para la IA" con los valores
// reales en vez de que la IA los adivine.

return [

    'dias' => [
        1 => [
            [
                'nombre' => 'Avena con quinua + azúcar / Cereal extruido',
                'ingredientes' => [
                    'Azúcar rubia'                          => ['inicial' => 30, 'primaria' => 60],
                    'Hojuela de avena precocida con quinua'  => ['inicial' => 11, 'primaria' => 13],
                    'Cereal extruido'                        => ['inicial' => 5,  'primaria' => 7],
                ],
            ],
            [
                'nombre' => 'Leche (E-R) + harina plátano + azúcar / Galleta con quinua',
                'ingredientes' => [
                    'Azúcar'                                                    => ['inicial' => 5,  'primaria' => 7],
                    'Harina de plátano'                                         => ['inicial' => 6,  'primaria' => 10],
                    'Leche evaporada entera / producto lácteo reconstituido'    => ['inicial' => 65, 'primaria' => 80],
                    'Galleta con quinua'                                        => ['inicial' => 30, 'primaria' => 60],
                ],
            ],
        ],
        2 => [
            [
                'nombre' => 'Avena con quinua + azúcar / Galleta con kiwicha',
                'ingredientes' => [
                    'Azúcar'                                 => ['inicial' => 5,  'primaria' => 6],
                    'Hojuela de avena precocida con quinua'  => ['inicial' => 11, 'primaria' => 13],
                    'Galleta con kiwicha'                    => ['inicial' => 30, 'primaria' => 60],
                ],
            ],
            [
                'nombre' => 'Leche (E-R) + harina maíz + azúcar / Pallar + aceite',
                'ingredientes' => [
                    'Azúcar'                                                 => ['inicial' => 5,  'primaria' => 6],
                    'Harina maíz'                                            => ['inicial' => 9,  'primaria' => 11],
                    'Pallar'                                                 => ['inicial' => 20, 'primaria' => 25],
                    'Aceite vegetal'                                         => ['inicial' => 5,  'primaria' => 6],
                    'Leche evaporada entera / producto lácteo reconstituido' => ['inicial' => 65, 'primaria' => 80],
                ],
            ],
        ],
        3 => [
            [
                'nombre' => 'Avena con kiwicha + chocolate + azúcar / Hojuela de avena precocida con kiwicha',
                'ingredientes' => [
                    'Azúcar para taza'                              => ['inicial' => 3,  'primaria' => 4],
                    'Chocolate'                                      => ['inicial' => 5,  'primaria' => 6],
                    'Hojuela de avena precocida con kiwicha'         => ['inicial' => 11, 'primaria' => 13],
                ],
            ],
            [
                'nombre' => 'Leche (E-R) + harina maíz + azúcar / Pescado en aceite + arroz (F) + frijol + aceite',
                'ingredientes' => [
                    'Azúcar'                                 => ['inicial' => 9,  'primaria' => 10],
                    'Harina extruida de maíz'                => ['inicial' => 20, 'primaria' => 27],
                    'Arroz fortificado'                      => ['inicial' => 25, 'primaria' => 30],
                    'Frijol'                                  => ['inicial' => 25, 'primaria' => 35],
                    'Conserva de pescado en aceite vegetal'   => ['inicial' => 25, 'primaria' => 40],
                    'Aceite vegetal'                          => ['inicial' => 5,  'primaria' => 6],
                ],
            ],
        ],
        4 => [
            [
                'nombre' => 'Avena con quinua + azúcar / Pescado en aceite + arroz (F) + lenteja + aceite',
                'ingredientes' => [
                    'Azúcar'                                 => ['inicial' => 5,  'primaria' => 6],
                    'Hojuela de avena precocida con quinua'  => ['inicial' => 11, 'primaria' => 13],
                    'Arroz fortificado'                      => ['inicial' => 25, 'primaria' => 25],
                    'Conserva de pescado en aceite vegetal'   => ['inicial' => 25, 'primaria' => 40],
                    'Lenteja'                                 => ['inicial' => 20, 'primaria' => 25],
                    'Aceite vegetal'                          => ['inicial' => 5,  'primaria' => 6],
                ],
            ],
        ],
        5 => [
            [
                'nombre' => 'Leche (E-R) + hojuela de avena precocida con quinua + azúcar / Cereal extruido',
                'ingredientes' => [
                    'Azúcar'                                                 => ['inicial' => 9,  'primaria' => 7],
                    'Hojuela de avena precocida con kiwicha'                 => ['inicial' => 5,  'primaria' => 6],
                    'Leche evaporada entera / producto lácteo reconstituido' => ['inicial' => 65, 'primaria' => 100],
                    'Cereal extruido'                                       => ['inicial' => 30, 'primaria' => 55],
                ],
            ],
        ],
    ],

    // Factor de dosificación del Arroz Fortificado: gramos -> medida casera,
    // tal como figura en la ficha de dosificación de Qali Warma Piura.
    'dosificacion_arroz_fortificado' => [
        25  => '1 cuchara bocona llena',
        30  => '1 cuchara bocona llena',
        40  => '1 cuchara bocona colmada',
        45  => '1 cuchara bocona colmada',
        55  => '1 cuchara bocona colmada',
        70  => '1 cuchara bocona colmada',
        75  => '1 cuchara bocona colmada',
        85  => '2 cucharas boconas colmadas',
        90  => '2 cucharas boconas colmadas',
        110 => '2 cucharas boconas colmadas',
        115 => '2 cucharas boconas colmadas',
        135 => '3 cucharas boconas colmadas',
        140 => '3 cucharas boconas colmadas',
        160 => '3 cucharas boconas colmadas',
    ],
];
