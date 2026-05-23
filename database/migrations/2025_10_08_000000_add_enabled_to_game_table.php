<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite activar/desactivar juegos desde el server.
     */
    public function up(): void
    {
        Schema::table('game', function (Blueprint $table) {
            $table->boolean('enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('game', function (Blueprint $table) {
            $table->dropColumn('enabled');
        });
    }
};
