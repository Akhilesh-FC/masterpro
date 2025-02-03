<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    use HasFactory;
     protected $fillable = [
        'userid',
        'name',
        'account_num',
        'bank_name',
        'ifsc_code',
        'status',
        
    ];
}
