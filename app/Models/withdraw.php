<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'actual_amount',
        'account_id',
        'type',
        'order_id',
        'status',
        'typeimage',
        'created_at',
        'updated_at',
    ];
}
