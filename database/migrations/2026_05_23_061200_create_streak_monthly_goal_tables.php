<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Metas mensuales de racha (catálogo) y su reclamo por usuario. La meta se
     * cumple al entrar `days_required` días dentro del mes (UTC). El reclamo se
     * reinicia cada mes mediante `period_key` ('Y-m').
     */
    public function up(): void
    {
        Schema::create('streak_monthly_goal', function (Blueprint $table) {
            $table->id();
            $table->integer('days_required');
            $table->integer('reward_coins')->default(0);
            $table->integer('reward_freezes')->default(0);
            $table->integer('reward_exp')->default(0);
        });

        Schema::create('user_streak_monthly_goal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('streak_monthly_goal_id');
            $table->string('period_key'); // 'Y-m' (UTC)
            $table->timestamp('claimed_at')->nullable();

            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
            $table->foreign('streak_monthly_goal_id')->references('id')->on('streak_monthly_goal')->onDelete('cascade');
            $table->unique(['user_id', 'streak_monthly_goal_id', 'period_key'], 'user_goal_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_streak_monthly_goal');
        Schema::dropIfExists('streak_monthly_goal');
    }
};
