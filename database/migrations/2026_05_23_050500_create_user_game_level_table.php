<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Progreso del usuario por nivel: mejor puntaje y si lo superó. Un nivel se
     * desbloquea cuando el anterior está superado (cleared_at != null).
     */
    public function up(): void
    {
        Schema::create('user_game_level', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('game_level_id');
            $table->integer('best_score')->default(0);
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('game_level_id')->references('id')->on('game_level')->onDelete('cascade');
            $table->unique(['user_id', 'game_level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_game_level');
    }
};
