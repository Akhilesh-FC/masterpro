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
        Schema::create('bet_results', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->double('column: games_no');
            $table->double('game_id');
            $table->longText('json')->nullable();
            $table->string('random_card',200);
            $table->string('token',100);
            $table->integer('block');
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_results');
    }
};
