<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Models\{User,MlmLevel,Payin};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Http;


 
class AgencyPromotionController extends Controller
{
	
public function promotion_data($id) 
{
    try {
        $user = User::findOrFail($id);
        $currentDate = Carbon::now()->subDay()->format('Y-m-d');
        //dd($currentDate);
        $directSubordinateCount = $user->referrals()->count();
        $totalCommission = $user->commission;
        $referralCode = $user->referral_code;
        $yesterdayTotalCommission = $user->yesterday_total_commission;

        $teamSubordinateCount = $user->getAllSubordinatesCount();
		
		

        $register = User::where('referrer_id', $user->id)
                        ->whereDate('created_at', $currentDate)
                        ->count();

        // Fetch deposit statistics
        $depositStats = $user->payins()
            ->whereDate('created_at', $currentDate)
            ->selectRaw('COUNT(id) as deposit_number, SUM(cash) as deposit_amount')->first();
            //->toRawSql();
            
            //dd($depositStats);
  // Adjusted this query to remove 'salary_first_recharge' condition
        $firstDepositCount = $user->payins()
            ->whereDate('created_at', $currentDate)
            ->count();  // Removed the 'salary_first_recharge' condition

        $subordinatesRegister = User::where('referrer_id', $user->id)
            ->whereDate('created_at', $currentDate)
            ->count();

        // Subordinate deposit data
        $subordinatesDeposit = $user->payins()
            ->whereDate('created_at', $currentDate)
            ->selectRaw('COUNT(id) as deposit_number, SUM(cash) as deposit_amount')
            ->first();

        // Adjusted this query to remove 'salary_first_recharge' condition
        $subordinatesFirstDepositCount = $user->payins()
            ->whereDate('created_at', $currentDate)
            ->count();  // Removed the 'salary_first_recharge' condition

        // Result array to return
        $result = [
            'yesterday_total_commission' => $yesterdayTotalCommission ?? 0,
            'register' => $register,
            'deposit_number' => $depositStats->deposit_number ?? 0,
            'deposit_amount' => $depositStats->deposit_amount ?? 0,
            'first_deposit' => $firstDepositCount,
            'subordinates_register' => $subordinatesRegister,
            'subordinates_deposit_number' => $subordinatesDeposit->deposit_number ?? 0,
            'subordinates_deposit_amount' => $subordinatesDeposit->deposit_amount ?? 0,
            'subordinates_first_deposit' => $subordinatesFirstDepositCount,
            'direct_subordinate' => $directSubordinateCount,
            'total_commission' => $totalCommission,
			'weekly_commission'=>0,
            'team_subordinate' => $teamSubordinateCount,
            'referral_code' => $referralCode,
        ];

        return response()->json(['status' => 200,'message' => 'data fetch successfully','data' =>$result], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

	 public function new_subordinate(Request $request)
{
    try {
        // Validation
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 200);
        }

        // Find the user using Eloquent Model
        $user = User::findOrFail($request->id);

        // Get the current, yesterday's, start and end of month dates
        $currentDate = Carbon::now()->format('Y-m-d');
        $yesterdayDate = Carbon::yesterday()->format('Y-m-d');
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Initialize the query for subordinates
        $query = User::select('mobile','u_id','commission', 'name', 'created_at')
            ->where('referrer_id', $user->id);
            
        switch ($request->type) {
            case 1:
                // Today's subordinates
                $query->whereDate('created_at', $currentDate);
                break;
            case 2:
                // Yesterday's subordinates
                $query->whereDate('created_at', $yesterdayDate);
                break;
            case 3:
                // Subordinates for this month
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                break;
            default:
                return response()->json(['status' => 400, 'message' => 'Invalid type provided'], 200);
        }
        $subordinate_data = $query->get();

        // Return success or error based on whether data exists
        if ($subordinate_data->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved subordinates!',
                'data' => $subordinate_data
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Data not found'
            ], 200);
        }
    } catch (\Exception $e) {
        // Handle any exceptions
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
 
 public function tier(){
    try {
        // Fetch all levels using the MlmLevel model
        $tier = MlmLevel::select('id', 'name')->get();

        if ($tier->isNotEmpty()) {
            $response = [
                'status' => 200,
                'message' => 'Successfully..!', 
                'data' => $tier
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'status' => 400, 
                'message' => 'Data not found'
            ];
            return response()->json($response, 400);
        }

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function subordinate_data(Request $request)
{
    try {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'tier' => 'required|integer|min:0',
        ]);

        // Stop on first failure
        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }

        // Get input parameters
        $userId = $request->id;
        $tier = $request->tier;
        $searchUid = $request->u_id;
        $currentDate = $request->created_at ?: Carbon::now()->subDay()->format('Y-m-d');

        // Step 1: Initialize a collection to store subordinates
        $subordinates = collect();

        // Step 2: Get the initial users at level 1 (direct referrals)
        $currentLevelUsers = User::where('referrer_id', $userId)->get();
        $currentLevel = 1; // Start at level 1

        // Step 3: Iterate through each level to get subordinates up to the given tier
        while ($currentLevelUsers->isNotEmpty() && $currentLevel <= $tier) {
            // Merge current level users into the subordinates collection
            $subordinates = $subordinates->merge($currentLevelUsers);

            // Get the next level users (users referred by the current level users)
            $currentLevelUsers = User::whereIn('referrer_id', $currentLevelUsers->pluck('id'))->get();
            
            $currentLevel++; // Increment the level
        }

        // Get all subordinate user IDs
        $subordinateIds = $subordinates->pluck('id');

        // Step 4: If there is a search_uid, filter the subordinates by UID
        if (!empty($searchUid)) {
            $subordinateIds = User::whereIn('id', $subordinateIds)
                                  ->where('u_id', 'like', $searchUid . '%')
                                  ->pluck('id');
        }

        // Step 5: Fetch data for the filtered subordinates
        $subordinatesData = User::whereIn('id', $subordinateIds)
            ->with(['bets' => function ($query) use ($currentDate) {
                $query->whereDate('created_at', $currentDate);
            }, 'payins' => function ($query) use ($currentDate) {
                $query->whereDate('created_at', $currentDate)
                      ->where('status', 2);
            }, 'mlmLevel'])
            ->get();

        // Step 6: Initialize the result array
        $result = [
            'number_of_deposit' => 0,
            'payin_amount' => 0,
            'number_of_bettor' => 0,
            'bet_amount' => 0,
            'first_deposit' => 0,
            'first_deposit_amount' => 0,
            'subordinates_data' => [],
        ];

        // Step 7: Calculate data for each subordinate
        foreach ($subordinatesData as $user) {
            // Calculate bet amount and payin amount
            $betAmount = $user->bets->sum('amount');
            $payinAmount = $user->payins->sum('cash');
            $numberOfBettors = $user->bets->count();
            $commission = ($betAmount * optional($user->mlmLevel)->commission) / 100;

            // Add to result totals
            $result['bet_amount'] += $betAmount;
            $result['payin_amount'] += $payinAmount;
            $result['number_of_bettor'] += $numberOfBettors;

            // Add individual subordinate data to the result
            $result['subordinates_data'][] = [
                'id' => $user->id,
                'u_id' => $user->u_id,
                'bet_amount' => $betAmount,
                'payin_amount' => $payinAmount,
                'commission' => $commission,
            ];
        }

        // Step 8: Return the result as JSON
        return response()->json(['status' => 200,'message' => 'data fetch successfully','data' =>$result], 200);

    } catch (\Exception $e) {
        // Return error message in case of exception
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

 
	public function turnover_new_old()
{
    // Get the current datetime and the date for the previous day
    $datetime = Carbon::now();
    $currentDate = Carbon::now()->subDay()->format('Y-m-d');
    
    // Reset yesterday's total commission to 0 for all users
    DB::table('users')->update(['yesterday_total_commission' => 0]);

    // Fetch users who have a referral_user_id (i.e., have a referrer)
    $referralUsers = DB::table('users')->whereNotNull('referrer_id')->get();
    //dd($referralUsers);
    $referralUsersCount = $referralUsers->count();

    // Check if there are any referral users
    if ($referralUsersCount > 0) {

        foreach ($referralUsers as $referralUser) {
            $user_id = $referralUser->id;
			//return $user_id;
            $maxTier = 6;

            // Recursive query to fetch all subordinates within the maxTier levels
            $subordinatesData = \DB::select("
                WITH RECURSIVE subordinates AS (
                    -- Base case: Start from users directly referred by the current user
                    SELECT id, referrer_id, 1 AS level
                    FROM users
                    WHERE referrer_id = ?
                    UNION ALL
                    -- Recursive case: Get users referred by users in the previous level
                    SELECT u.id, u.referrer_id, s.level + 1
                    FROM users u
                    INNER JOIN subordinates s ON s.id = u.referrer_id
                    WHERE s.level + 1 <= ?
                )
                SELECT 
                    users.id, 
                    subordinates.level,
                    COALESCE(SUM(bet_summary.total_bet_amount), 0) AS bet_amount,
                    COALESCE(SUM(bet_summary.total_bet_amount), 0) * COALESCE(level_commissions.commission, 0) / 100 AS commission
                FROM users
                LEFT JOIN (
                    -- Sum bet amounts for each user for the previous day
                    SELECT userid, SUM(amount) AS total_bet_amount 
                    FROM bets 
                    WHERE created_at LIKE ?
                    GROUP BY userid
                ) AS bet_summary ON users.id = bet_summary.userid 
                LEFT JOIN subordinates ON users.id = subordinates.id
                LEFT JOIN (
                    -- Commission rates for each level
                    SELECT id, commission
                    FROM mlm_levels
                ) AS level_commissions ON subordinates.level = level_commissions.id
                WHERE subordinates.level <= ?
                GROUP BY users.id, subordinates.level, level_commissions.commission;
            ", [$user_id, $maxTier, $currentDate . '%', $maxTier]);
            return $subordinatesData;
            $totalCommission = 0;

            // Calculate the total commission for the user by summing all subordinate commissions
            foreach ($subordinatesData as $data) {
                $totalCommission += $data->commission;
            }

            // Update the user's wallet, commission, and yesterday's total commission
            DB::table('users')->where('id', $user_id)->update([
                'wallet' => DB::raw('wallet + ' . $totalCommission),
                'commission' => DB::raw('commission + ' . $totalCommission),
                'yesterday_total_commission' => $totalCommission,
                'updated_at' => $datetime,
            ]);

            // Insert a record into the wallet history for the user's commission
            DB::table('wallet_history')->insert([
                'userid' => $user_id,
                'amount' => $totalCommission,
                'subtypeid' => 26, // Assuming 26 is the subtype for commission
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }

    } else {
        return response()->json(['message' => 'No referral users found.'], 400);
    }
}

	public function turnover_new()
{
    // Get the current datetime and the date for the previous day
    $datetime = Carbon::now();
    $currentDate = Carbon::now()->subDay()->format('Y-m-d');
    
    // Reset yesterday's total commission to 0 for all users
    DB::table('users')->update(['yesterday_total_commission' => 0]);

    // Fetch users who have a referral_user_id (i.e., have a referrer)
    $referralUsers = DB::table('users')->whereNotNull('referrer_id')->get();
    //dd($referralUsers);
    $referralUsersCount = $referralUsers->count();
     //dd($referralUsersCount);
    // Check if there are any referral users
    if ($referralUsersCount > 0) {

        foreach ($referralUsers as $referralUser) {
            $user_id = $referralUser->id;
            $maxTier = 10;

            // Recursive query to fetch all subordinates within the maxTier levels
            $subordinatesData = \DB::select("
                WITH RECURSIVE subordinates AS (
                    -- Base case: Start from users directly referred by the current user
                    SELECT id, referrer_id, 1 AS level
                    FROM users
                    WHERE referrer_id = ?
                    UNION ALL
                    -- Recursive case: Get users referred by users in the previous level
                    SELECT u.id, u.referrer_id, s.level + 1
                    FROM users u
                    INNER JOIN subordinates s ON s.id = u.referrer_id
                    WHERE s.level + 1 <= ?
                )
                SELECT 
                    users.id, 
                    subordinates.level,
                    COALESCE(SUM(bet_summary.total_bet_amount), 0) AS bet_amount,
                    COALESCE(SUM(bet_summary.total_bet_amount), 0) * COALESCE(level_commissions.commission, 0) / 100 AS commission
                FROM users
                LEFT JOIN (
                    -- Sum bet amounts for each user for the previous day
                    SELECT userid, SUM(amount) AS total_bet_amount 
                    FROM bets 
                    WHERE created_at LIKE ?
                    GROUP BY userid
                ) AS bet_summary ON users.id = bet_summary.userid 
                LEFT JOIN subordinates ON users.id = subordinates.id
                LEFT JOIN (
                    -- Commission rates for each level
                    SELECT id, commission
                    FROM mlm_levels
                ) AS level_commissions ON subordinates.level = level_commissions.id
                WHERE subordinates.level <= ?
                GROUP BY users.id, subordinates.level, level_commissions.commission;
            ", [$user_id, $maxTier, $currentDate . '%', $maxTier]);
			//dd($user_id,$maxTier,$currentDate,$maxTier);
            return $subordinatesData;
            $totalCommission = 0;

            // Calculate the total commission for the user by summing all subordinate commissions
            foreach ($subordinatesData as $data) {
                $totalCommission += $data->commission;
            }

            // Update the user's wallet, commission, and yesterday's total commission
            DB::table('users')->where('id', $user_id)->update([
                'wallet' => DB::raw('wallet + ' . $totalCommission),
                'commission' => DB::raw('commission + ' . $totalCommission),
                'yesterday_total_commission' => $totalCommission,
                'updated_at' => $datetime,
            ]);

            // Insert a record into the wallet history for the user's commission
            DB::table('wallet_histories')->insert([
                'user_id' => $user_id,
                'amount' => $totalCommission,
                'type_id' => 26, // Assuming 26 is the subtype for commission
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }

    } else {
        return response()->json(['message' => 'No referral users found.'], 400);
    }
}
	
	
}