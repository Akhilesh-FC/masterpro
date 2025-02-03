<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    use HasFactory;
    
     protected $fillable = [
        'code',
        'amount',
        'number_people',
        'availed_numb',
        'status',
    ];
}
