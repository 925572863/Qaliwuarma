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

        // Regresión lineal mejorada
        $puntosRaw = [];
        foreach (array_values($historico) as $i => $r) {
            $raciones = is_array($r) ? ($r['raciones'] ?? 0) : 0;
            $puntosRaw[] = ['x' => $i, 'y' => (float) $raciones];
        }
        // Filtrar outliers antes de entrenar
        $puntos = $this->filtrarOutliers($puntosRaw);

        $regresion = $this->linearRegression($puntos);
        $m = is_array($regresion[0]) ? 0.0 : (float) $regresion[0];
        $b = is_array($regresion[1]) ? 0.0 : (float) $regresion[1];

        // Predicción próximos días hábiles (combinada: regresión + MA)
        $n = count($puntos);
        $predicciones = [];
        for ($i = 0; $i <= 14 && count($predicciones) < 5; $i++) {
            $fecha    = now()->addDays($i);
            $diaSemana = $fecha->dayOfWeek;
            if ($diaSemana === 0 || $diaSemana === 6) continue;

            $pred = round($this->predecir($puntos, $n + $i, $m, $b));
            $predicciones[] = [
                'fecha'              => $fecha->format('Y-m-d'),
                'fecha_legible'      => ucfirst($fecha->locale('es')->isoFormat('dddd D/MM')),
                'raciones_predichas' => $pred,
            ];
        }

        // Métricas (sobre datos sin outliers)
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

        // Análisis IA — cargar receta de sesión antes de calcular ingredientes
        $recetaIA = session("receta_ia_{$nivel}", '');

        // Ingredientes necesarios según predicción (solo para inicial)
        $ingredientes = [];
        if ($nivel === 'inicial' && count($predicciones) > 0) {
            $nutricion = RecetaNutricional::all();

            if ($nutricion->isNotEmpty()) {
                // Fuente 1: RecetaNutricional (análisis PECOSA con calorías reales)
                foreach ($predicciones as $pred) {
                    $raciones = $pred['raciones_predichas'];
                    $items = $nutricion->map(fn($n) => [
                        'producto'       => $n->producto,
                        'gramos_racion'  => $n->gramos_racion,
                        'gramos_total'   => round($n->gramos_racion * $raciones),
                        'kg_total'       => round(($n->gramos_racion * $raciones) / 1000, 3),
                        'calorias_racion'=> $n->calorias_racion,
                        'calorias_total' => round($n->calorias_racion * $raciones),
                        'proteinas_total'=> round(($n->proteinas_racion ?? 0) * $raciones, 1),
                        'fuente'         => 'bd',
                    ])->values()->toArray();
                    $ingredientes[] = [
                        'fecha'   => $pred['fecha_legible'],
                        'raciones'=> $raciones,
                        'items'   => $items,
                    ];
                }
            } elseif (!empty($recetaIA)) {
                // Fuente 2: Parsear texto de receta (ej: "arroz 130g, aceite 6g")
                preg_match_all(
                    '/([a-záéíóúñü][a-záéíóúñü\s\/]{1,35}?)\s+(\d+(?:[.,]\d+)?)\s*(g|gr|gramos|ml|cc|kg|kilo|kilos)\b/ui',
                    $recetaIA,
                    $matches,
                    PREG_SET_ORDER
                );

                if (!empty($matches)) {
                    foreach ($predicciones as $pred) {
                        $raciones = $pred['raciones_predichas'];
                        $items = [];
                        foreach ($matches as $m) {
                            $nombre   = ucfirst(trim($m[1]));
                            $cantidad = (float) str_replace(',', '.', $m[2]);
                            $unidad   = strtolower($m[3]);

                            // Normalizar a gramos
                            if (in_array($unidad, ['kg', 'kilo', 'kilos'])) {
                                $gramosPorRacion = $cantidad * 1000;
                            } else {
                                $gramosPorRacion = $cantidad;
                            }

                            $gramosTotal = round($gramosPorRacion * $raciones);
                            $kgTotal     = round($gramosTotal / 1000, 3);

                            $items[] = [
                                'producto'       => $nombre,
                                'gramos_racion'  => $gramosPorRacion,
                                'gramos_total'   => $gramosTotal,
                                'kg_total'       => $kgTotal,
                                'calorias_racion'=> null,
                                'calorias_total' => null,
                                'proteinas_total'=> null,
                                'fuente'         => 'texto',
                            ];
                        }
                        if (!empty($items)) {
                            $ingredientes[] = [
                                'fecha'   => $pred['fecha_legible'],
                                'raciones'=> $raciones,
                                'items'   => $items,
                            ];
                        }
                    }
                }
            }
        }

        // Análisis IA automático — caché se invalida cuando hay nuevo registro
        $analisisIA = null;
        if (count($historico) >= 2 && count($predicciones) > 0) {
            $ultimoRegistro  = RegistroAsistencia::where('nivel', $nivel)->max('updated_at');
            $ultimaRecetaNut = RecetaNutricional::max('updated_at');
            $hashActual = md5($ultimoRegistro . count($historico) . $recetaIA . $ultimaRecetaNut);

            // Buscar análisis guardado en BD
            try {
                $guardado = \Illuminate\Support\Facades\DB::table('ia_analisis')
                    ->where('nivel', $nivel)
                    ->where('ultimo_registro', $hashActual)
                    ->first();
            } catch (\Exception $e) {
                $guardado = null;
            }

            if ($guardado) {
                $analisisIA = $guardado->analisis;
            } else {
            $analisisIA = (function () use ($historico, $predicciones, $metricas, $m, $nivel, $ingredientes, $recetaIA) {
                try {
                    $n          = count($historico);
                    $promedio   = round(array_sum(array_column($historico, 'raciones')) / $n);
                    $tendencia  = $m > 0.5 ? 'creciente' : ($m < -0.5 ? 'decreciente' : 'estable');
                    $nivelTexto = $nivel === 'inicial' ? 'nivel inicial' : 'nivel primaria';

                    // Solo el primer día hábil próximo
                    $proximoDia = $predicciones[0] ?? null;
                    $listaPred = $proximoDia
                        ? "- {$proximoDia['fecha_legible']}: {$proximoDia['raciones_predichas']} alumnos presentes"
                        : '(sin predicción disponible)';

                    // Últimos 5 registros reales con presentes
                    $ultimosReales = collect($historico)->sortByDesc('fecha')->take(5)->map(fn($r) =>
                        "- " . \Carbon\Carbon::parse($r['fecha'])->locale('es')->isoFormat('ddd D/MM') .
                        ": {$r['presentes']} presentes / {$r['raciones']} raciones"
                    )->join("\n");

                    // Solo mostrar ingredientes del día más próximo (hoy/mañana)
                    $seccionAlimentos = '';
                    if (!empty($ingredientes)) {
                        $dia = $ingredientes[0]; // solo el primer día
                        $linea = "  {$dia['fecha']} ({$dia['raciones']} alumnos):";
                        foreach ($dia['items'] as $item) {
                            if ($item['gramos_racion'] <= 0) continue; // omitir ingredientes con 0g
                            $kcalTexto = $item['calorias_total'] !== null
                                ? " · {$item['calorias_total']} kcal"
                                : '';
                            $linea .= "\n    · {$item['producto']}: {$item['gramos_racion']}g/ración → {$item['gramos_total']}g total ({$item['kg_total']} kg){$kcalTexto}";
                        }
                        $seccionAlimentos = "\nALIMENTOS PARA EL DÍA MÁS PRÓXIMO (gramos por ración × alumnos):\n{$linea}\n\nUSA EXACTAMENTE estos números en la sección 4. No inventes otros.";
                    } elseif (!empty($recetaIA)) {
                        $seccionAlimentos = "\nRECETA (sin calcular aún): {$recetaIA}\n(No hay cálculo disponible, menciona que falta configurar la receta en PECOSA.)";
                    } else {
                        $seccionAlimentos = "\n(No hay receta configurada. En la sección 4 indica que deben registrar la receta del día.)";
                    }

                    $prompt = <<<PROMPT
Eres un asistente del Comité de Alimentación Escolar (CAE) del programa Qali Warma en Perú. Habla de forma simple y directa, como si le explicaras a la cocinera o responsable del comedor escolar.

MODELO DE PREDICCIÓN ({$nivelTexto}):
- Días históricos: {$n} · Promedio: {$promedio} alumnos/día · Tendencia: {$tendencia}
- Error promedio del modelo: {$metricas['mae']} alumnos ({$metricas['mape']}%) · Precisión R²: {$metricas['r2']}

ASISTENCIA REAL — ÚLTIMOS DÍAS:
{$ultimosReales}

PREDICCIÓN PRÓXIMOS DÍAS HÁBILES:
{$listaPred}
{$seccionAlimentos}

Responde EXACTAMENTE con estos 5 títulos numerados. Máximo 3 oraciones por sección. Sin markdown, sin asteriscos, solo texto plano:

1. ¿Qué tan confiable es el modelo?
2. ¿Cuántos alumnos se esperan mañana y cuántas raciones preparar?
3. ¿Hay días con más o menos asistencia últimamente?
4. ¿Qué alimentos preparar mañana y en qué cantidad? (copia exactamente los gramos y kg del cálculo de arriba, solo los ingredientes con cantidad mayor a 0)
5. Recomendación final para el CAE
PROMPT;

                    $response = \Illuminate\Support\Facades\Http::timeout(15)->withHeaders([
                        'Authorization' => 'Bearer ' . config('services.groq.key'),
                        'Content-Type'  => 'application/json',
                    ])->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model'       => 'llama-3.1-8b-instant',
                        'messages'    => [['role' => 'user', 'content' => $prompt]],
                        'temperature' => 0.2,
                        'max_tokens'  => 1500,
                    ]);

                    if ($response->successful()) {
                        return trim($response->json('choices.0.message.content', ''));
                    }
                    // API respondió con error
                    \Illuminate\Support\Facades\Log::error('Groq API error: ' . $response->body());
                    return '__ERROR__: ' . $response->status() . ' - ' . substr($response->body(), 0, 200);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Groq exception: ' . $e->getMessage());
                    return '__ERROR__: ' . $e->getMessage();
                }
            })();

            // Guardar en BD si se generó correctamente
            if ($analisisIA) {
                try {
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
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('ia_analisis insert error: ' . $e->getMessage());
                }
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

        $puntosRaw = array_map(fn($r, $i) => ['x' => $i, 'y' => (float)$r['raciones']], $historico, array_keys($historico));
        $puntos = $this->filtrarOutliers($puntosRaw);
        [$m, $b] = $this->linearRegression($puntos);
        $n = count($puntos);
        $predicciones = [];
        for ($i = 0; $i <= 14 && count($predicciones) < 5; $i++) {
            $fecha = now()->addDays($i);
            if (in_array($fecha->dayOfWeek, [0, 6])) continue;
            $predicciones[] = [
                'fecha'   => $fecha->locale('es')->isoFormat('dddd D/MM'),
                'raciones'=> round($this->predecir($puntos, $n + $i, $m, $b)),
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
    public function create(Request $request)
    {
        $nivel = in_array($request->get('nivel'), ['inicial', 'primaria'])
            ? $request->get('nivel')
            : 'inicial';
        return view('prediccion.create', compact('nivel'));
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

    // ── Filtrar outliers por Z-score (excluye días aberrantes) ───────────
    private function filtrarOutliers(array $puntos, float $umbral = 2.0): array
    {
        $n = count($puntos);
        if ($n < 4) return $puntos;

        $ys   = array_column($puntos, 'y');
        $mean = array_sum($ys) / $n;
        $std  = sqrt(array_sum(array_map(fn($y) => ($y - $mean) ** 2, $ys)) / $n);

        if ($std < 1) return $puntos;

        $filtrados = array_values(array_filter($puntos, fn($p) => abs($p['y'] - $mean) / $std <= $umbral));

        // Reindexar x para que sea continuo
        foreach ($filtrados as $i => &$p) { $p['x'] = $i; }
        unset($p);

        return count($filtrados) >= 3 ? $filtrados : $puntos;
    }

    // ── Regresión lineal ponderada (días recientes pesan más) ─────────────
    private function linearRegression(array $puntos): array
    {
        $n = count($puntos);
        if ($n === 0) return [0, 0];
        if ($n === 1) return [0, $puntos[0]['y']];

        // Peso exponencial: el último día tiene peso 1, el primero tiene peso e^(-decay*(n-1))
        $decay = 0.04;
        $sumW = $sumWX = $sumWY = $sumWXY = $sumWX2 = 0;

        foreach ($puntos as $i => $p) {
            $w      = exp($decay * ($i - ($n - 1)) * -1 * -1); // más reciente = mayor peso
            $w      = exp(-$decay * ($n - 1 - $i));
            $sumW   += $w;
            $sumWX  += $w * $p['x'];
            $sumWY  += $w * $p['y'];
            $sumWXY += $w * $p['x'] * $p['y'];
            $sumWX2 += $w * $p['x'] ** 2;
        }

        $denom = $sumW * $sumWX2 - $sumWX ** 2;
        if (abs($denom) < 1e-10) return [0, $sumWY / $sumW];

        $m = ($sumW * $sumWXY - $sumWX * $sumWY) / $denom;
        $b = ($sumWY - $m * $sumWX) / $sumW;

        return [$m, $b];
    }

    // ── Predicción combinada: regresión + promedio móvil reciente ─────────
    private function predecir(array $puntos, int $xFuturo, float $m, float $b): float
    {
        $pred = $m * $xFuturo + $b;

        // Promedio de los últimos 10 días como ancla
        $ultimos = array_slice($puntos, -10);
        if (count($ultimos) >= 3) {
            $ma = array_sum(array_column($ultimos, 'y')) / count($ultimos);
            // Mezcla 60% regresión + 40% promedio móvil
            $pred = 0.6 * $pred + 0.4 * $ma;
        }

        return max(0, $pred);
    }

    // ── Métricas de evaluación ────────────────────────────────────────────
    private function calcularMetricas(array $puntos, float $m, float $b): array
    {
        $n = count($puntos);
        if ($n < 2) {
            return ['mae' => 0, 'rmse' => 0, 'r2' => 0, 'mape' => 0, 'suficientes' => false, 'n' => $n];
        }

        $actuals    = array_column($puntos, 'y');
        $meanActual = array_sum($actuals) / $n;
        $mae = $rmse = $mape = $ssRes = $ssTot = $mapeCount = 0;

        foreach ($puntos as $i => $p) {
            // Usar predicción combinada solo con datos hasta el punto i
            $subPuntos = array_slice($puntos, 0, $i);
            $pred = count($subPuntos) >= 3
                ? $this->predecir($subPuntos, $p['x'], $m, $b)
                : ($m * $p['x'] + $b);

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
            'mae'         => round($mae / $n, 2),
            'rmse'        => round(sqrt($rmse / $n), 2),
            'r2'          => $ssTot > 0 ? round(1 - $ssRes / $ssTot, 4) : 1.0,
            'mape'        => $mapeCount > 0 ? round(($mape / $mapeCount) * 100, 2) : 0,
            'suficientes' => true,
            'n'           => $n,
        ];
    }
}
