<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\PecosaInicialController;
use App\Http\Controllers\PecosaPrimariaController;
use App\Http\Controllers\PrediccionController;
use App\Http\Controllers\AporteController;
use App\Http\Controllers\ComprasAdicionalesController;
use App\Http\Controllers\ProductosVencidosController;
use App\Http\Controllers\EvaluacionController;
use App\Http\Controllers\ControlNutricionalController;
use App\Http\Controllers\ControlDistribucionController;
use App\Http\Controllers\IaEntrenamientoController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AnalisisContextoController;
use App\Http\Controllers\ProrrateoInicialController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', fn () => redirect()->route('dashboard'));

// Exportar datos temporalmente (solo para migración)
Route::get('/exportar-datos-migracion', function () {
    $data = [
        'registros_asistencia' => \App\Models\RegistroAsistencia::all()->toArray(),
        'pecosa_inicial'       => \DB::table('pecosa_inicial')->get()->toArray(),
        'pecosa_primaria'      => \DB::table('pecosa_primaria')->get()->toArray(),
    ];
    return response()->json($data);
})->middleware('auth');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

});

Route::get('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Protected routes
Route::middleware(['auth', 'throttle:120,1'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/alumnos/plantilla-inicial',  [AlumnoController::class, 'plantillaInicial'])->name('alumnos.plantilla-inicial');
    Route::get('/alumnos/plantilla-primaria', [AlumnoController::class, 'plantillaPrimaria'])->name('alumnos.plantilla-primaria');
    Route::post('/alumnos/importar-inicial',  [AlumnoController::class, 'importInicial'])->name('alumnos.importar-inicial')->middleware('throttle:10,1');
    Route::post('/alumnos/importar-primaria', [AlumnoController::class, 'importPrimaria'])->name('alumnos.importar-primaria')->middleware('throttle:10,1');
    Route::resource('alumnos', AlumnoController::class);

    // Pecosa Inicial (CRUD completo)
    Route::prefix('pecosa/inicial')->name('pecosa.inicial.')->group(function () {
        Route::get('/',                   [PecosaInicialController::class, 'index'])->name('index');
        Route::get('/crear',              [PecosaInicialController::class, 'create'])->name('create');
        Route::post('/',                  [PecosaInicialController::class, 'store'])->name('store');
        Route::get('/{inicial}/editar',   [PecosaInicialController::class, 'edit'])->name('edit');
        Route::put('/{inicial}',          [PecosaInicialController::class, 'update'])->name('update');
        Route::delete('/{inicial}',       [PecosaInicialController::class, 'destroy'])->name('destroy');

        // Distribución / Prorrateo Inicial
        Route::get('/prorrateo',                     [ProrrateoInicialController::class, 'index'])->name('prorrateo');
        Route::post('/prorrateo',                    [ProrrateoInicialController::class, 'guardar'])->name('prorrateo.guardar');
        Route::get('/distribuciones',                [ProrrateoInicialController::class, 'historial'])->name('distribuciones');
        Route::get('/distribuciones/{version}',      [ProrrateoInicialController::class, 'verVersion'])->name('distribuciones.ver');
        Route::delete('/distribuciones/{version}',   [ProrrateoInicialController::class, 'eliminarVersion'])->name('distribuciones.eliminar');
        Route::get('/distribuciones/{version}/listado/{seccion}', [ProrrateoInicialController::class, 'listadoAula'])->name('distribuciones.listado');
        Route::post('/distribuciones/importar',      [ProrrateoInicialController::class, 'importarExcel'])->name('distribuciones.importar')->middleware('throttle:10,1');

        // Lista de compras adicionales
        Route::get('/plantilla', [PecosaInicialController::class, 'plantilla'])->name('plantilla');
        Route::post('/importar', [PecosaInicialController::class, 'importar'])->name('importar')->middleware('throttle:10,1');
        Route::post('/nutricion', [PecosaInicialController::class, 'nutricion'])->name('nutricion');

        Route::get('/compras',                        [ComprasAdicionalesController::class, 'index'])->name('compras');
        Route::post('/compras',                       [ComprasAdicionalesController::class, 'store'])->name('compras.store');
        Route::put('/compras/{id}',                   [ComprasAdicionalesController::class, 'update'])->name('compras.update');
        Route::patch('/compras/{id}/estado',          [ComprasAdicionalesController::class, 'toggleEstado'])->name('compras.estado');
        Route::delete('/compras/{id}',                [ComprasAdicionalesController::class, 'destroy'])->name('compras.destroy');
        Route::delete('/compras-limpiar',             [ComprasAdicionalesController::class, 'limpiarComprados'])->name('compras.limpiar');
    });

    // Predicción de raciones
    Route::prefix('prediccion')->name('prediccion.')->group(function () {
        Route::get('/',              [PrediccionController::class, 'index'])->name('index');
        Route::get('/crear',         [PrediccionController::class, 'create'])->name('create');
        Route::post('/',             [PrediccionController::class, 'store'])->name('store');
        Route::get('/secciones-grado',[PrediccionController::class, 'seccionesGrado'])->name('secciones-grado');
        Route::get('/alumnos-aula',  [PrediccionController::class, 'alumnosAula'])->name('alumnos-aula');
        Route::get('/detalle-aula',  [PrediccionController::class, 'detalleAula'])->name('detalle-aula');
        Route::get('/ia',            [PrediccionController::class, 'analizarIA'])->name('ia');
        Route::post('/guardar-receta',  [PrediccionController::class, 'guardarReceta'])->name('guardar-receta');
        Route::post('/descontar-stock', [PrediccionController::class, 'descontarStock'])->name('descontar-stock');

        // Acciones sensibles del módulo de investigación: borrar histórico, cargas
        // masivas y reentrenar el modelo IA, restringidas a admin/investigador.
        Route::middleware('role:admin,investigador')->group(function () {
            Route::delete('/{registro}', [PrediccionController::class, 'destroy'])->name('destroy');
            Route::post('/importar',     [PrediccionController::class, 'importarHistorico'])->name('importar')->middleware('throttle:10,1');
            Route::post('/entrenar-ia',  [PrediccionController::class, 'entrenarIA'])->name('entrenar-ia')->middleware('throttle:5,1');
        });
    });

    // Aportes PAE – Nivel Inicial
    Route::prefix('aportes')->name('aportes.')->group(function () {
        Route::get('/',                          [AporteController::class, 'index'])->name('index');
        Route::post('/pagos',                    [AporteController::class, 'registrarPagos'])->name('pagos.store');
        Route::post('/config',                   [AporteController::class, 'storeConfig'])->name('config.store');
        Route::delete('/config/{config}',        [AporteController::class, 'destroyConfig'])->name('config.destroy');
        Route::post('/config/{config}/semana',   [AporteController::class, 'storeSemana'])->name('semana.store');
    });

    // Pecosa Primaria (CRUD completo)
    Route::prefix('pecosa/primaria')->name('pecosa.primaria.')->group(function () {
        Route::get('/',                    [\App\Http\Controllers\PecosaPrimariaController::class, 'index'])->name('index');
        Route::get('/prorrateo',                      [\App\Http\Controllers\ProrrateoController::class, 'primaria'])->name('prorrateo');
        Route::post('/prorrateo',                     [\App\Http\Controllers\ProrrateoController::class, 'guardar'])->name('prorrateo.guardar');
        Route::get('/distribuciones',                              [\App\Http\Controllers\ProrrateoController::class, 'historial'])->name('distribuciones');
        Route::get('/distribuciones/{version}',                    [\App\Http\Controllers\ProrrateoController::class, 'verVersion'])->name('distribuciones.ver');
        Route::delete('/distribuciones/{version}',                 [\App\Http\Controllers\ProrrateoController::class, 'eliminarVersion'])->name('distribuciones.eliminar');
        Route::get('/distribuciones/{version}/listado/{seccion}',  [\App\Http\Controllers\ProrrateoController::class, 'listadoAula'])->name('distribuciones.listado');
        Route::post('/distribuciones/importar',                    [\App\Http\Controllers\ProrrateoController::class, 'importarExcel'])->name('distribuciones.importar')->middleware('throttle:10,1');
        Route::post('/importar',           [PecosaPrimariaController::class, 'importar'])->name('importar')->middleware('throttle:10,1');
        Route::get('/crear',               [\App\Http\Controllers\PecosaPrimariaController::class, 'create'])->name('create');
        Route::post('/',                   [PecosaPrimariaController::class, 'store'])->name('store');
        Route::get('/{primarium}/editar',  [PecosaPrimariaController::class, 'edit'])->name('edit');
        Route::put('/{primarium}',         [PecosaPrimariaController::class, 'update'])->name('update');
        Route::delete('/{primarium}',      [PecosaPrimariaController::class, 'destroy'])->name('destroy');
    });

    // Productos Vencidos
    Route::get('/vencidos', [ProductosVencidosController::class, 'index'])->name('vencidos.index');
    Route::get('/vencidos/reporte', [ProductosVencidosController::class, 'reporte'])->name('vencidos.reporte');

    // Evaluación de Usabilidad
    Route::get('/evaluacion',          [EvaluacionController::class, 'index'])->name('evaluacion.index');
    Route::get('/evaluacion/nueva',    [EvaluacionController::class, 'create'])->name('evaluacion.create');
    Route::post('/evaluacion',         [EvaluacionController::class, 'store'])->name('evaluacion.store');
    Route::delete('/evaluacion/{evaluacion}', [EvaluacionController::class, 'destroy'])->name('evaluacion.destroy');

    // Control Nutricional (Ficha 5 - VD: precisión en raciones nutricionales)
    // Módulo de investigación: solo admin/investigador pueden capturar, importar o borrar datos.
    Route::get('/control-nutricional', [ControlNutricionalController::class, 'index'])->name('control-nutricional.index');
    Route::get('/control-nutricional/plantilla', [ControlNutricionalController::class, 'plantilla'])->name('control-nutricional.plantilla');
    Route::middleware('role:admin,investigador')->group(function () {
        Route::get('/control-nutricional/nuevo', [ControlNutricionalController::class, 'create'])->name('control-nutricional.create');
        Route::post('/control-nutricional',      [ControlNutricionalController::class, 'store'])->name('control-nutricional.store');
        Route::post('/control-nutricional/importar', [ControlNutricionalController::class, 'importar'])->name('control-nutricional.importar')->middleware('throttle:10,1');
        Route::delete('/control-nutricional/{control_nutricional}', [ControlNutricionalController::class, 'destroy'])->name('control-nutricional.destroy');
    });

    // Control de Distribución (Ficha 6 - VD: eficiencia en distribución y desperdicio)
    Route::get('/control-distribucion', [ControlDistribucionController::class, 'index'])->name('control-distribucion.index');
    Route::get('/control-distribucion/plantilla', [ControlDistribucionController::class, 'plantilla'])->name('control-distribucion.plantilla');
    Route::middleware('role:admin,investigador')->group(function () {
        Route::get('/control-distribucion/nuevo', [ControlDistribucionController::class, 'create'])->name('control-distribucion.create');
        Route::post('/control-distribucion',      [ControlDistribucionController::class, 'store'])->name('control-distribucion.store');
        Route::post('/control-distribucion/importar', [ControlDistribucionController::class, 'importar'])->name('control-distribucion.importar')->middleware('throttle:10,1');
        Route::delete('/control-distribucion/{control_distribucion}', [ControlDistribucionController::class, 'destroy'])->name('control-distribucion.destroy');
    });

    // Historial de entrenamientos IA (Fichas 1, 2 y 3 - VI) — solo lectura, visible para todos
    Route::get('/ia-entrenamientos', [IaEntrenamientoController::class, 'index'])->name('ia-entrenamientos.index');

    // Análisis de variables de contexto (clima, eventos especiales) — solo lectura
    Route::get('/analisis-contexto', [AnalisisContextoController::class, 'index'])->name('analisis-contexto.index');

    // Exportadores CSV para SPSS/Excel (fichas 4, 5, 6 y entrenamientos IA) — datos institucionales,
    // restringidos a quienes gestionan la investigación.
    Route::prefix('exportar')->name('exportar.')->middleware('role:admin,investigador')->group(function () {
        Route::get('/raciones',      [ExportController::class, 'raciones'])->name('raciones');
        Route::get('/nutricional',   [ExportController::class, 'nutricional'])->name('nutricional');
        Route::get('/distribucion',  [ExportController::class, 'distribucion'])->name('distribucion');
        Route::get('/ia-entrenamientos', [ExportController::class, 'iaEntrenamientos'])->name('ia-entrenamientos');

        // Comparativos pareados pretest/postest, listos para SPSS (Shapiro-Wilk + t-Student/Wilcoxon)
        Route::get('/comparativo/raciones',     [ExportController::class, 'comparativoRaciones'])->name('comparativo.raciones');
        Route::get('/comparativo/nutricional',  [ExportController::class, 'comparativoNutricional'])->name('comparativo.nutricional');
        Route::get('/comparativo/mermas',       [ExportController::class, 'comparativoMermas'])->name('comparativo.mermas');
        Route::get('/comparativo/tiempo-distribucion', [ExportController::class, 'comparativoTiempoDistribucion'])->name('comparativo.tiempo-distribucion');
        Route::get('/contexto', [ExportController::class, 'contexto'])->name('contexto');
    });

    // Gestión de usuarios
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
});
