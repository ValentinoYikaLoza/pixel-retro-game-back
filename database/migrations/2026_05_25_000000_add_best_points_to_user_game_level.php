<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mejor PUNTAJE (score real) por nivel, además del best_score que guarda la
     * métrica del objetivo (p. ej. pellets en Pac-Man, líneas en Tetris). Permite
     * mostrar en el selector un récord que varía e incentiva rejugar.
     */
    public function up(): void
    {
        Schema::table('user_game_level', function (Blueprint $table) {
            $table->integer('best_points')->default(0)->after('best_score');
        });
    }

    public function down(): void
    {
        Schema::table('user_game_level', function (Blueprint $table) {
            $table->dropColumn('best_points');
        });
    }
};
