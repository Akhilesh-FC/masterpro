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
        Schema::create('users', function (Blueprint $table) {
            // $table->bigIncrements('id');
            // $table->string('name');
            // $table->string('mobile', 11);
            // $table->string('email', 32)->unique();
            // $table->timestamp('email_verified_at')->nullable();
            // $table->string('password');
            // $table->decimal('wallet', 10, 2); 
            // $table->decimal('deposit_amount', 10, 2); 
            // $table->string('image')->nullable(); 
            // $table->integer('role_id')->constrained(); 
            // $table->decimal('commission', 10, 2)->nullable();
            // $table->decimal('turnover', 10, 2)->nullable(); 
            // $table->string('referral_code', 15)->nullable();
            // $table->foreignId('referrer_id')->nullable()->constrained('users'); 
            // $table->tinyInteger('first_recharge')->nullable(); 
            // $table->decimal('today_turnover', 10, 2)->nullable(); 
            // $table->enum('status', ['1', '0'])->default('1'); 
            // $table->rememberToken();
            // $table->timestamps();
            
             $table->id();
    $table->string('name');
    $table->unsignedBigInteger('u_id')->unique();
    $table->string('mobile');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('image')->nullable();
    $table->string('referral_code')->nullable();
    $table->unsignedBigInteger('referrer_id')->nullable();
    $table->string('third_party_wallet')->nullable();
    
    // Change decimal to double
    $table->double('commission', 10, 2)->default(0);
    $table->double('bonus', 10, 2)->default(0);
    $table->double('total_referral_bonus', 10, 2)->default(0);
    $table->double('turnover', 15, 2)->default(0);
    $table->double('today_turnover', 15, 2)->default(0);
    $table->double('totalbet', 15, 2)->default(0);
    $table->boolean('first_recharge')->default(0);
    $table->boolean('salary_first_recharge')->default(0);
    $table->double('first_recharge_amount', 10, 2)->default(0);
    $table->double('recharge', 15, 2)->default(0);
    $table->boolean('verification')->default(0);
    $table->unsignedBigInteger('role_id')->default(2); // assuming default role is user
    $table->date('dob')->nullable();
    $table->double('wallet', 15, 2)->default(0);
    $table->double('bonus_wallet', 10, 2)->default(0);
    $table->double('total_payin', 15, 2)->default(0);
    $table->double('total_payout', 15, 2)->default(0);
    $table->integer('no_of_payin')->default(0);
    $table->integer('no_of_payout')->default(0);
    $table->double('yesterday_payin', 15, 2)->default(0);
    $table->integer('yesterday_register')->default(0);
    $table->integer('yesterday_no_of_payin')->default(0);
    $table->boolean('yesterday_first_deposit')->default(0);
    $table->double('yesterday_total_commission', 10, 2)->default(0);
    $table->double('winning_wallet', 15, 2)->default(0);
    $table->double('deposit_amount', 15, 2)->default(0);
    $table->double('withdraw_balance', 15, 2)->default(0);
    $table->double('win_loss', 15, 2)->default(0);
    $table->string('type')->nullable();
    $table->string('status')->default('active');
    $table->timestamps(); // created_at and updated_at fields
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
