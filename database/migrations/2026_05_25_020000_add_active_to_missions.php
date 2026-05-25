<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flag para curar el pool de misiones: solo las `active` se asignan a los
     * usuarios en el rollover. Permite diseñar un set y desactivar contenido
     * viejo sin borrarlo (las asignaciones existentes siguen válidas).
     */
    public function up(): void
    {
        foreach (['daily_mission', 'weekly_mission', 'monthly_mission'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('active')->default(true);
            });
        }
    }

    public function down(): void
    {
        foreach (['daily_mission', 'weekly_mission', 'monthly_mission'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('active');
            });
        }
    }
};
