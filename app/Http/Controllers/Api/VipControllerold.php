<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DateTime; 
use DateInterval; 

use Illuminate\Support\Facades\DB;


class VipController extends Controller
{
	
public function vip_level(Request $request)
{
    date_default_timezone_set('Asia/Kolkata');
    $currentDateTime = now();
    $currentDate = $currentDateTime->format('Y-m-d');

    $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric'
    ])->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    $userid = $request->userid;

    // Calculate experience in months and days
    $createdDate = DB::table('users')->where('id', $userid)->value('created_at');
    $createdAt = new DateTime($createdDate);
    $interval = $createdAt->diff(new DateTime($currentDateTime));
    $totalMonths = ($interval->y * 12) + $interval->m;
    $totalDays = $interval->d;

    // Calculate bet amounts
    //$aviatorAmount = DB::table('aviator_bet')->where('uid', $userid)->sum('amount');
    
    $wdhAmount = DB::table('bets')->where('userid', $userid)->sum('amount');
    $mineAmount = DB::table('mine_game_bets')->where('userid', $userid)->sum('amount');
   
    //$betAmount = $aviatorAmount + $wdhAmount + $mineAmount;
    $betAmount = $wdhAmount + $mineAmount;

    // Fetch VIP level details
    $activityRewards = DB::table('vip_levels')
        ->leftJoin('vip_levels_claim', function ($join) use ($userid) {
            $join->on('vip_levels.id', '=', 'vip_levels_claim.vip_levels_id')
                 ->where('vip_levels_claim.userid', '=', $userid);
        })
        ->select(
            'vip_levels.*',
            'vip_levels_claim.level_up_status',
            'vip_levels_claim.monthly_rewards_status',
            'vip_levels_claim.rebate_rate_status',
            DB::raw("COALESCE(vip_levels_claim.status, '0') AS claim_status"),
            DB::raw("COALESCE(vip_levels.created_at, 'Not Found') AS created_at")
        )
        ->orderBy('vip_levels.id')
        ->limit(10)
        ->get();

    $data = [];
    foreach ($activityRewards as $item) {
        $betRange = $item->betting_range;
        $percentage = $betRange ? number_format(($betAmount / $betRange) * 100, 2) : 0;

        // Check and insert into vip_levels_claim if not existing
        $checkExist = DB::table('vip_levels_claim')
                        ->where('userid', $userid)
                        ->where('vip_levels_id', $item->id)
                        ->first();
        
        if (!$checkExist && $betAmount >= $betRange) {
            DB::table('vip_levels_claim')->insert([
                'userid' => $userid,
                'vip_levels_id' => $item->id,
                'status' => 1,
                'level_up_status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $data[] = [
            'id' => $item->id,
            'name' => $item->name,
            'range_amount' => (int)$betRange,
            'level_up_rewards' => $item->level_up_rewards,
            'monthly_rewards' => $item->monthly_rewards,
            'rebate_rate' => $item->rebate_rate,
            'status' => (int)$item->claim_status,
            'level_up_status' => $item->level_up_status ?? 0,
            'monthly_rewards_status' => $item->monthly_rewards_status ?? 0,
            'rebate_rate_status' => $item->rebate_rate_status ?? 0,
            'bet_amount' => $betAmount,
            //'percentage' => (double)$percentage,
			'percentage' => number_format((double)$percentage, 2, '.', '0'),
		    'created_at' => $item->created_at,
            'updated_at' => $item->updated_at
        ];
    }

    if ($activityRewards->isNotEmpty()) {
        return response()->json([
            'message' => 'VIP Level List',
            'status' => 200,
            'days_count' => $totalDays ?? 0,
            'my_experience' => $totalMonths ?? 0,
            'data' => $data
        ]);
    } else {
        return response()->json([
            'message' => 'Not found..!',
            'status' => 400,
            'data' => []
        ], 400);
    }
}



 //// vip Level History ////
   public function vip_level_history(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        // 'limit' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }

    $userid = $request->userid;
    $limit = $request->limit ?? 10000;
    $offset = $request->offset ?? 0;
    $from_date = $request->created_at ?? null;
    $to_date = $request->created_at ?? null;

    $whereClauses = [];
    $params = [];

    // Add userid condition
    if (!empty($userid)) {
        $whereClauses[] = 'vip_levels_claim.userid = ?';
        $params[] = $userid;
    }

    // Add date conditions if both dates are provided
    if (!empty($from_date) && !empty($to_date)) {
        $whereClauses[] = 'vip_levels_claim.created_at BETWEEN ? AND ?';
        $params[] = $from_date;
        $params[] = $to_date;
    }

    // Prepare the base query
    $query = "SELECT `exp`, `created_at` FROM `vip_levels_claim`";

    // Append where clauses if any
    if (!empty($whereClauses)) {
        $query .= " WHERE " . implode(" AND ", $whereClauses);
    }

    // Append order and limit
    $query .= " ORDER BY `id` DESC LIMIT ?, ?";
    $params[] = $offset; // for the OFFSET
    $params[] = $limit;  // for the LIMIT

    // Execute the query with parameters
    $results = DB::select($query, $params);

    // Return the response based on results
    if (!empty($results)) {
        return response()->json([
            'status' => 200,
            'message' => 'Data found',
            'data' => $results
        ]);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'No Data found',
            'data' => []
        ]);
    }
}

public function receive_money(Request $request)
{
    // Validate the incoming request
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'level_id' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    // Extracting inputs
    $userid = $request->input('userid');
    $level_id = $request->input('level_id');
    $level_up_reward = $request->input('level_up_rewards');
    $monthly_rewards = $request->input('monthly_rewards');
    $datetime = now();

    // Check if the user has already claimed the rewards
    $check_exist = DB::table('vip_levels_claim')
        ->where('userid', $userid)
        ->where('vip_levels_id', $level_id)
        ->where('level_up_status', 0)
        ->first();

    if ($check_exist) {
        return response()->json(['message' => 'Already claimed!', 'status' => 400]);
    }

    // Determine the reward type and perform the necessary operations
    if (!empty($level_up_reward) && $level_up_reward > 0) {
        // Level up reward handling
        $this->handleReward($userid, $level_up_reward, 10, $datetime);
        
        DB::table('vip_levels_claim')
            ->where('userid', $userid)
            ->where('vip_levels_id', $level_id)
            ->update(['level_up_status' => 0]);
    } else {
        // Monthly reward handling
        $this->handleReward($userid, $monthly_rewards, 11, $datetime);
        
        DB::table('vip_levels_claim')
            ->where('userid', $userid)
            ->where('vip_levels_id', $level_id)
            ->update(['monthly_rewards_status' => 0]);
    }

    return response()->json(['message' => 'Added Successfully', 'status' => 200], 200);
}

// Helper function to handle wallet updates
private function handleReward($userid, $amount, $subtypeid, $datetime)
{
    // Insert into wallet history
    DB::table('wallet_histories')->insert([
        'user_id' => $userid,
        'amount' => $amount,
        'type_id' => $subtypeid,
        'created_at' => $datetime,
        'updated_at' => $datetime
    ]);

    // Update user wallet and recharge amounts
    DB::table('users')
        ->where('id', $userid)
        ->update([
            'wallet' => DB::raw('wallet + ' . $amount),
            'recharge' => DB::raw('recharge + ' . $amount)
        ]);
}


}
