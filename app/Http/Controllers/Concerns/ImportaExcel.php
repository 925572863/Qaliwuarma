<?php

namespace App\Http\Controllers\Concerns;

use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Utilidades compartidas para los importadores de fichas de recolección de
 * datos (Excel/CSV): normalización de encabezados tolerante a acentos y
 * variantes de nombre, y parseo robusto de fechas (texto o serial de Excel).
 */
trait ImportaExcel
{
    /**
     * Normaliza un encabezado: minúsculas, sin espacios extremos, sin acentos
     * y con guiones bajos/espacios equivalentes, para poder comparar contra
     * una lista de nombres candidatos sin depender de la ortografía exacta.
     */
    private function normalizarEncabezado(mixed $valor): string
    {
        $texto = mb_strtolower(trim((string) ($valor ?? '')));
        $texto = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', '-'],
            ['a', 'e', 'i', 'o', 'u', 'n', '_'],
            $texto
        );
        return trim(preg_replace('/\s+/', '_', $texto), '_');
    }

    /**
     * Busca en $headers (ya normalizados) la primera columna que coincida
     * con alguno de los $candidatos (también se normalizan antes de comparar).
     * Devuelve el índice de columna o false si ninguno coincide.
     */
    private function buscarColumna(array $headers, array $candidatos): int|false
    {
        foreach ($candidatos as $candidato) {
            $idx = array_search($this->normalizarEncabezado($candidato), $headers, true);
            if ($idx !== false) {
                return $idx;
            }
        }
        return false;
    }

    /**
     * Parsea una fecha proveniente de una celda de Excel/CSV, que puede venir
     * como número serial de Excel o como texto en cualquier formato que
     * Carbon entienda. Devuelve null si no se pudo interpretar.
     */
    private function parsearFechaCelda(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        try {
            if (is_numeric($valor)) {
                return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse((string) $valor)->toDateString();
        } catch (\Exception) {
            return null;
        }
    }
}
