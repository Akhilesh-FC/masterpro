<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftClaim extends Model
{
    use HasFactory;
    protected $fillable = [
        'userid',
        'gift_code',
        'amount',
        'status'
    ];
    
    /// giftredeemed function uses///
    
     public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }
}
