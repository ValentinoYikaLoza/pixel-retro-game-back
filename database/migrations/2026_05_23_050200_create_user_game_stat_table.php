<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregado por usuario+juego. Denormaliza el mejor puntaje y los totales para
     * que los leaderboards por juego no tengan que recorrer game_session.
     */
    public function up(): void
    {
        Schema::create('user_game_stat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('game_id');
            $table->integer('high_score')->default(0);
            $table->integer('total_games')->default(0);
            $table->integer('total_score')->default(0);
            $table->integer('total_food')->default(0);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('game_id')->references('id')->on('game')->onDelete('cascade');

            $table->unique(['user_id', 'game_id']);
            $table->index(['game_id', 'high_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_game_stat');
    }
};
