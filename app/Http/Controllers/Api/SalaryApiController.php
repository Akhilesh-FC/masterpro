<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Models\{User,AviatorSalary,Payin,Bet,WalletHistory};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


use Illuminate\Support\Facades\Http;


 
class SalaryApiController extends Controller
{


public function aviator_salary()
{
    $kolkataTime = Carbon::now('Asia/Kolkata');
    $formattedTime = $kolkataTime->toDateTimeString();
   
    $qulifyUserid = []; 
    
    $users = User::whereNotNull('referrer_id')->where('status', 1)->get();
    
    foreach ($users as $user) {
        $id = $user['id'];  
      
        $referrerId = $user['referrer_id']; 
    
        $allUserBetAmount = Bet::where('userid', $id)->sum('amount');
    
        if ($allUserBetAmount >= 5000) {
            $refer_users = User::where('referrer_id', $id)->get();
     
            foreach ($refer_users as $ref_user) { 
                $ref_id = $ref_user['id'];  
                
                $user_recharge_amount = Payin::where('status', 2)
                    ->where('user_id', $ref_id)
                    ->sum('cash');
    
                $refUserBetAmount = Bet::where('userid', $ref_id)->sum('amount');
                
                if ($user_recharge_amount >= 1000 && $refUserBetAmount >= 5000) {
                    $qulifyUserid[] = $ref_id; 
                }
            }
        }
    }
    $qualifiedUserCount = count($qulifyUserid);
    
   $aviatorSalaries = AviatorSalary::all();

foreach ($aviatorSalaries as $aviatorSalary) {

    $activeMembers = $aviatorSalary['active_members'];
    $dailyFixSalary = $aviatorSalary['daily_fix_salary'];
    
    if ($activeMembers == $qualifiedUserCount) {
  
    User::where('id', $id)->update(['wallet' => $dailyFixSalary]);
    WalletHistory::create([
        'user_id' => $id, 
        'amount' => $dailyFixSalary, 
        'type_id' => 1, 
        'description' => 'Aviator salary',
        'created_at' => $formattedTime,
        'updated_at' => $formattedTime
    ]);
                }
    
}

}



public function dailyBonus(Request $request)
{
   
    $yesterdayDate = Carbon::now()->subDay()->toDateString();
	$todayDate = Carbon::now('Asia/Kolkata');

    $users = User::whereNotNull('referrer_id')
        ->where('status', 1)
        ->get();
     
    foreach ($users as $user) {
        $id = $user->id; 
	
        $referredUsers = User::where('referrer_id', $id)->pluck('id');
    
        $referredUsersArray = $referredUsers->toArray();
       
        if (empty($referredUsersArray)) {
            continue;
        }

        $totalDeposit = DB::table('payins')
            ->whereIn('user_id', $referredUsersArray)
            ->whereDate('created_at', '=', $yesterdayDate)
            ->where('status', 2) 
            ->sum('cash');
 
        $referredCount = count($referredUsersArray);

        $bonus = DB::table('daily_salaries')
            ->where('invite_people', '<=', $referredCount) 
            ->where('deposit', '<=', $totalDeposit) 
            ->orderByDesc('invite_people')
            ->orderByDesc('deposit')
            ->limit(1)
            ->value('bonus');
 
        if ($bonus) {
		
              DB::table('salary')->insert([
                'user_id' => $user->id,
                'salary' => $bonus,
                'salary_type' => 1,
                'created_at' => $todayDate,
                'updated_at' =>$todayDate,
            ]);

        } 
	}
    return response()->json(['message' => 'Daily bonus distributed successfully based on yesterday\'s deposits']);
}


	public function monthlyBonus(Request $request)
{
    $firstDayOfMonth = Carbon::now()->startOfMonth()->toDateString();
    $lastDayOfMonth = Carbon::now()->endOfMonth()->toDateString();
		$todayDate = Carbon::now('Asia/Kolkata');
		
    $users = User::whereNotNull('referrer_id')
        ->where('status', 1)
        ->get();

    foreach ($users as $user) {
        $id = $user->id; 
        $referredUsers = User::where('referrer_id', $id)->pluck('id');
      
        $referredUsersArray = $referredUsers->toArray();
      
        if (empty($referredUsersArray)) {
            continue;
        }

        $totalDeposit = DB::table('payins')
            ->whereIn('user_id', $referredUsersArray)
            ->whereBetween('created_at', [$firstDayOfMonth, $lastDayOfMonth])
            ->where('status', 2) 
            ->sum('cash');
        
        $referredCount = count($referredUsersArray);
       
        $bonus = DB::table('monthly_salaries')
            ->where('invite_people', '<=', $referredCount) 
            ->where('deposit', '<=', $totalDeposit) 
            ->orderByDesc('invite_people')
            ->orderByDesc('deposit')
            ->limit(1)
            ->value('bonus');
   
        if ($bonus) {
            DB::table('salary')->insert([
                'user_id' => $user->id, 
                'salary' => $bonus,
                'salary_type' => 2, 
                'created_at' => $todayDate,
                'updated_at' => $todayDate,
            ]);
        }
    }

    return response()->json(['message' => 'Monthly bonus distributed successfully based on this month\'s deposits']);
}

	
}