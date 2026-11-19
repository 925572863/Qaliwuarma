<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_historial', function (Blueprint $table) {
            $table->foreignId('pecosa_primaria_id')->nullable()->after('pecosa_inicial_id')
                ->constrained('pecosa_primaria')->onDelete('cascade');
        });

        // SQLite (local y tests) no admite ALTER COLUMN: Laravel reconstruye la tabla.
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('stock_historial', function (Blueprint $table) {
                $table->unsignedBigInteger('pecosa_inicial_id')->nullable()->change();
            });
        } else {
            DB::statement('ALTER TABLE stock_historial ALTER COLUMN pecosa_inicial_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('stock_historial', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pecosa_primaria_id');
        });

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('stock_historial', function (Blueprint $table) {
                $table->unsignedBigInteger('pecosa_inicial_id')->nullable(false)->change();
            });
        } else {
            DB::statement('ALTER TABLE stock_historial ALTER COLUMN pecosa_inicial_id SET NOT NULL');
        }
    }
};
