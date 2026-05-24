<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extras de la racha: congeladores disponibles (protegen la racha ante un
     * día perdido) y el último hito consecutivo recompensado en la racha actual
     * (para no volver a otorgarlo en la misma racha).
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->integer('streak_freezes')->default(0)->after('last_streak_date');
            $table->integer('last_milestone')->default(0)->after('streak_freezes');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['streak_freezes', 'last_milestone']);
        });
    }
};
