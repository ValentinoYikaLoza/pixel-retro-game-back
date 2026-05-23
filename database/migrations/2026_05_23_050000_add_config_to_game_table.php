<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Configuración de dificultad/economía por juego, definida en el server.
     * El cliente la recibe en `startGame` (deja de hardcodear velocidad y grid),
     * y permite validar el score máximo posible en `finishGame`.
     */
    public function up(): void
    {
        Schema::table('game', function (Blueprint $table) {
            $table->integer('lives_cost')->default(1);   // vidas que cuesta jugar
            $table->integer('tick_ms')->default(200);    // velocidad del bucle
            $table->integer('grid_width')->default(30);
            $table->integer('grid_height')->default(20);
        });
    }

    public function down(): void
    {
        Schema::table('game', function (Blueprint $table) {
            $table->dropColumn(['lives_cost', 'tick_ms', 'grid_width', 'grid_height']);
        });
    }
};
