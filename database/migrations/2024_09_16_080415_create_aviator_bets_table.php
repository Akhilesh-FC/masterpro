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
        Schema::create('aviator_bets', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 32); // Changed to 'char' for fixed length
            $table->decimal('amount', 15, 2); // Changed to 'decimal' for monetary values with precision and scale
            $table->decimal('stop_multiplier', 8, 2); // Adjusted to decimal for better numerical handling
            $table->unsignedBigInteger('game_id')->nullable(); // Changed from timestamp to unsignedBigInteger for 'game_id'
            $table->decimal('totalamount', 15, 2); // Changed to decimal for total amount
            $table->decimal('number', 10, 2); 
            $table->decimal('sub_number', 10, 2); 
            $table->string('color', 20)->nullable(); // Shortened 'color' field to appropriate length
            $table->unsignedInteger('game_sr_num'); // Changed to unsignedInteger for serial number
            $table->decimal('commission', 10, 2)->nullable();
            $table->decimal('result_status', 5, 2)->nullable(); // Adjusted precision and scale
            $table->decimal('win', 15, 2)->nullable(); // Changed 'win' to decimal for numeric win values
            $table->unsignedBigInteger('multiplier')->nullable(); // Foreign key should be unsignedBigInteger
            $table->enum('status', ['1', '0'])->default('1'); 
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aviator_bets');
    }
};
