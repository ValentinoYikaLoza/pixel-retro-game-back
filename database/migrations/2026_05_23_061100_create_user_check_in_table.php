<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial de check-ins diarios (un registro por día UTC en que el usuario
     * entró). Alimenta el calendario tipo Duolingo y el conteo de días del mes
     * para las metas mensuales.
     */
    public function up(): void
    {
        Schema::create('user_check_in', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('checked_in_on');

            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
            $table->unique(['user_id', 'checked_in_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_check_in');
    }
};
