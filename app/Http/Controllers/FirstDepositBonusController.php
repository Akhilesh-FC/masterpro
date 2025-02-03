<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class FirstDepositBonusController extends Controller
{
	public function index(){
		
	$first_deposit_bonus = DB::table('first_deposit_bonus')->where('status',1)->get();
		
	return view('first_deposit_bonus.index')->with('first_deposit_bonus',$first_deposit_bonus);
		
	}
	
	  public function first_deposit_bonus_update(Request $request, $id)
      {
        $recharge_min=$request->recharge_min;
        $recharge_max=$request->recharge_max;
        $member=$request->member;
		$agent=$request->agent;
		  
        $data= DB::update("UPDATE `first_deposit_bonus` SET `recharge_min`='$recharge_min',`recharge_max`='$recharge_max',`member`='$member', `agent`='$agent' WHERE id=$id");
         
             return redirect()->route('first.deposit.bonus');
          
      }
	
	
	
}