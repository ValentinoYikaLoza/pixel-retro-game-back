<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Niveles de un juego, definidos en el servidor (balanceables sin actualizar
     * la app). La dificultad combina velocidad (tick_ms), borde sólido
     * (wrap_around=false), obstáculos (walls) y arena más chica (grid), más un
     * objetivo (target_score) para superar el nivel.
     */
    public function up(): void
    {
        Schema::create('game_level', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->integer('level');
            $table->integer('tick_ms');
            $table->integer('grid_width');
            $table->integer('grid_height');
            $table->boolean('wrap_around')->default(false);
            $table->json('walls')->nullable(); // [[x,y], ...] celdas-obstáculo
            $table->integer('target_score');
            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('game')->onDelete('cascade');
            $table->unique(['game_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_level');
    }
};
