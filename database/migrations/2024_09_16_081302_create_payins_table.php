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
        Schema::create('payins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->double('cash',2,10);
            $table->double('usdt_amount',2,10);
            $table->double('extra_cash',2.10);
            $table->string('bonus',20);
            $table->string('screenshot',100);
            $table->string('order_id',100);
            $table->string('redirect_url',100);
            $table->unsignedInteger('type');
            $table->tinyInteger('status');
            $table->string('typeimages',100);
            $table->string('transaction_id',100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payins');
    }
};
