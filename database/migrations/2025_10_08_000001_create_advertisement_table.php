<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisement', function (Blueprint $table) {
            $table->id();
            $table->integer('reward');
            $table->string('reward_type'); // coin | life
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement');
    }
};
