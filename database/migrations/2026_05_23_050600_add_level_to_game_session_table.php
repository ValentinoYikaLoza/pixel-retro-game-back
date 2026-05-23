<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nivel jugado en la sesión: define la config aplicada y, al terminar,
     * contra qué objetivo se evalúa si se superó.
     */
    public function up(): void
    {
        Schema::table('game_session', function (Blueprint $table) {
            $table->integer('level')->default(1)->after('game_id');
        });
    }

    public function down(): void
    {
        Schema::table('game_session', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
