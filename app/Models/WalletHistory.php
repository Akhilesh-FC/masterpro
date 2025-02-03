<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    use HasFactory;
    
      protected $fillable = [
        'user_id',
        'amount',
        'type_id',
        'description',
        'description_2',
    ];

}
