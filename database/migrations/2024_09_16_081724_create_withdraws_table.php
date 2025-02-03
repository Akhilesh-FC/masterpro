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
        Schema::create('withdraws', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount',10,2);
            $table->double('actual_amount',2,8);
            $table->integer('mobile');
            $table->unsignedBigInteger('account_id');
            $table->integer('type');
            $table->string('usdt_wallet_address',100);
            $table->string('order_id',100);
            $table->integer('payout');
            $table->string('remark',200);
            $table->string('response');
            $table->tinyInteger('status')->default(1);
            $table->string('typeimage',200);
            $table->string('referenceId',50);
            $table->text('reject_msg');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraws');
    }
};
