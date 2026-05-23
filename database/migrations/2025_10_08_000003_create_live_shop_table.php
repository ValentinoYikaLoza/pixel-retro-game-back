<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_shop', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->decimal('price', 8, 2);
            $table->integer('type_id'); // 1 = usd, 2 = coin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_shop');
    }
};
