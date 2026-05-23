<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una partida. Es la fuente de verdad del servidor: se crea en `startGame`
     * (status in_progress) y se cierra en `finishGame`/`abandonGame`. La semilla
     * permite reproducir/validar el RNG y los campos de telemetría sustentan el
     * control anti-trampa por plausibilidad.
     */
    public function up(): void
    {
        Schema::create('game_session', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('game_id');
            $table->string('status')->default('in_progress'); // in_progress|finished|abandoned
            $table->integer('score')->default(0);
            $table->bigInteger('seed');
            $table->integer('food_eaten')->default(0);
            $table->integer('duration_ms')->default(0);
            $table->integer('exp_awarded')->default(0);
            $table->integer('coins_awarded')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('game_id')->references('id')->on('game')->onDelete('cascade');

            $table->index(['user_id', 'game_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_session');
    }
};
