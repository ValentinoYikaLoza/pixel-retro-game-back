<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca cuándo se duplicó la recompensa de una partida vía anuncio
     * (rewarded ad). Garantiza que el bono solo se otorgue una vez por sesión.
     */
    public function up(): void
    {
        Schema::table('game_session', function (Blueprint $table) {
            $table->timestamp('rewarded_at')->nullable()->after('ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('game_session', function (Blueprint $table) {
            $table->dropColumn('rewarded_at');
        });
    }
};
