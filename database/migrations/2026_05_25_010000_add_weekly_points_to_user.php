<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Puntos de liga semanales: métrica competitiva de la división. Se acumulan
     * como la exp (igual que `score`) pero se REINICIAN cada semana en el
     * rollover de liga (ascenso/descenso). El ranking de división ordena por
     * esto, no por el `score` de por vida.
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->integer('weekly_points')->default(0)->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('weekly_points');
        });
    }
};
