<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\User;

class UsdtDepositController extends Controller
{
    public function usdt_deposit_index(string $id)
    {

         $deposits= DB::select("SELECT payins.*,users.name AS uname,users.id As userid, users.mobile As mobile FROM `payins` LEFT JOIN 
users ON payins.user_id=users.id WHERE payins.status = '$id' && payins.type=2");

        return view('usdt_deposit.deposit')->with('deposits',$deposits)->with('id',$id);
    }
  

public function usdt_success(string $id) {
    // Fetch the details
    $details = DB::table('payins')->where('id', $id)->first();
	
    // Check if details exist
    if (!$details) {
        return redirect()->back()->with('error', 'Payin details not found.');
    }
	$userid = $details->user_id;
	
	if($details->type==3){
    	$amount = $details->usdt_amount;
	}elseif($details->type==2){
		$amount = $details->cash;
	}
	
	$userdata = DB::table('users')->where('id',$userid)->where('status',1)->first();
	 if (!$userdata) {
        return redirect()->back()->with('error', 'User block by Admin');
    }
	$first_recharge = $userdata->first_recharge;
	
    $users = User::where('id',$details->user_id)->first();
    if(!$users){
        return redirect()->back()->with('error', 'user not found.');
    }
		  $referral_user = DB::table('users')->where('id', $userid)->value('referrer_id');
			  
		  
			 if($first_recharge == '0'){
					 
				 $first_deposit_bonus = DB::table('first_deposit_bonus')
    					->where('recharge_min', '<=', $amount)
    					->where('recharge_max', '>=', $amount)
    					->first();
				 
				 $first_deposit_bonus->member;
				 $first_deposit_bonus->agent;
				
			 	 if($amount >= 200){ $first_recharge_status = 1; } else { $first_recharge_status = 0;}
				
				 $data2 = DB::table('users')
    				->where('id', $userid)
    				->update([
        					'wallet' => DB::raw("wallet + $amount + $first_deposit_bonus->member"),	
						    'recharge' => DB::raw("recharge + $amount + $first_deposit_bonus->member"),	
						    'first_recharge' => $first_recharge_status
						  
    					]);
				 
				   DB::table('users')
    				->where('id', $referral_user)
    				->update([
        					'wallet' => DB::raw("wallet + $first_deposit_bonus->agent"),
						    'recharge' => DB::raw("recharge + $first_deposit_bonus->agent"),
    					]);

					 
	  $insert= DB::table('wallet_histories')->insert([
        'user_id' => $userid,
        'amount' => $first_deposit_bonus->member,
        'type_id' => 10,
		'created_at'=> now(),
        'updated_at' => now()
		
    ]);
				 
	$insert= DB::table('wallet_histories')->insert([
        'user_id' => $referral_user,
        'amount' => $first_deposit_bonus->agent,
        'type_id' => 10,
		'created_at'=> now(),
        'updated_at' => now()
		
    ]);
 

    // Update the payin status
    DB::table('payins')->where('id', $id)->update([
        'status' => 2
    ]);

    // Insert into wallet history
    DB::table('wallet_histories')->insert([
        'user_id' => $userid,
        'amount' => $amount,
        'type_id' => 26
    ]);


    return redirect()->back()->with('success', 'Successfully Updated.');
}elseif($first_recharge == '1'){
				 
				 
				 
			   $data2 = DB::table('users')->where('id', $userid)
				   	->update([
        					'wallet' => DB::raw("wallet + $amount"),	
						    'recharge' => DB::raw("recharge + $amount")
    					]);
    					
    					
 DB::table('payins')->where('id', $id)->update([
        'status' => 2
    ]);
				
			return redirect()->back()->with('success', 'Successfully Updated.');
		
				 
 }
			 }

 public function usdt_reject(string $id){

                DB::table('payins')->where('id', $id)->update([
                        'status' => 3
                ]);

                return redirect()->back()->with('success', 'Successfully Updated.');
        }
        
        
        
        // offline payment
        
         public function offline_deposit_index(string $id)
    {

         $deposits= DB::select("SELECT payins.*,users.username AS uname,users.id As userid, users.mobile As mobile FROM `payins` LEFT JOIN 
users ON payins.user_id=users.id WHERE payins.status = '$id' && payins.type=3");

        return view('usdt_deposit.deposit')->with('deposits',$deposits)->with('id',$id);
    }


}

