<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    
    
    
    
    /// AdminUserList///
    
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
    /// AdminUserList///
    
    ////// Agency promation/////
    
public function referrals()
{
    return $this->hasMany(User::class, 'referrer_id');
}

// Payins relationship (assuming Payin model exists)
public function payins()
{
    return $this->hasMany(Payin::class, 'user_id'); // 'user_id' should match the foreign key in the payins table
}


// Recursive function to count all subordinates
public function getAllSubordinatesCount()
{
    $count = 0;
    $subordinates = $this->referrals;

    foreach ($subordinates as $subordinate) {
        $count++;
        $count += $subordinate->getAllSubordinatesCount(); // Recursively count subordinates
    }

    return $count;
}
    ////// Agency promation/////
    
    /// subordinate_data /////
    public function referral()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referals()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'userid');
    }

    public function payinss()
    {
        return $this->hasMany(Payin::class, 'user_id');
    }

    public function mlmLevel()
    {
        return $this->hasOne(MlmLevel::class, 'id', 'level');
    }
    //// subordinate_data ////
    

    
      protected $fillable = [
        'name',
        'u_id',
        'mobile',
        'email',
        'password',
		  'country_code',
		  'account_token',
		  'accountNo',
		  'spribe_id',
        'image',
        'referral_code',
        'referrer_id',
        'third_party_wallet',
        'commission',
        'bonus',
        'total_referral_bonus',
        'turnover',
        'today_turnover',
        'totalbet',
        'first_recharge',
        'salary_first_recharge',
        'first_recharge_amount',
        'recharge',
        'verification',
        'role_id',
        'dob',
        'wallet',
        'bonus_wallet',
        'total_payin',
        'total_payout',
        'no_of_payin',
        'no_of_payout',
        'yesterday_payin',
        'yesterday_register',
        'yesterday_no_of_payin',
        'yesterday_first_deposit',
        'yesterday_total_commission',
        'winning_wallet',
        'deposit_amount',
        'withdraw_balance',
        'win_loss',
        'type',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        //'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            //'password' => 'hashed',
        ];
    }
}
