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
        Schema::create('user_monthly_mission', function (Blueprint $table) {
            $table->id();
            $table->integer('current_value')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('monthly_mission_id');
            $table->unsignedBigInteger('status_id');

            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('monthly_mission_id')->references('id')->on('monthly_mission')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('status')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_monthly_mission');
    }
};
