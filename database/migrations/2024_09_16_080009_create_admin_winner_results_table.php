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
        Schema::create('admin_winner_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('gamesno')->unique();
            $table->smallInteger('gameId');
            $table->Integer('number');
            $table->Integer('status')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_winner_results');
    }
};
