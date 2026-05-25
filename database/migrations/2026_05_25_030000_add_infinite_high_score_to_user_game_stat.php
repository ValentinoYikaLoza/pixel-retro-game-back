<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Récord del MODO INFINITO por usuario+juego, separado del high_score de los
     * niveles para no mezclar ambos modos (habilita un leaderboard infinito).
     */
    public function up(): void
    {
        Schema::table('user_game_stat', function (Blueprint $table) {
            $table->integer('infinite_high_score')->default(0)->after('high_score');
        });
    }

    public function down(): void
    {
        Schema::table('user_game_stat', function (Blueprint $table) {
            $table->dropColumn('infinite_high_score');
        });
    }
};
