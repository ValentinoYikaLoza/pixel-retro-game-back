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
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('score');
            $table->integer('coins');
            $table->integer('lives');
            $table->integer('streak');
            $table->integer('timesRankedFirst');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('country_id');

            $table->foreign('division_id')->references('id')->on('division')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('country')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
