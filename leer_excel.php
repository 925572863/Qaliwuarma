<?php
require 'vendor/autoload.php';

$path = 'C:/Users/USER/Downloads/Actualizado_Mes de abril_ESTUDIANTES DE INICIAL LCV.xlsx';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
$rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

echo "Total filas: " . count($rows) . PHP_EOL . PHP_EOL;

// Mostrar todas las filas NO vacías con su índice
echo "--- TODAS LAS FILAS CON CONTENIDO (primeras 80) ---" . PHP_EOL;
$shown = 0;
foreach ($rows as $i => $row) {
    $txt = implode(' | ', array_filter(array_map('strval', $row)));
    if ($txt !== '' && $shown < 80) {
        echo "Fila $i: $txt" . PHP_EOL;
        $shown++;
    }
}
