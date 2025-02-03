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
        Schema::create('aviator_salaries', function (Blueprint $table) {
            $table->id();
            $table->tinyint('active_members');  
            $table->double('daily_fix_salary', 10, 2); 
            $table->tinyint('status', 1); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aviator_salaries');
    }
};
