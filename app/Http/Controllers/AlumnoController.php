<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        $query = Alumno::query();

        if ($request->filled('seccion')) {
            $query->where('carrera', $request->seccion);
        }

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('nombre', 'like', "%{$b}%")
                  ->orWhere('apellido_paterno', 'like', "%{$b}%")
                  ->orWhere('apellido_materno', 'like', "%{$b}%")
                  ->orWhere('matricula', 'like', "%{$b}%");
            });
        }

        // Agrupar secciones por nivel y luego por carrera
        $seccionesPorNivel = Alumno::orderBy('nivel')
            ->orderBy('carrera')
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->get()
            ->groupBy(['nivel', 'carrera']);

        $alumnos   = $query->orderBy('apellido_paterno')->orderBy('nombre')->get();
        $carreras  = Alumno::distinct()->orderBy('carrera')->pluck('carrera');

        return view('alumnos.index', compact('alumnos', 'seccionesPorNivel', 'carreras'));
    }

    public function create()
    {
        return view('alumnos.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateAlumno($request);
        Alumno::create($validated);

        return redirect()->route('alumnos.index')
            ->with('success', 'Alumno registrado exitosamente.');
    }

    public function show(Alumno $alumno)
    {
        return view('alumnos.show', compact('alumno'));
    }

    public function edit(Alumno $alumno)
    {
        return view('alumnos.edit', compact('alumno'));
    }

    public function update(Request $request, Alumno $alumno)
    {
        $validated = $this->validateAlumno($request, $alumno->id);
        $alumno->update($validated);

        return redirect()->route('alumnos.show', $alumno)
            ->with('success', 'Alumno actualizado exitosamente.');
    }

    public function destroy(Alumno $alumno)
    {
        $alumno->delete();
        return redirect()->route('alumnos.index')
            ->with('success', 'Alumno eliminado exitosamente.');
    }

    public function importInicial(Request $request)
    {
        $request->validate([
            'archivos'        => 'required|array|min:1',
            'archivos.*'      => 'file|mimes:xlsx,xls,csv|max:10240',
            'seccion_default' => 'nullable|string|max:100',
        ]);

        $seccionDefault  = strtoupper(trim($request->input('seccion_default', '')));
        $seccionesVistas = [];   // secciones detectadas en todos los archivos
        $todosAlumnos    = [];   // todos los alumnos a insertar
        $erroresArchivo  = [];

        foreach ($request->file('archivos') as $archivo) {
            $resultado = $this->parsearArchivoInicial($archivo, $seccionDefault);

            if ($resultado['error']) {
                $erroresArchivo[] = $archivo->getClientOriginalName() . ': ' . $resultado['error'];
                continue;
            }

            foreach ($resultado['secciones'] as $sec) {
                $seccionesVistas[$sec] = true;
            }
            foreach ($resultado['alumnos'] as $alumno) {
                $todosAlumnos[] = $alumno;
            }
        }

        if (empty($todosAlumnos)) {
            $msg = 'No se encontraron alumnos en los archivos subidos.';
            if ($erroresArchivo) $msg .= ' ' . implode(' | ', $erroresArchivo);
            return back()->withErrors(['archivos' => $msg]);
        }

        // Eliminar todos los alumnos de inicial antes de importar
        Alumno::where('nivel', 'inicial')->delete();

        // Insertar todos los alumnos
        $imported = 0;
        $errores  = [];
        $matriculasUsadas = [];

        foreach ($todosAlumnos as $datos) {
            // Evitar duplicados de matrícula entre archivos
            $mat = $datos['matricula'];
            if (isset($matriculasUsadas[$mat])) {
                $datos['matricula'] = $mat . '-' . ($matriculasUsadas[$mat]++);
            } else {
                $matriculasUsadas[$mat] = 1;
            }

            try {
                Alumno::create($datos);
                $imported++;
            } catch (\Exception $e) {
                $errores[] = $e->getMessage();
            }
        }

        $listaSecc = array_keys($seccionesVistas);
        $resumen = collect($listaSecc)->sort()->implode(', ');
        $msg = "Se importaron {$imported} alumnos en " . count($listaSecc) . " sección(es): {$resumen}.";
        if ($errores)       $msg .= ' | ERRORES (' . count($errores) . '): ' . implode(' /// ', array_slice($errores, 0, 3));
        if ($erroresArchivo) $msg .= ' Archivos no leídos: ' . implode(', ', $erroresArchivo);

        return redirect()->route('alumnos.index')->with('success', $msg);
    }

    private function parsearArchivoInicial($archivo, string $seccionDefault): array
    {
        $result = ['secciones' => [], 'alumnos' => [], 'error' => null];

        try {
            $spreadsheet = IOFactory::load($archivo->getPathname());
        } catch (\Exception $e) {
            $result['error'] = 'No se pudo leer: ' . $e->getMessage();
            return $result;
        }

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $this->parsearHojaInicial($sheet, $seccionDefault, $result);
        }

        $result['secciones'] = array_keys($result['secciones']);
        return $result;
    }

    private function parsearHojaInicial($sheet, string $seccionDefault, array &$result): void
    {
        // Detectar sección desde el nombre de la pestaña (ej: "3 años-A", "4 años -B")
        $seccionDeHoja = '';
        $normHoja = $this->normalizeText($sheet->getTitle());
        if (preg_match('/(\d+)\s*anos?\s*[-\s]+([a-z])\b/i', $normHoja, $mh)) {
            $seccionDeHoja = $mh[1] . ' AÑOS ' . strtoupper($mh[2]);
        } elseif (preg_match('/(\d+)\s*anos?\s+([a-z])\b/i', $normHoja, $mh)) {
            $seccionDeHoja = $mh[1] . ' AÑOS ' . strtoupper($mh[2]);
        }

        try {
            $rows = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return;
        }

        // ── Encontrar fila de encabezados y sección del título ────────────────
        $headerIdx     = null;
        $seccionTitulo = $seccionDeHoja ?: $seccionDefault;

        foreach ($rows as $idx => $row) {
            $rowText = mb_strtolower(implode(' ', array_filter(array_map('strval', $row))));
            if ($rowText === '') continue;

            if ($headerIdx === null) {
                $norm = $this->normalizeText(implode(' ', array_filter(array_map('strval', $row))));
                $det  = $this->detectarSeccion($norm, $seccionTitulo);
                if ($det) $seccionTitulo = $det;
            }

            if (str_contains($rowText, 'apellido') &&
               (str_contains($rowText, 'nombres') || str_contains($rowText, 'nombre'))) {
                $headerIdx = $idx;
                break;
            }
        }

        if ($headerIdx === null) return;

        // ── Detectar columnas ─────────────────────────────────────────────────
        $normH  = array_map(fn($h) => $this->normalizeHeader((string)($h ?? '')), $rows[$headerIdx]);
        $colAP  = $this->findCol($normH, ['apellido_paterno','ap_paterno','paterno','ape_paterno']);
        $colAM  = $this->findCol($normH, ['apellido_materno','ap_materno','materno','ape_materno']);
        $colNom = $this->findCol($normH, ['nombres','nombre','primer_nombre']);
        $colFull= $this->findCol($normH, ['apellidos_y_nombres','nombre_completo','apellidos_nombres','ape_y_nom']);
        $colDni = $this->findCol($normH, ['numero_de_documento','numero_documento','num_doc','n_doc','dni','matricula','documento','doc']);
        $colCod = $this->findCol($normH, ['codigo_del_estudiante','codigo_estudiante','cod_estudiante','codigo_alumno']);
        $colFec = $this->findCol($normH, ['fecha_nacimiento','fecha_nac','f_nacimiento','nacimiento','fec_nac','fecha_de_nacimiento']);
        $colSex = $this->findCol($normH, ['sexo','genero','sex']);
        $colSec = $this->findCol($normH, ['seccion','aula','grado_seccion','clase']);

        $seccionActual = $seccionTitulo;

        // ── Leer filas de datos ───────────────────────────────────────────────
        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row     = $rows[$i];
            $rowText = mb_strtolower(implode(' ', array_filter(array_map('strval', $row))));
            if ($rowText === '') continue;

            $norm = $this->normalizeText(implode(' ', array_filter(array_map('strval', $row))));
            $det  = $this->detectarSeccion($norm, $seccionActual);
            if ($det) { $seccionActual = $det; continue; }

            if (str_contains($rowText, 'apellido') && str_contains($rowText, 'nombres')) continue;

            if ($colAP !== null) {
                $apellidoP = strtoupper(trim((string)($row[$colAP] ?? '')));
                $apellidoM = $colAM !== null ? strtoupper(trim((string)($row[$colAM] ?? ''))) : '';
                $nombres   = $colNom !== null ? strtoupper(trim((string)($row[$colNom] ?? ''))) : '';
            } elseif ($colFull !== null) {
                $full = strtoupper(trim((string)($row[$colFull] ?? '')));
                if (str_contains($full, ',')) {
                    [$apes, $noms] = explode(',', $full, 2);
                    $apParts   = explode(' ', trim($apes));
                    $apellidoP = trim($apParts[0] ?? '');
                    $apellidoM = trim($apParts[1] ?? '');
                    $nombres   = trim($noms);
                } else {
                    $words     = explode(' ', $full);
                    $apellidoP = $words[0] ?? '';
                    $apellidoM = $words[1] ?? '';
                    $nombres   = implode(' ', array_slice($words, 2));
                }
            } else {
                continue;
            }

            if (!$apellidoP || !$nombres) continue;

            $dni = '';
            if ($colDni !== null) $dni = trim((string)($row[$colDni] ?? ''));
            if ($dni === '' && $colCod !== null) $dni = trim((string)($row[$colCod] ?? ''));
            $matricula = $dni ?: 'INI-' . $sheet->getTitle() . '-' . $i;

            $seccion = $colSec !== null ? strtoupper(trim((string)($row[$colSec] ?? ''))) : '';
            if ($seccion === '') $seccion = $seccionActual;
            if ($seccion === '') continue;

            $result['secciones'][$seccion] = true;
            $result['alumnos'][] = [
                'nivel'             => 'inicial',
                'user_id'           => auth()->id(),
                'matricula'         => $matricula,
                'nombre'            => $nombres,
                'apellido_paterno'  => $apellidoP,
                'apellido_materno'  => $apellidoM,
                'genero'            => $this->parseGenero($colSex !== null ? ($row[$colSex] ?? null) : null),
                'fecha_nacimiento'  => $this->parseFecha($colFec !== null ? ($row[$colFec] ?? null) : null),
                'carrera'           => $seccion,
                'semestre'          => 1,
                'fecha_inscripcion' => now()->toDateString(),
                'estado'            => 'activo',
            ];
        }
    }

    private function detectarSeccion(string $norm, string $seccionActual): ?string
    {
        if (preg_match('/(\d+)\s*anos?/i', $norm, $m1) &&
            preg_match('/(?:seccion|aula|sala)\s*:?\s*([a-z])\b/i', $norm, $m2)) {
            return $m1[1] . ' AÑOS ' . strtoupper($m2[1]);
        }
        if (preg_match('/(\d+)\s*anos?\s+([a-z])\b/i', $norm, $m)) {
            return $m[1] . ' AÑOS ' . strtoupper($m[2]);
        }
        if (preg_match('/(?:seccion|aula|sala)\s*:?\s*([a-z])\b/i', $norm, $m) &&
            preg_match('/(\d+)/', $seccionActual, $yearM)) {
            return $yearM[1] . ' AÑOS ' . strtoupper($m[1]);
        }
        return null;
    }

    private function normalizeHeader(string $h): string
    {
        $h = mb_strtolower(trim($h));
        $h = str_replace(
            ['á','é','í','ó','ú','ü','ñ','à','è','ì','ò','ù','°','º','ª','.'],
            ['a','e','i','o','u','u','n','a','e','i','o','u','','','',''],
            $h
        );
        $h = preg_replace('/[^a-z0-9]+/', '_', $h);
        return trim($h, '_');
    }

    private function normalizeText(string $t): string
    {
        $t = mb_strtolower(trim($t));
        return str_replace(
            ['á','é','í','ó','ú','ü','ñ','à','è','ì','ò','ù'],
            ['a','e','i','o','u','u','n','a','e','i','o','u'],
            $t
        );
    }

    private function findCol(array $normalizedHeaders, array $possibleNames): ?int
    {
        // Paso 1: coincidencia exacta (más confiable)
        foreach ($normalizedHeaders as $idx => $header) {
            if ($header === '') continue;
            if (in_array($header, $possibleNames)) return $idx;
        }
        // Paso 2: coincidencia parcial, excluyendo columnas tipo "tipo_de_*"
        foreach ($normalizedHeaders as $idx => $header) {
            if ($header === '' || str_starts_with($header, 'tipo_')) continue;
            foreach ($possibleNames as $name) {
                if (str_contains($header, $name) || str_contains($name, $header)) {
                    return $idx;
                }
            }
        }
        return null;
    }

    public function importPrimaria(Request $request)
    {
        $request->validate([
            'archivos'        => 'required|array|min:1',
            'archivos.*'      => 'file|mimes:xlsx,xls,csv|max:10240',
            'seccion_default' => 'nullable|string|max:100',
        ]);

        $seccionDefault  = strtoupper(trim($request->input('seccion_default', '')));
        $seccionesVistas = [];
        $todosAlumnos    = [];
        $erroresArchivo  = [];

        foreach ($request->file('archivos') as $archivo) {
            $resultado = $this->parsearArchivoPrimaria($archivo, $seccionDefault);
            if ($resultado['error']) {
                $erroresArchivo[] = $archivo->getClientOriginalName() . ': ' . $resultado['error'];
                continue;
            }
            foreach ($resultado['secciones'] as $sec) $seccionesVistas[$sec] = true;
            foreach ($resultado['alumnos'] as $alumno) $todosAlumnos[] = $alumno;
        }

        if (empty($todosAlumnos)) {
            $msg = 'No se encontraron alumnos en los archivos subidos.';
            if ($erroresArchivo) $msg .= ' ' . implode(' | ', $erroresArchivo);
            return back()->withErrors(['archivos' => $msg]);
        }

        Alumno::where('nivel', 'primaria')->delete();

        $imported = 0;
        $errores  = [];
        $matriculasUsadas = [];

        foreach ($todosAlumnos as $datos) {
            $mat = $datos['matricula'];
            if (isset($matriculasUsadas[$mat])) {
                $datos['matricula'] = $mat . '-' . ($matriculasUsadas[$mat]++);
            } else {
                $matriculasUsadas[$mat] = 1;
            }
            try {
                Alumno::create($datos);
                $imported++;
            } catch (\Exception $e) {
                $errores[] = $e->getMessage();
            }
        }

        $listaSecc = array_keys($seccionesVistas);
        $resumen = collect($listaSecc)->sort()->implode(', ');
        $msg = "Se importaron {$imported} alumnos en " . count($listaSecc) . " sección(es): {$resumen}.";
        if ($errores)        $msg .= ' | ERRORES (' . count($errores) . '): ' . implode(' /// ', array_slice($errores, 0, 3));
        if ($erroresArchivo) $msg .= ' Archivos no leídos: ' . implode(', ', $erroresArchivo);

        return redirect()->route('alumnos.index')->with('success', $msg);
    }

    private function parsearArchivoPrimaria($archivo, string $seccionDefault): array
    {
        $result = ['secciones' => [], 'alumnos' => [], 'error' => null];

        try {
            $spreadsheet = IOFactory::load($archivo->getPathname());
        } catch (\Exception $e) {
            $result['error'] = 'No se pudo leer: ' . $e->getMessage();
            return $result;
        }

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $this->parsearHojaPrimaria($sheet, $seccionDefault, $result);
        }

        $result['secciones'] = array_keys($result['secciones']);
        return $result;
    }

    private function parsearHojaPrimaria($sheet, string $seccionDefault, array &$result): void
    {
        // Detectar sección desde el nombre de la pestaña (ej: "1° A", "2do B", "3er grado C")
        $seccionDeHoja = '';
        $normHoja = $this->normalizeText($sheet->getTitle());
        if (preg_match('/(\d+)[°º]?\s*(?:er|do|ro|to|vo)?\s*(?:grado)?\s*[-\s]*([a-z])\b/i', $normHoja, $mh)) {
            $seccionDeHoja = $mh[1] . '° ' . strtoupper($mh[2]);
        }

        try {
            $rows = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return;
        }

        $headerIdx     = null;
        $seccionTitulo = $seccionDeHoja ?: $seccionDefault;

        foreach ($rows as $idx => $row) {
            $rowText = mb_strtolower(implode(' ', array_filter(array_map('strval', $row))));
            if ($rowText === '') continue;

            if ($headerIdx === null) {
                $norm = $this->normalizeText(implode(' ', array_filter(array_map('strval', $row))));
                $det  = $this->detectarSeccionPrimaria($norm, $seccionTitulo);
                if ($det) $seccionTitulo = $det;
            }

            if (str_contains($rowText, 'apellido') &&
               (str_contains($rowText, 'nombres') || str_contains($rowText, 'nombre'))) {
                $headerIdx = $idx;
                break;
            }
        }

        if ($headerIdx === null) return;

        $normH  = array_map(fn($h) => $this->normalizeHeader((string)($h ?? '')), $rows[$headerIdx]);
        $colAP  = $this->findCol($normH, ['apellido_paterno','ap_paterno','paterno','ape_paterno']);
        $colAM  = $this->findCol($normH, ['apellido_materno','ap_materno','materno','ape_materno']);
        $colNom = $this->findCol($normH, ['nombres','nombre','primer_nombre']);
        $colFull= $this->findCol($normH, ['apellidos_y_nombres','nombre_completo','apellidos_nombres']);
        $colDni = $this->findCol($normH, ['numero_de_documento','numero_documento','num_doc','dni','matricula','documento']);
        $colCod = $this->findCol($normH, ['codigo_del_estudiante','codigo_estudiante','cod_estudiante','codigo_alumno']);
        $colFec = $this->findCol($normH, ['fecha_nacimiento','fecha_nac','f_nacimiento','nacimiento','fec_nac']);
        $colSex = $this->findCol($normH, ['sexo','genero','sex']);
        $colSec = $this->findCol($normH, ['seccion','aula','grado_seccion','clase']);

        $seccionActual = $seccionTitulo;

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row     = $rows[$i];
            $rowText = mb_strtolower(implode(' ', array_filter(array_map('strval', $row))));
            if ($rowText === '') continue;

            $norm = $this->normalizeText(implode(' ', array_filter(array_map('strval', $row))));
            $det  = $this->detectarSeccionPrimaria($norm, $seccionActual);
            if ($det) { $seccionActual = $det; continue; }

            if (str_contains($rowText, 'apellido') && str_contains($rowText, 'nombres')) continue;

            if ($colAP !== null) {
                $apellidoP = strtoupper(trim((string)($row[$colAP] ?? '')));
                $apellidoM = $colAM !== null ? strtoupper(trim((string)($row[$colAM] ?? ''))) : '';
                $nombres   = $colNom !== null ? strtoupper(trim((string)($row[$colNom] ?? ''))) : '';
            } elseif ($colFull !== null) {
                $full = strtoupper(trim((string)($row[$colFull] ?? '')));
                if (str_contains($full, ',')) {
                    [$apes, $noms] = explode(',', $full, 2);
                    $apParts   = explode(' ', trim($apes));
                    $apellidoP = trim($apParts[0] ?? '');
                    $apellidoM = trim($apParts[1] ?? '');
                    $nombres   = trim($noms);
                } else {
                    $words     = explode(' ', $full);
                    $apellidoP = $words[0] ?? '';
                    $apellidoM = $words[1] ?? '';
                    $nombres   = implode(' ', array_slice($words, 2));
                }
            } else {
                continue;
            }

            if (!$apellidoP || !$nombres) continue;

            $dni = '';
            if ($colDni !== null) $dni = trim((string)($row[$colDni] ?? ''));
            if ($dni === '' && $colCod !== null) $dni = trim((string)($row[$colCod] ?? ''));
            $matricula = $dni ?: 'PRI-' . $sheet->getTitle() . '-' . $i;

            $seccion = $colSec !== null ? strtoupper(trim((string)($row[$colSec] ?? ''))) : '';
            if ($seccion === '') $seccion = $seccionActual;
            if ($seccion === '') continue;

            // Extraer grado numérico de la sección (ej: "3° A" → 3, "2° B" → 2)
            $gradoNum = 1;
            if (preg_match('/^(\d+)/', $seccion, $gm)) {
                $gradoNum = (int) $gm[1];
            }

            $result['secciones'][$seccion] = true;
            $result['alumnos'][] = [
                'nivel'             => 'primaria',
                'user_id'           => auth()->id(),
                'matricula'         => $matricula,
                'nombre'            => $nombres,
                'apellido_paterno'  => $apellidoP,
                'apellido_materno'  => $apellidoM,
                'genero'            => $this->parseGenero($colSex !== null ? ($row[$colSex] ?? null) : null),
                'fecha_nacimiento'  => $this->parseFecha($colFec !== null ? ($row[$colFec] ?? null) : null),
                'carrera'           => $seccion,
                'semestre'          => $gradoNum,
                'fecha_inscripcion' => now()->toDateString(),
                'estado'            => 'activo',
            ];
        }
    }

    private function detectarSeccionPrimaria(string $norm, string $seccionActual): ?string
    {
        // Detecta: "1° A", "2do grado B", "3er grado seccion C"
        if (preg_match('/(\d+)[°º]?\s*(?:er|do|ro|to|vo)?\s*(?:grado)?\s*[-\s]*([a-z])\b/i', $norm, $m)) {
            return $m[1] . '° ' . strtoupper($m[2]);
        }
        if (preg_match('/(?:seccion|aula)\s*:?\s*([a-z])\b/i', $norm, $m) &&
            preg_match('/(\d+)/', $seccionActual, $gradoM)) {
            return $gradoM[1] . '° ' . strtoupper($m[1]);
        }
        return null;
    }

    public function plantillaInicial()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_alumnos_inicial.csv"',
        ];

        $callback = function () {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF"); // BOM para compatibilidad con Excel
            fputcsv($f, ['apellido_paterno', 'apellido_materno', 'nombres', 'dni', 'fecha_nacimiento', 'genero', 'seccion']);
            fputcsv($f, ['GARCIA',    'LOPEZ',   'JUAN CARLOS', '12345678', '15/03/2020', 'M', '3 AÑOS A']);
            fputcsv($f, ['MARTINEZ',  'TORRES',  'ANA LUCIA',   '87654321', '22/07/2019', 'F', '4 AÑOS B']);
            fputcsv($f, ['RODRIGUEZ', 'FLORES',  'PEDRO JOSE',  '11223344', '10/09/2018', 'M', '5 AÑOS C']);
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function plantillaPrimaria()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_alumnos_primaria.csv"',
        ];

        $callback = function () {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            fputcsv($f, ['apellido_paterno', 'apellido_materno', 'nombres', 'dni', 'fecha_nacimiento', 'genero', 'seccion']);
            fputcsv($f, ['GARCIA',    'LOPEZ',   'JUAN CARLOS', '12345678', '15/03/2015', 'M', '1° A']);
            fputcsv($f, ['MARTINEZ',  'TORRES',  'ANA LUCIA',   '87654321', '22/07/2014', 'F', '2° B']);
            fputcsv($f, ['RODRIGUEZ', 'FLORES',  'PEDRO JOSE',  '11223344', '10/09/2013', 'M', '3° C']);
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function parseGenero($val): ?string
    {
        if (!$val) return null;
        $v = strtoupper(trim((string)$val));
        if (in_array($v, ['M', 'MASCULINO', 'H', 'HOMBRE', 'VARON', 'VARÓN'])) return 'M';
        if (in_array($v, ['F', 'FEMENINO', 'MUJER'])) return 'F';
        return null;
    }

    private function parseFecha($val): ?string
    {
        if ($val === null || $val === '') return null;
        try {
            if (is_numeric($val)) {
                return ExcelDate::excelToDateTimeObject((float)$val)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse((string)$val)->toDateString();
        } catch (\Exception) {
            return null;
        }
    }

    private function validateAlumno(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'matricula'        => "required|string|max:20|unique:alumnos,matricula,{$id}",
            'nivel'            => 'required|in:inicial,primaria',
            'nombre'           => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'genero'           => 'nullable|in:M,F,Otro',
            'curp'             => "nullable|string|size:18|unique:alumnos,curp,{$id}",
            'telefono'         => 'nullable|string|max:15',
            'email'            => 'nullable|email|max:255',
            'direccion'        => 'nullable|string|max:500',
            'carrera'          => 'required|string|max:200',
            'semestre'         => 'required|integer|min:1|max:12',
            'fecha_inscripcion'=> 'required|date',
            'estado'           => 'required|in:activo,inactivo,egresado,baja',
            'observaciones'    => 'nullable|string|max:1000',
        ]);
    }
}
