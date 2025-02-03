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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->double('amount',2,8);
            $table->double('commission',2,8);
            $table->double('trade_amount',2,8);
            $table->double('win_amount',2,8);
            $table->integer('number');
            $table->integer('win_number');
            $table->integer('games_no');
            $table->integer('game_id');
            $table->bigInteger('userid');
            $table->string('order_id');
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
