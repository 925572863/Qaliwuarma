<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El Random Forest de la tesis usa profundidad sin límite (max_depth = None),
     * que se guarda como null.
     */
    public function up(): void
    {
        Schema::table('ia_entrenamientos', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_depth')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ia_entrenamientos', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_depth')->nullable(false)->change();
        });
    }
};
