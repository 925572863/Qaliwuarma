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
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', fn () => redirect()->route('dashboard'));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
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

        // Lista de compras adicionales
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
        Route::delete('/{registro}', [PrediccionController::class, 'destroy'])->name('destroy');
        Route::get('/secciones-grado',[PrediccionController::class, 'seccionesGrado'])->name('secciones-grado');
        Route::get('/alumnos-aula',  [PrediccionController::class, 'alumnosAula'])->name('alumnos-aula');
        Route::get('/detalle-aula',  [PrediccionController::class, 'detalleAula'])->name('detalle-aula');
        Route::get('/ia',            [PrediccionController::class, 'analizarIA'])->name('ia');
        Route::post('/importar',       [PrediccionController::class, 'importarHistorico'])->name('importar')->middleware('throttle:10,1');
        Route::post('/guardar-receta',  [PrediccionController::class, 'guardarReceta'])->name('guardar-receta');
        Route::post('/descontar-stock', [PrediccionController::class, 'descontarStock'])->name('descontar-stock');
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
});
