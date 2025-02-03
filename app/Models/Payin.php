<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payin extends Model
{
    use HasFactory;
    
     protected $fillable = ['user_id', 'cash', 'order_id', 'type', 'status','zili_utr_num'];
    
    public $timestamps = false;
    /////  subordinate_data //////
     public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    ///// subordinate_data //////
}
