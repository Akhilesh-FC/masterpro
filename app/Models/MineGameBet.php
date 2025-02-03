<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MineGameBet extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'amount',
        'game_id',
        'userid',
        'status',
        'created_at',
        'updated_at',
        'tax',
        'after_tax',
        'order_id'
    ];
}
