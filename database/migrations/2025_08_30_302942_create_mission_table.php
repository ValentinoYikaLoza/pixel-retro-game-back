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
        Schema::create('mission', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->integer('total_points');
            $table->integer('current_points');
            $table->unsignedBigInteger('frequency_id');
            $table->unsignedBigInteger('reward_id');

            $table->foreign('frequency_id')->references('id')->on('frequency')->onDelete('cascade');
            $table->foreign('reward_id')->references('id')->on('reward')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission');
    }
};
