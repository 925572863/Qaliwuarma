<?php

namespace App\Http\Controllers;

use App\Models\RecetaNutricional;
use App\Models\RegistroAsistencia;
use Illuminate\Http\Request;

class PrediccionController extends Controller
{
    // ── Grados por nivel ──────────────────────────────────────────────────
    public static function gradosPorNivel(string $nivel): array
    {
        return $nivel === 'inicial'
            ? ['3 Años', '4 Años', '5 Años']
            : ['1°', '2°', '3°', '4°', '5°', '6°'];
    }

    // ── Index: dashboard de predicción ───────────────────────────────────
    public function index(Request $request)
    {
        $nivel = in_array($request->get('nivel'), ['inicial', 'primaria'])
            ? $request->get('nivel')
            : 'inicial';

        // Aulas únicas registradas
        $registrosRaw = RegistroAsistencia::where('nivel', $nivel)
            ->where('fecha', '>=', now()->subDays(60)->toDateString())
            ->orderBy('fecha')
            ->get();

        // Aulas desde alumnos — normaliza "INICIAL 3 AÑOS A" → "3 Años A"
        $aulasDeAlumnos = \App\Models\Alumno::where('nivel', $nivel)
            ->where('estado', 'activo')
            ->distinct()
            ->pluck('carrera')
            ->map(function ($c) {
                $c = mb_strtoupper(trim($c));
                // Eliminar prefijo "INICIAL " si existe
                $c = preg_replace('/^INICIAL\s+/i', '', $c);
                // Normalizar a formato título: "3 Años A"
                return ucwords(mb_strtolower($c));
            })
            ->filter()
            ->unique()->sort()->values()->toArray();

        $aulasDeRegistros = $registrosRaw->map(fn($r) => $r->grado . ' ' . $r->seccion)
            ->unique()->sort()->values()->toArray();

        $aulas = collect(array_merge($aulasDeAlumnos, $aulasDeRegistros))
            ->map(fn($a) => mb_strtoupper($a))
            ->unique()->sort()->values()
            ->map(fn($a) => ucwords(mb_strtolower($a)))
            ->values()->toArray();

        // Datos por fecha y aula
        $porFecha = $registrosRaw->groupBy(fn($r) => $r->fecha->toDateString())
            ->map(function ($grupo, $fecha) use ($aulas) {
                // Indexar registros del grupo por aula en UPPER para matching seguro
                $regPorAula = [];
                foreach ($grupo as $r) {
                    $key = mb_strtoupper(trim($r->grado) . ' ' . trim($r->seccion));
                    $regPorAula[$key] = $r;
                }
                $porAula = [];
                foreach ($aulas as $aula) {
                    $key = mb_strtoupper($aula);
                    $reg = $regPorAula[$key] ?? null;
                    $porAula[$aula] = $reg ? ['presentes' => $reg->presentes, 'raciones' => $reg->raciones] : null;
                }
                return [
                    'fecha'     => $fecha,
                    'presentes' => $grupo->sum('presentes'),
                    'raciones'  => $grupo->sum('raciones'),
                    'por_aula'  => $porAula,
                ];
            })
            ->sortByDesc('fecha')
            ->values();

        // Histórico para regresión lineal — debe ir de más antiguo a más reciente
        $historico = $porFecha->sortBy('fecha')->values()->toArray();

        // Regresión lineal
        $puntos = [];
        foreach (array_values($historico) as $i => $r) {
            $raciones = is_array($r) ? ($r['raciones'] ?? 0) : 0;
            $puntos[] = ['x' => $i, 'y' => (float) $raciones];
        }
        $regresion = $this->linearRegression($puntos);
        $m = is_array($regresion[0]) ? 0.0 : (float) $regresion[0];
        $b = is_array($regresion[1]) ? 0.0 : (float) $regresion[1];

        // Predicción próximos 7 días
        $n = count($puntos);
        $predicciones = [];
        for ($i = 1; $i <= 14 && count($predicciones) < 5; $i++) {
            $fecha    = now()->addDays($i);
            $diaSemana = $fecha->dayOfWeek;
            if ($diaSemana === 0 || $diaSemana === 6) continue;

            $pred = round(max(0, $m * ($n + $i - 1) + $b));
            $predicciones[] = [
                'fecha'            => $fecha->format('Y-m-d'),
                'fecha_legible'    => ucfirst($fecha->locale('es')->isoFormat('dddd D/MM')),
                'raciones_predichas' => $pred,
            ];
        }

        // Métricas
        $metricas = $this->calcularMetricas($puntos, $m, $b);

        // Últimos 15 registros individuales para tabla
        $registros = RegistroAsistencia::where('nivel', $nivel)
            ->orderByDesc('fecha')
            ->orderBy('grado')
            ->paginate(15)
            ->withQueryString();

        // Totales del mes actual
        $resumenMes = RegistroAsistencia::where('nivel', $nivel)
            ->whereYear('fecha', now()->year)
            ->whereMonth('fecha', now()->month)
            ->selectRaw('SUM(raciones) as total_raciones, SUM(presentes) as total_presentes, COUNT(DISTINCT fecha) as dias_registrados')
            ->first();

        // Lista de alumnos — solo los que pertenecen a aulas con registros de asistencia
        $aulasUpper = array_map('mb_strtoupper', $aulas);
        $listaAlumnos = \App\Models\Alumno::where('nivel', $nivel)
            ->where('estado', 'activo')
            ->when(count($aulasUpper) > 0, fn($q) =>
                $q->whereRaw('UPPER(carrera) IN (' . implode(',', array_fill(0, count($aulasUpper), '?')) . ')', $aulasUpper)
            )
            ->orderBy('carrera')
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->get();

        // Fechas con sus aulas registradas (para cruzar con alumnos)
        $fechasConAulas = $registrosRaw->groupBy(fn($r) => $r->fecha->toDateString())
            ->map(fn($grupo) => $grupo->map(fn($r) => mb_strtoupper($r->grado . ' ' . $r->seccion))->values()->toArray())
            ->toArray();
        $fechasOrdenadas = array_keys($fechasConAulas);
        sort($fechasOrdenadas);

        // Ingredientes necesarios según predicción (solo para inicial)
        $ingredientes = [];
        if ($nivel === 'inicial' && count($predicciones) > 0) {
            $nutricion = RecetaNutricional::all();
            if ($nutricion->isNotEmpty()) {
                foreach ($predicciones as $pred) {
                    $raciones = $pred['raciones_predichas'];
                    $items = $nutricion->map(fn($n) => [
                        'producto'      => $n->producto,
                        'gramos_total'  => round($n->gramos_racion * $raciones),
                        'kg_total'      => round(($n->gramos_racion * $raciones) / 1000, 2),
                        'calorias_total'=> round($n->calorias_racion * $raciones),
                    ])->values()->toArray();
                    $ingredientes[] = [
                        'fecha'   => $pred['fecha_legible'],
                        'raciones'=> $raciones,
                        'items'   => $items,
                    ];
                }
            }
        }

        // Análisis IA automático — caché se invalida cuando hay nuevo registro
        $recetaIA   = session("receta_ia_{$nivel}", '');
        $analisisIA = null;
        if (count($historico) >= 2 && count($predicciones) > 0) {
            $ultimoRegistro = RegistroAsistencia::where('nivel', $nivel)->max('updated_at');
            $hashActual = md5($ultimoRegistro . count($historico) . $recetaIA);

            // Buscar análisis guardado en BD
            $guardado = \Illuminate\Support\Facades\DB::table('ia_analisis')
                ->where('nivel', $nivel)
                ->where('ultimo_registro', $hashActual)
                ->first();

            if ($guardado) {
                $analisisIA = $guardado->analisis;
            } else {
            $analisisIA = (function () use ($historico, $predicciones, $metricas, $m, $nivel, $ingredientes, $recetaIA) {
                try {
                    $n          = count($historico);
                    $promedio   = round(array_sum(array_column($historico, 'raciones')) / $n);
                    $tendencia  = $m > 0.5 ? 'creciente' : ($m < -0.5 ? 'decreciente' : 'estable');
                    $nivelTexto = $nivel === 'inicial' ? 'nivel inicial' : 'nivel primaria';
                    // Buscar el lunes más próximo con datos reales o usar predicción
                    $proximoLunes = collect($historico)
                        ->filter(fn($r) => \Carbon\Carbon::parse($r['fecha'])->dayOfWeek === 1)
                        ->sortByDesc('fecha')
                        ->first();

                    if ($proximoLunes) {
                        // Usar asistencia REAL del último lunes registrado
                        $fechaLunes   = \Carbon\Carbon::parse($proximoLunes['fecha'])->locale('es')->isoFormat('dddd D/MM');
                        $racionesReal = $proximoLunes['raciones'];
                        $lista = "- {$fechaLunes}: {$racionesReal} raciones (DATO REAL REGISTRADO)";
                        $racionesParaReceta = $racionesReal;
                    } else {
                        // Sin lunes real, usar predicción
                        $soloLunes  = collect($predicciones)->filter(fn($p) => \Carbon\Carbon::parse($p['fecha'])->dayOfWeek === 1)->first();
                        $lista      = $soloLunes ? "- {$soloLunes['fecha_legible']}: {$soloLunes['raciones_predichas']} raciones (predicción)" : 'Sin datos disponibles';
                        $racionesParaReceta = $soloLunes['raciones_predichas'] ?? $promedio;
                    }

                    // Receta escrita por el usuario
                    $seccionReceta = '';
                    if (!empty($recetaIA)) {
                        $seccionReceta = "\nRECETA DEL DÍA (por ración):\n{$recetaIA}\n\nCalcula EXACTAMENTE para {$racionesParaReceta} raciones:\n- Cantidad total de cada ingrediente: gramos por ración × {$racionesParaReceta} raciones (convierte a kg)\n- Calorías totales: calorías por ración × {$racionesParaReceta}\nUSA SOLO {$racionesParaReceta} COMO NÚMERO DE RACIONES, no inventes otro número.";
                    } else {
                        $seccionReceta = "\n(No se ha registrado receta. Omite la sección de alimentos y calorías.)";
                    }

                    $prompt = <<<PROMPT
Eres un asistente del Comité de Alimentación Escolar (CAE) del programa Qali Warma en Perú. Habla de forma simple y práctica, como si le explicaras a la cocinera o responsable del comedor escolar.

DATOS DEL MODELO ({$nivelTexto}):
- Días históricos: {$n} · Promedio: {$promedio} raciones/día · Tendencia: {$tendencia}
- Error promedio: {$metricas['mae']} raciones ({$metricas['mape']}%) · Precisión R²: {$metricas['r2']}

PREDICCIONES PRÓXIMOS DÍAS:
{$lista}
{$seccionReceta}

Responde exactamente con estos 5 títulos. Máximo 3 oraciones por sección. Texto plano, sin markdown, sin asteriscos:

1. ¿Qué tan confiable es el modelo?
2. ¿Cuántas raciones preparar por día?
3. ¿Hay días con más o menos alumnos?
4. ¿Qué alimentos preparar y cuántas calorías tiene cada día?
5. Recomendación final para el CAE
PROMPT;

                    $response = \Illuminate\Support\Facades\Http::timeout(15)->withHeaders([
                        'Authorization' => 'Bearer ' . config('services.groq.key'),
                        'Content-Type'  => 'application/json',
                    ])->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model'       => 'llama-3.1-8b-instant',
                        'messages'    => [['role' => 'user', 'content' => $prompt]],
                        'temperature' => 0.3,
                        'max_tokens'  => 700,
                    ]);

                    if ($response->successful()) {
                        return trim($response->json('choices.0.message.content', ''));
                    }
                } catch (\Exception $e) {}
                return null;
            })();

            // Guardar en BD si se generó correctamente
            if ($analisisIA) {
                \Illuminate\Support\Facades\DB::table('ia_analisis')->updateOrInsert(
                    ['nivel' => $nivel],
                    [
                        'analisis'        => $analisisIA,
                        'receta'          => $recetaIA,
                        'dias_historico'  => count($historico),
                        'ultimo_registro' => $hashActual,
                        'updated_at'      => now(),
                        'created_at'      => now(),
                    ]
                );
            }
            } // cierra else
        }

        return view('prediccion.index', compact(
            'historico', 'predicciones', 'metricas', 'nivel', 'registros', 'resumenMes', 'm', 'b',
            'ingredientes', 'aulas', 'porFecha', 'listaAlumnos', 'fechasConAulas', 'fechasOrdenadas', 'analisisIA'
        ));
    }

    // ── Análisis IA de predicciones ──────────────────────────────────────
    public function analizarIA(Request $request)
    {
        $nivel = in_array($request->get('nivel'), ['inicial', 'primaria']) ? $request->get('nivel') : 'inicial';

        $historico = RegistroAsistencia::where('nivel', $nivel)
            ->where('fecha', '>=', now()->subDays(60)->toDateString())
            ->orderBy('fecha')
            ->get()
            ->groupBy(fn($r) => $r->fecha->toDateString())
            ->map(fn($g) => ['fecha' => $g->first()->fecha->toDateString(), 'raciones' => $g->sum('raciones')])
            ->values()->toArray();

        $puntos = array_map(fn($r, $i) => ['x' => $i, 'y' => (float)$r['raciones']], $historico, array_keys($historico));
        [$m, $b] = $this->linearRegression($puntos);
        $n = count($puntos);
        $predicciones = [];
        for ($i = 1; $i <= 14 && count($predicciones) < 5; $i++) {
            $fecha = now()->addDays($i);
            if (in_array($fecha->dayOfWeek, [0, 6])) continue;
            $predicciones[] = [
                'fecha'   => $fecha->locale('es')->isoFormat('dddd D/MM'),
                'raciones'=> round(max(0, $m * ($n + $i - 1) + $b)),
            ];
        }

        if (empty($predicciones)) {
            return response()->json(['error' => 'No hay predicciones disponibles.']);
        }

        $metricas  = $this->calcularMetricas($puntos, $m, $b);
        $lista     = collect($predicciones)->map(fn($p) => "- {$p['fecha']}: {$p['raciones']} raciones")->join("\n");
        $nivelTexto = $nivel === 'inicial' ? 'nivel inicial' : 'nivel primaria';
        $tendencia = $m > 0.5 ? 'creciente' : ($m < -0.5 ? 'decreciente' : 'estable');
        $promedio  = $n > 0 ? round(array_sum(array_column($historico, 'raciones')) / $n) : 0;

        $prompt = <<<PROMPT
Eres un asistente del Comité de Alimentación Escolar (CAE) del programa Qali Warma en Perú. Tu tarea es explicar de forma simple y práctica los resultados del modelo de predicción a personas sin conocimientos técnicos.

DATOS DEL MODELO ({$nivelTexto}):
- Días de datos históricos: {$n}
- Promedio de raciones por día: {$promedio}
- Tendencia: {$tendencia} (pendiente m = {$m})
- MAE (error promedio): {$metricas['mae']} raciones
- MAPE (error porcentual): {$metricas['mape']}%
- R² (precisión del modelo): {$metricas['r2']} (1.0 = perfecto)

PREDICCIONES PRÓXIMOS DÍAS:
{$lista}

Responde en 4 partes claramente separadas con estos títulos exactos:
1. ¿Qué tan confiable es el modelo?
2. ¿Cuántas raciones preparar?
3. ¿Hay días con más o menos asistencia?
4. Recomendación para el CAE

Usa lenguaje simple, sin tecnicismos. Máximo 2 oraciones por parte. Solo texto plano, sin markdown.
PROMPT;

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.groq.key'),
                'Content-Type'  => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => 'llama-3.1-8b-instant',
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.3,
                'max_tokens'  => 500,
            ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'Error al consultar la IA: ' . $response->status()]);
            }

            $texto = $response->json('choices.0.message.content', '');
            return response()->json(['analisis' => trim($texto)]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error de conexión con la IA.']);
        }
    }

    // ── Secciones disponibles para un nivel+grado ────────────────────────
    public function seccionesGrado(Request $request)
    {
        $nivel = $request->get('nivel');
        $grado = $request->get('grado');
        $aulaUpper = mb_strtoupper(trim($grado)) . ' ';

        $secciones = \App\Models\Alumno::where('nivel', $nivel)
            ->where('estado', 'activo')
            ->whereRaw("UPPER(carrera) LIKE ?", [$aulaUpper . '%'])
            ->distinct()
            ->pluck('carrera')
            ->map(function ($c) {
                $partes = explode(' ', trim($c));
                return strtoupper(end($partes));
            })
            ->filter(fn($s) => preg_match('/^[A-Z]$/', $s))
            ->sort()->values();

        return response()->json(['secciones' => $secciones]);
    }

    // ── Total matriculados por aula (para autocompletar formulario) ───────
    public function alumnosAula(Request $request)
    {
        $nivel   = $request->get('nivel');
        $grado   = $request->get('grado');
        $seccion = $request->get('seccion');

        $aulaEsperada = mb_strtoupper(trim($grado) . ' ' . trim($seccion));

        $alumnos = \App\Models\Alumno::where('nivel', $nivel)
            ->where('estado', 'activo')
            ->whereRaw('UPPER(carrera) = ?', [$aulaEsperada])
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']);

        return response()->json([
            'total'   => $alumnos->count(),
            'alumnos' => $alumnos->map(fn($a) => [
                'id'     => $a->id,
                'nombre' => $a->apellido_paterno . ' ' . $a->apellido_materno . ', ' . $a->nombre,
            ])->values(),
        ]);
    }

    // ── Create: formulario de registro ───────────────────────────────────
    public function create()
    {
        return view('prediccion.create');
    }

    // ── Store ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha'         => 'required|date|before_or_equal:today',
            'nivel'         => 'required|in:inicial,primaria',
            'grado'         => 'required|string|max:30',
            'seccion'       => 'required|string|max:5',
            'total_alumnos' => 'required|integer|min:1|max:500',
            'presentes'     => 'required|integer|min:0|max:500',
            'raciones'      => 'required|integer|min:0|max:500',
            'observaciones' => 'nullable|string|max:500',
            'detalle_json'        => 'nullable|string',
        ]);

        if ($validated['presentes'] > $validated['total_alumnos']) {
            return back()->withErrors(['presentes' => 'Los presentes no pueden superar el total de alumnos.'])->withInput();
        }

        $fecha   = \Carbon\Carbon::parse($validated['fecha'])->toDateString();
        $nivel   = $validated['nivel'];
        $grado   = $validated['grado'];
        $seccion = $validated['seccion'];

        $existe = \Illuminate\Support\Facades\DB::table('registros_asistencia')
            ->where('fecha', $fecha)->where('nivel', $nivel)
            ->where('grado', $grado)->where('seccion', $seccion)
            ->first();

        $datos = [
            'total_alumnos' => $validated['total_alumnos'],
            'presentes'     => $validated['presentes'],
            'raciones'      => $validated['raciones'],
            'observaciones' => $validated['observaciones'] ?? null,
            'updated_at'    => now(),
        ];

        if ($existe) {
            \Illuminate\Support\Facades\DB::table('registros_asistencia')
                ->where('id', $existe->id)->update($datos);
            $registroId = $existe->id;
        } else {
            $registroId = \Illuminate\Support\Facades\DB::table('registros_asistencia')->insertGetId(
                array_merge($datos, [
                    'fecha' => $fecha, 'nivel' => $nivel, 'grado' => $grado,
                    'seccion' => $seccion, 'user_id' => auth()->id(), 'created_at' => now(),
                ])
            );
        }

        // Guardar detalle individual si viene del formulario con checkboxes
        $detalleJson = json_decode($validated['detalle_json'] ?? '[]', true);
        if (!empty($detalleJson)) {
            \App\Models\DetalleAsistencia::where('registro_asistencia_id', $registroId)->delete();
            $detalles = array_map(fn($d) => [
                'registro_asistencia_id' => $registroId,
                'alumno_id'  => (int) $d['alumno_id'],
                'presente'   => (bool) $d['presente'],
                'created_at' => now(),
                'updated_at' => now(),
            ], array_filter($detalleJson, fn($d) => !empty($d['alumno_id'])));
            \App\Models\DetalleAsistencia::insert($detalles);
        }

        return redirect()->route('prediccion.index', ['nivel' => $nivel])
            ->with('success', 'Registro de asistencia guardado correctamente.');
    }

    // ── Detalle de alumnos por aula y fecha ───────────────────────────────
    public function detalleAula(Request $request)
    {
        $fecha   = $request->get('fecha');
        $nivel   = $request->get('nivel');
        $grado   = $request->get('grado');
        $seccion = $request->get('seccion');

        $registro = RegistroAsistencia::where('fecha', $fecha)
            ->where('nivel', $nivel)->where('grado', $grado)->where('seccion', $seccion)
            ->first();

        if (!$registro) {
            return response()->json(['error' => 'Sin registro para esta fecha.']);
        }

        $detalle = \App\Models\DetalleAsistencia::where('registro_asistencia_id', $registro->id)
            ->with('alumno')
            ->get()
            ->map(fn($d) => [
                'nombre'   => $d->alumno->apellido_paterno . ' ' . $d->alumno->apellido_materno . ', ' . $d->alumno->nombre,
                'presente' => (bool) $d->presente,
            ])
            ->sortBy('nombre')->values();

        return response()->json([
            'grado'    => $grado,
            'seccion'  => $seccion,
            'fecha'    => \Carbon\Carbon::parse($fecha)->format('d/m/Y'),
            'presentes'=> $registro->presentes,
            'total'    => $registro->total_alumnos,
            'detalle'  => $detalle,
        ]);
    }

    // ── Descontar ingredientes calculados por la IA del stock PECOSA ─────
    public function descontarStock(Request $request)
    {
        $nivel = in_array($request->get('nivel'), ['inicial', 'primaria']) ? $request->get('nivel') : 'inicial';

        // Obtener el análisis IA guardado en BD
        $guardado = \Illuminate\Support\Facades\DB::table('ia_analisis')->where('nivel', $nivel)->first();
        if (!$guardado || empty($guardado->analisis)) {
            return redirect()->route('prediccion.index', ['nivel' => $nivel])
                ->with('error', 'No hay análisis IA disponible para descontar.');
        }

        $texto = $guardado->analisis;

        // Extraer líneas con cantidades: "26.13 kg de arroz" o "500 g de azúcar"
        // Patrón: número (decimal) + unidad (kg/g/l/ml/lt) + "de" + nombre del producto
        preg_match_all(
            '/(\d+[\.,]?\d*)\s*(kg|g|gr|gramos|kilos|kilo|l|lt|litros|ml)\s+(?:de\s+)?([a-záéíóúñü][a-záéíóúñü\s]{1,40})/ui',
            $texto,
            $matches,
            PREG_SET_ORDER
        );

        if (empty($matches)) {
            return redirect()->route('prediccion.index', ['nivel' => $nivel])
                ->with('error', 'No se encontraron cantidades de ingredientes en el análisis IA. Asegúrate de haber guardado una receta.');
        }

        $descontados = [];
        $noEncontrados = [];

        foreach ($matches as $m) {
            $cantidad = (float) str_replace(',', '.', $m[1]);
            $unidad   = strtolower($m[2]);
            $nombre   = trim(preg_replace('/\s+/', ' ', $m[3]));
            $nombre   = preg_replace('/[^a-záéíóúñüa-z\s]/ui', '', $nombre);
            $nombre   = trim($nombre);

            // Convertir todo a kg para descontar del stock
            if (in_array($unidad, ['g', 'gr', 'gramos'])) {
                $cantidadKg = $cantidad / 1000;
            } elseif (in_array($unidad, ['ml'])) {
                $cantidadKg = $cantidad / 1000; // aprox. litros
            } else {
                $cantidadKg = $cantidad; // ya en kg o litros
            }

            // Buscar en PECOSA por descripción (LIKE, case insensitive, primera palabra del ingrediente)
            $palabraClave = explode(' ', $nombre)[0];
            $pecosa = \App\Models\PecosaInicial::whereRaw('LOWER(descripcion) LIKE ?', ['%' . strtolower($palabraClave) . '%'])
                ->first();

            if ($pecosa) {
                $stockAnterior = (float) $pecosa->stock_actual;
                $nuevo = max(0, $stockAnterior - $cantidadKg);
                $pecosa->update(['stock_actual' => $nuevo]);

                \App\Models\StockHistorial::create([
                    'pecosa_inicial_id'    => $pecosa->id,
                    'descripcion_producto' => $pecosa->descripcion,
                    'nivel'                => $nivel,
                    'receta'               => $guardado->receta ?? null,
                    'cantidad_descontada'  => $cantidadKg,
                    'stock_anterior'       => $stockAnterior,
                    'stock_nuevo'          => $nuevo,
                    'unidad'               => 'kg',
                ]);

                $descontados[] = [
                    'producto'   => $pecosa->descripcion,
                    'ingrediente'=> $nombre,
                    'cantidad'   => number_format($cantidadKg, 3),
                    'anterior'   => number_format($stockAnterior, 2),
                    'nuevo'      => number_format($nuevo, 2),
                ];
            } else {
                $noEncontrados[] = $nombre . ' (' . number_format($cantidadKg, 3) . ' kg)';
            }
        }

        $msg = '';
        if (!empty($descontados)) {
            $lineas = array_map(fn($d) => "{$d['producto']}: {$d['anterior']} → {$d['nuevo']} kg (-{$d['cantidad']} kg)", $descontados);
            $msg .= 'Stock descontado: ' . implode(' | ', $lineas) . '. ';
        }
        if (!empty($noEncontrados)) {
            $msg .= 'No encontrados en PECOSA: ' . implode(', ', $noEncontrados) . '.';
        }
        if (empty($descontados) && empty($noEncontrados)) {
            $msg = 'No se procesaron ingredientes.';
        }

        $tipo = !empty($descontados) ? 'success' : 'error';
        return redirect()->route('prediccion.index', ['nivel' => $nivel])->with($tipo, $msg);
    }

    // ── Guardar receta para la IA ─────────────────────────────────────────
    public function guardarReceta(Request $request)
    {
        $nivel  = $request->get('nivel', 'inicial');
        $receta = trim($request->get('receta_texto', ''));
        session(["receta_ia_{$nivel}" => $receta]);
        \Illuminate\Support\Facades\Cache::forget("ia_analisis_{$nivel}_*");
        // Limpiar todos los cachés de análisis de este nivel
        \Illuminate\Support\Facades\Cache::flush();
        return redirect()->route('prediccion.index', ['nivel' => $nivel])
            ->with('success', 'Receta guardada. La IA actualizará el análisis.');
    }

    // ── Importar histórico desde Excel ───────────────────────────────────
    public function importarHistorico(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'nivel'   => 'required|in:inicial,primaria',
        ]);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('archivo')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return back()->withErrors(['archivo' => 'No se pudo leer el archivo: ' . $e->getMessage()]);
        }

        // Detectar fila de encabezados
        $headerIdx = null;
        foreach ($rows as $i => $row) {
            $text = mb_strtolower(implode(' ', array_filter(array_map('strval', $row))));
            if (str_contains($text, 'fecha') && (str_contains($text, 'grado') || str_contains($text, 'aula') || str_contains($text, 'presentes'))) {
                $headerIdx = $i;
                break;
            }
        }

        if ($headerIdx === null) {
            return back()->withErrors(['archivo' => 'No se encontró la fila de encabezados. Verifica que el Excel tenga columnas: fecha, grado, seccion, presentes, total_alumnos.']);
        }

        // Mapear columnas
        $headers = array_map(fn($h) => mb_strtolower(trim(str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], (string)($h ?? '')))), $rows[$headerIdx]);
        $col = fn($names) => collect($names)->map(fn($n) => array_search($n, $headers))->filter(fn($v) => $v !== false)->first();

        $colFecha    = $col(['fecha']);
        $colGrado    = $col(['grado', 'aula', 'grado_seccion']);
        $colSeccion  = $col(['seccion', 'seccion']);
        $colPresentes= $col(['presentes', 'asistentes', 'presentes']);
        $colTotal    = $col(['total_alumnos', 'total', 'matriculados', 'total alumnos']);
        $colRaciones = $col(['raciones', 'raciones_entregadas']);

        if ($colFecha === null || $colPresentes === null) {
            return back()->withErrors(['archivo' => 'El archivo debe tener al menos las columnas: fecha y presentes.']);
        }

        $nivel     = $request->get('nivel');
        $importados = 0;
        $errores   = [];

        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $texto = trim(implode('', array_map('strval', $row)));
            if ($texto === '') continue;

            try {
                $fechaRaw = $row[$colFecha] ?? null;
                if (is_numeric($fechaRaw)) {
                    $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$fechaRaw)->format('Y-m-d');
                } else {
                    $fecha = \Carbon\Carbon::parse((string)$fechaRaw)->toDateString();
                }

                $presentes = (int) ($row[$colPresentes] ?? 0);
                $total     = $colTotal !== null ? (int)($row[$colTotal] ?? $presentes) : $presentes;
                $raciones  = $colRaciones !== null ? (int)($row[$colRaciones] ?? $presentes) : $presentes;

                // Grado y sección
                if ($colGrado !== null && $colSeccion !== null) {
                    $grado   = trim((string)($row[$colGrado] ?? ''));
                    $seccion = strtoupper(trim((string)($row[$colSeccion] ?? 'A')));
                } elseif ($colGrado !== null) {
                    // Intentar separar "3 Años A" en grado y sección
                    $partes  = explode(' ', trim((string)($row[$colGrado] ?? '')));
                    $seccion = strtoupper(array_pop($partes));
                    $grado   = implode(' ', $partes);
                } else {
                    $grado   = '3 Años';
                    $seccion = 'A';
                }

                if (!$fecha || $presentes < 0) continue;

                $existe = \Illuminate\Support\Facades\DB::table('registros_asistencia')
                    ->where('fecha', $fecha)->where('nivel', $nivel)
                    ->where('grado', $grado)->where('seccion', $seccion)->first();

                $datos = [
                    'total_alumnos' => max($total, $presentes),
                    'presentes'     => $presentes,
                    'raciones'      => $raciones ?: $presentes,
                    'updated_at'    => now(),
                ];

                if ($existe) {
                    \Illuminate\Support\Facades\DB::table('registros_asistencia')->where('id', $existe->id)->update($datos);
                } else {
                    \Illuminate\Support\Facades\DB::table('registros_asistencia')->insert(array_merge($datos, [
                        'fecha' => $fecha, 'nivel' => $nivel, 'grado' => $grado,
                        'seccion' => $seccion, 'user_id' => auth()->id(), 'created_at' => now(),
                    ]));
                }
                $importados++;
            } catch (\Exception $e) {
                $errores[] = "Fila " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        $msg = "Se importaron {$importados} registros correctamente.";
        if ($errores) $msg .= ' Errores: ' . implode(' | ', array_slice($errores, 0, 3));

        return redirect()->route('prediccion.index', ['nivel' => $nivel])->with('success', $msg);
    }

    // ── Destroy ───────────────────────────────────────────────────────────
    public function destroy(RegistroAsistencia $registro)
    {
        $nivel = $registro->nivel;
        $registro->delete();

        return redirect()->route('prediccion.index', ['nivel' => $nivel])
            ->with('success', 'Registro eliminado.');
    }

    // ── Regresión lineal (mínimos cuadrados) ─────────────────────────────
    private function linearRegression(array $puntos): array
    {
        $n = count($puntos);
        if ($n === 0) return [0, 0];
        if ($n === 1) return [0, $puntos[0]['y']];

        $sumX = $sumY = $sumXY = $sumX2 = 0;
        foreach ($puntos as $p) {
            $sumX  += $p['x'];
            $sumY  += $p['y'];
            $sumXY += $p['x'] * $p['y'];
            $sumX2 += $p['x'] ** 2;
        }

        $denom = $n * $sumX2 - $sumX ** 2;
        if ($denom == 0) return [0, $sumY / $n];

        $m = ($n * $sumXY - $sumX * $sumY) / $denom;
        $b = ($sumY - $m * $sumX) / $n;

        return [$m, $b];
    }

    // ── Métricas de evaluación ────────────────────────────────────────────
    private function calcularMetricas(array $puntos, float $m, float $b): array
    {
        $n = count($puntos);
        if ($n < 2) {
            return ['mae' => 0, 'rmse' => 0, 'r2' => 0, 'mape' => 0, 'suficientes' => false, 'n' => $n];
        }

        $actuals   = array_column($puntos, 'y');
        $meanActual = array_sum($actuals) / $n;
        $mae = $rmse = $mape = $ssRes = $ssTot = $mapeCount = 0;

        foreach ($puntos as $i => $p) {
            $pred  = $m * $p['x'] + $b;
            $err   = abs($p['y'] - $pred);
            $mae   += $err;
            $rmse  += $err ** 2;
            $ssRes += ($p['y'] - $pred) ** 2;
            $ssTot += ($p['y'] - $meanActual) ** 2;
            if ($p['y'] > 0) {
                $mape += $err / $p['y'];
                $mapeCount++;
            }
        }

        return [
            'mae'        => round($mae / $n, 2),
            'rmse'       => round(sqrt($rmse / $n), 2),
            'r2'         => $ssTot > 0 ? round(1 - $ssRes / $ssTot, 4) : 1.0,
            'mape'       => $mapeCount > 0 ? round(($mape / $mapeCount) * 100, 2) : 0,
            'suficientes' => true,
            'n'          => $n,
        ];
    }
}
