<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_mission', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->integer('total_value');
            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('mission_type_id');
            $table->unsignedBigInteger('reward_id');

            $table->foreign('game_id')->references('id')->on('game')->onDelete('cascade');
            $table->foreign('mission_type_id')->references('id')->on('mission_type')->onDelete('cascade');
            $table->foreign('reward_id')->references('id')->on('reward')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_mission');
    }
};
