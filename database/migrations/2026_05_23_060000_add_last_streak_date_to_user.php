<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fecha (UTC) del último check-in de racha. Permite incrementar la racha
     * solo una vez por día, mantenerla en días consecutivos y reiniciarla si
     * el usuario se salta un día.
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->date('last_streak_date')->nullable()->after('streak');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('last_streak_date');
        });
    }
};
