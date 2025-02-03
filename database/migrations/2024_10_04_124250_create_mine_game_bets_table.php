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
        Schema::create('mine_game_bets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid');
            $table->integer('game_id',2);
            $table->decimal('amount',10,2);
            $table->double('multipler',5,2);
            $table->double('win_amount',5,2);
            $table->tinyInteger('status')->default(0);
             $table->double('tax',5,2);
            $table->double('after_tax',5,2);
            $table->int('order_id',100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mine_game_bets');
    }
};
