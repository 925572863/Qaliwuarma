<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Autorización por rol para los módulos de investigación (fichas 4/5/6,
 * entrenamiento del modelo IA, importadores masivos y exportadores).
 *
 * - admin: acceso total (gestión de usuarios incluida).
 * - investigador: puede gestionar los datos de la tesis (registrar, importar,
 *   eliminar fichas y entrenar el modelo), pero no administra usuarios.
 * - personal: solo puede ver los módulos y registrar la asistencia diaria del
 *   comedor; no puede borrar registros ni hacer cargas masivas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'investigador', 'personal'])->default('personal')->after('email');
        });

        // El usuario id=1 ya se trataba como super-admin (ver UserController::resetPassword,
        // hoy hardcodeado a Auth::id() === 1). Se conserva ese comportamiento con el rol formal.
        \Illuminate\Support\Facades\DB::table('users')->where('id', 1)->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
