<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clave del período (UTC) en el que está asignada cada misión del usuario:
     * diaria 'Y-m-d', semanal 'o-W' (semana ISO), mensual 'Y-m'. Permite el
     * rollover perezoso: al listar, si la clave guardada != el período actual,
     * la misión se reasigna y reinicia.
     */
    public function up(): void
    {
        foreach (['user_daily_mission', 'user_weekly_mission', 'user_monthly_mission'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('period_key')->nullable()->after('current_value');
            });
        }
    }

    public function down(): void
    {
        foreach (['user_daily_mission', 'user_weekly_mission', 'user_monthly_mission'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('period_key');
            });
        }
    }
};
