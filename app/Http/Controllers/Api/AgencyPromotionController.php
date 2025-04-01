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
		
		
        	$teamSubordinateCount =DB::select("
        WITH RECURSIVE subordinates AS (
            SELECT id FROM users WHERE referrer_id = ?
            UNION ALL
            SELECT u.id FROM users u INNER JOIN subordinates s ON u.referrer_id = s.id
        )
        SELECT COUNT(*) as total FROM subordinates
    ",  [$user->id]); 

		
$teamSubordinateCount = $teamSubordinateQuery->total ?? 0;

        $register = User::where('referrer_id', $user->id)
                        ->whereDate('created_at', $currentDate)
                        ->count();


$depositStats = DB::selectOne("
    SELECT COUNT(p.id) AS deposit_number, SUM(p.cash) AS deposit_amount
    FROM payins p
    WHERE p.user_id IN (
        SELECT id FROM users WHERE referrer_id = ?
    )
    AND DATE(p.created_at) = ?
", [$user->id, $currentDate]);


//dd($depositStats);

// Agar aapko count aur amount access karna ho:
$depositNumber = $depositStats->deposit_number;
$depositAmount = $depositStats->deposit_amount;

           
    
    $firstDepositCount = DB::table('payins')
    ->whereIn('user_id', function($query) use ($user) {
        $query->select('id')->from('users')->where('referrer_id', $user->id);
    })
    ->whereDate('created_at', $currentDate)
    ->distinct('user_id')
    ->count('user_id');

//dd($firstDepositCount);


      $subordinatesRegister = DB::selectOne("
    WITH RECURSIVE Subordinates AS (
        SELECT id, referrer_id, 1 AS level
        FROM users
        WHERE referrer_id = ?  
        AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        UNION ALL
        SELECT u.id, u.referrer_id, s.level + 1
        FROM users u
        INNER JOIN Subordinates s ON u.referrer_id = s.id
        WHERE DATE(u.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    )
    SELECT COUNT(*) AS count FROM Subordinates
", [$user->id]);

 // Subordinate register count on the current day
       $subordinatesRegisters = DB::select("SELECT COUNT(*) as count FROM users WHERE referrer_id IN ( SELECT id FROM users WHERE referrer_id = $user->id ) AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY;");
//dd($subordinatesRegister);
// Direct integer value extract karna
$totalCount = $subordinatesRegister->count;


        // Fetch referred users (subordinates)
        $referUserIds = DB::table('users')
            ->where('referrer_id', $user->id)
            ->pluck('id');  // Get the ids of all referred users


         

// $subordinatesDeposit = DB::select("
//     WITH RECURSIVE subordinates AS (
//         SELECT id, referrer_id, 1 AS level
//         FROM users
//         WHERE referrer_id = ?
//         UNION ALL
//         SELECT u.id, u.referrer_id, s.level + 1
//         FROM users u
//         INNER JOIN subordinates s ON s.id = u.referrer_id
//         WHERE s.level <= 7
//     )
//     SELECT COUNT(p.id) as deposit_number, 
//           SUM(p.cash) as deposit_amount 
//     FROM payins p
//     JOIN subordinates s ON p.user_id = s.id
//     WHERE p.created_at LIKE ?
//     AND p.status = 2
// ", [$user->id, $currentDate . '%']);

// Aggregate deposit data for all referred users (subordinates)
        if ($referUserIds->isNotEmpty()) {
            $subordinatesDeposit = DB::select("WITH RECURSIVE team_members AS (
    SELECT id
    FROM users
    WHERE referrer_id = $user->id  -- starting from the user whose team we're analyzing
    UNION
    SELECT u.id
    FROM users u
    JOIN team_members tm ON tm.id = u.referrer_id  -- recursively include all team members' downlines
)
SELECT COUNT(*) AS deposit_number, SUM(cash) AS deposit_amount
FROM payins
WHERE user_id IN (SELECT id FROM team_members)  -- consider deposits made by the entire team
AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY
AND status = 2;
");
}


$depositNumber = $subordinatesDeposit[0]->deposit_number ?? 0;
$depositAmount = $subordinatesDeposit[0]->deposit_amount ?? 0;
//dd($subordinatesDeposit);

        $subordinatesFirstDepositCount =// Get first deposit count for subordinates
            $totalFirstDepositCount = DB::table('payins')
                ->whereIn('user_id', $referUserIds)
                ->whereDate('created_at', $currentDate)
                ->count();
        
//dd($subordinatesFirstDepositCount);
        // Result array to return
        $result = [
            'yesterday_total_commission' => $yesterdayTotalCommission ?? 0,
            'register' => $register,
            'deposit_number' => $depositStats->deposit_number ?? 0,
            'deposit_amount' => $depositStats->deposit_amount ?? 0,
            'first_deposit' => $firstDepositCount,
            'subordinates_register' => $totalCount,
            'subordinates_deposit_number' => $depositNumber ?? 0,
            'subordinates_deposit_amount' => $depositAmount ?? 0,
            'subordinates_first_deposit' => $subordinatesFirstDepositCount,
            'direct_subordinate' => $directSubordinateCount,
            'total_commission' => $totalCommission,
			'weekly_commission'=>0,
            'team_subordinate' => $teamSubordinateCount,
            'referral_code' => $referralCode,
        ];
        //dd($subordinatesDeposit);
       // dd($depositNumber);

        return response()->json(['status' => 200,'message' => 'data fetch successfully','data' =>$result], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

	
// public function promotion_data($id) 
// {
//     try {
//         $user = User::findOrFail($id);
//         $currentDate = Carbon::now()->subDay()->format('Y-m-d');
//         //dd($currentDate);
//         $directSubordinateCount = $user->referrals()->count();
//         $totalCommission = $user->commission;
//         $referralCode = $user->referral_code;
//         $yesterdayTotalCommission = $user->yesterday_total_commission;

//         //$teamSubordinateCount = $user->getAllSubordinatesCount();
		
		
//         	$teamSubordinateCount =DB::select("
//         WITH RECURSIVE subordinates AS (
//             SELECT id FROM users WHERE referrer_id = ?
//             UNION ALL
//             SELECT u.id FROM users u INNER JOIN subordinates s ON u.referrer_id = s.id
//         )
//         SELECT COUNT(*) as total FROM subordinates
//     ",  [$user->id]); 

		
// $teamSubordinateCount = $teamSubordinateQuery->total ?? 0;

//         $register = User::where('referrer_id', $user->id)
//                         ->whereDate('created_at', $currentDate)
//                         ->count();


// $depositStats = DB::selectOne("
//     SELECT COUNT(p.id) AS deposit_number, SUM(p.cash) AS deposit_amount
//     FROM payins p
//     WHERE p.user_id IN (
//         SELECT id FROM users WHERE referrer_id = ?
//     )
//     AND DATE(p.created_at) = ?
// ", [$user->id, $currentDate]);


// //dd($depositStats);

// // Agar aapko count aur amount access karna ho:
// $depositNumber = $depositStats->deposit_number;
// $depositAmount = $depositStats->deposit_amount;

           
    
//     $firstDepositCount = DB::table('payins')
//     ->whereIn('user_id', function($query) use ($user) {
//         $query->select('id')->from('users')->where('referrer_id', $user->id);
//     })
//     ->whereDate('created_at', $currentDate)
//     ->distinct('user_id')
//     ->count('user_id');

// //dd($firstDepositCount);


//       $subordinatesRegister = DB::selectOne("
//     WITH RECURSIVE Subordinates AS (
//         SELECT id, referrer_id, 1 AS level
//         FROM users
//         WHERE referrer_id = ?  
//         AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
//         UNION ALL
//         SELECT u.id, u.referrer_id, s.level + 1
//         FROM users u
//         INNER JOIN Subordinates s ON u.referrer_id = s.id
//         WHERE DATE(u.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
//     )
//     SELECT COUNT(*) AS count FROM Subordinates
// ", [$user->id]);
// //dd($subordinatesRegister);
// // Direct integer value extract karna
// $totalCount = $subordinatesRegister->count;

         

// $subordinatesDeposit = DB::select("
//     WITH RECURSIVE subordinates AS (
//         SELECT id, referrer_id, 1 AS level
//         FROM users
//         WHERE referrer_id = ?
//         UNION ALL
//         SELECT u.id, u.referrer_id, s.level + 1
//         FROM users u
//         INNER JOIN subordinates s ON s.id = u.referrer_id
//         WHERE s.level <= 7
//     )
//     SELECT COUNT(p.id) as deposit_number, 
//           SUM(p.cash) as deposit_amount 
//     FROM payins p
//     JOIN subordinates s ON p.user_id = s.id
//     WHERE p.created_at LIKE ?
//     AND p.status = 2
// ", [$user->id, $currentDate . '%']);

// $depositNumber = $subordinatesDeposit[0]->deposit_number ?? 0;
// $depositAmount = $subordinatesDeposit[0]->deposit_amount ?? 0;


//         $subordinatesFirstDepositCount = DB::table('payins')
//     ->where('user_id', $user->id)
//     ->count();

//         // Result array to return
//         $result = [
//             'yesterday_total_commission' => $yesterdayTotalCommission ?? 0,
//             'register' => $register,
//             'deposit_number' => $depositStats->deposit_number ?? 0,
//             'deposit_amount' => $depositStats->deposit_amount ?? 0,
//             'first_deposit' => $firstDepositCount,
//             'subordinates_register' => $totalCount,
//             'subordinates_deposit_number' => $subordinatesDeposit->deposit_number ?? 0,
//             'subordinates_deposit_amount' => $subordinatesDeposit->deposit_amount ?? 0,
//             'subordinates_first_deposit' => $subordinatesFirstDepositCount,
//             'direct_subordinate' => $directSubordinateCount,
//             'total_commission' => $totalCommission,
// 			'weekly_commission'=>0,
//             'team_subordinate' => $teamSubordinateCount,
//             'referral_code' => $referralCode,
//         ];
//         //dd($result);

//         return response()->json(['status' => 200,'message' => 'data fetch successfully','data' =>$result], 200);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }

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

// public function subordinate_data(Request $request) {
//     try {
//         $validator = Validator::make($request->all(), [
//             'id' => 'required',
//             'tier' => 'required|integer|min:0',
//         ]);

//         $validator->stopOnFirstFailure();

//         if ($validator->fails()) {
//             $response = [
//                 'status' => 400,
//                 'message' => $validator->errors()->first()
//             ]; 
//             return response()->json($response, 400);
//         }

//         $user_id = $request->id; 
//         $tier = $request->tier; 
// 		$search_uid = $request->u_id;
// 		$CurrentDate = $request->created_at;
// 	 if (!empty($CurrentDate)) {	 
// 		 $currentDate = $CurrentDate;
// 	 }else{
//          $currentDate = Carbon::now()->subDay()->format('Y-m-d');
// 	 }
// 		  if (!empty($search_uid)) {
//             $subordinates_deposit = \DB::select("WITH RECURSIVE subordinates AS (
//     SELECT id, referrer_id, 1 AS level
//     FROM users
//     WHERE referrer_id = ?
//     UNION ALL
//     SELECT u.id, u.referrer_id, s.level + 1
//     FROM users u
//     INNER JOIN subordinates s ON s.id = u.referrer_id
//     WHERE s.level + 1 <= ?
// )
// SELECT 
//     users.id, 
//     users.u_id, 
//     COALESCE(SUM(bets.amount), 0) AS bet_amount, 
//     COALESCE(SUM(payins.cash), 0) AS total_cash, 
//     COALESCE(SUM(bets.amount), 0) * COALESCE(mlm_levels.commission, 0) / 100 AS commission, 
//     ? AS yesterday_date 
// FROM users
// LEFT JOIN subordinates ON users.id = subordinates.id
// LEFT JOIN mlm_levels ON subordinates.level = mlm_levels.id
// LEFT JOIN bets ON users.id = bets.userid AND bets.created_at LIKE ?
// LEFT JOIN payins ON users.id = payins.user_id AND payins.created_at LIKE ?
// WHERE users.u_id LIKE ?
// GROUP BY users.id, users.u_id, mlm_levels.commission;
// ;
// ", [$user_id, $tier, $currentDate ,$currentDate . ' %', $currentDate . ' %', $search_uid . '%']);
			  
			  
// 			  $subordinates_data = \DB::select("
//     WITH RECURSIVE subordinates AS (
//         SELECT id, referrer_id, 1 AS level
//         FROM users
//         WHERE referrer_id = ?
//         UNION ALL
//         SELECT u.id, u.referrer_id, s.level + 1
//         FROM users u
//         INNER JOIN subordinates s ON s.id = u.referrer_id
//         WHERE s.level + 1 <= ?
//     )
//     SELECT 
//         users.id, 
//         users.u_id, 
//         COALESCE(payin_summary1.total_payins, 0) AS payin_count,
//         COALESCE(bettor_count.total_bettors, 0) AS bettor_count,
//         COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount,
//         COALESCE(payin_summary2.total_payin_cash, 0) AS payin_amount
//     FROM users
//     LEFT JOIN (
//         SELECT userid, SUM(amount) AS total_bet_amount 
//         FROM bets 
//         WHERE created_at LIKE ? 
//         GROUP BY userid
//     ) AS bet_summary ON users.id = bet_summary.userid
    
//     LEFT JOIN (
//         SELECT user_id, SUM(cash) AS total_payin_cash
//         FROM payins 
//         WHERE status = 2 AND created_at LIKE ? 
//         GROUP BY user_id
//     ) AS payin_summary2 ON users.id = payin_summary2.user_id
    
//     LEFT JOIN (
//         SELECT user_id, COUNT(*) AS total_payins
//         FROM payins 
//         WHERE status = 2 AND created_at LIKE ? 
//         GROUP BY user_id
//     ) AS payin_summary1 ON users.id = payin_summary1.user_id

//     LEFT JOIN (
//         SELECT userid, COUNT(DISTINCT userid) AS total_bettors
//         FROM bets 
//         WHERE created_at LIKE ? 
//         GROUP BY userid
//     ) AS bettor_count ON users.id = bettor_count.userid
	
//     WHERE users.id IN (
//         SELECT id FROM subordinates WHERE level = ?
//     )
//     GROUP BY 
//         users.id, 
//         users.u_id, 
//         payin_summary1.total_payins,
//         bettor_count.total_bettors,
//         bet_summary.total_bet_amount,
//         payin_summary2.total_payin_cash
		
// ", [$user_id, $tier, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier]);

//         } else {
		
//       $subordinates_deposit = \DB::select("
//   WITH RECURSIVE subordinates AS (
//         SELECT id, referrer_id, 1 AS level
//         FROM users
//         WHERE referrer_id = ?
//         UNION ALL
//         SELECT u.id, u.referrer_id, s.level + 1
//         FROM users u
//         INNER JOIN subordinates s ON s.id = u.referrer_id
//         WHERE s.level + 1 <= ?
//     )
//     SELECT 
//         users.id, 
//         users.u_id, 
//         COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount, 
//         COALESCE(payin_summary.total_cash, 0) AS total_cash,  
//         COALESCE(bet_summary.total_bet_amount, 0) * COALESCE(mlm_levels.commission, 0) / 100 AS commission,
//         ? AS yesterday_date 
		

//     FROM users
//     LEFT JOIN (
//         SELECT userid, SUM(amount) AS total_bet_amount 
//         FROM bets 
//         WHERE created_at LIKE ? 
//         GROUP BY userid
//     ) AS bet_summary ON users.id = bet_summary.userid 
//     LEFT JOIN (
//         SELECT user_id, SUM(cash) AS total_cash 
//         FROM payins  
//         WHERE status = 2 AND created_at LIKE ? 
//         GROUP BY user_id
//     ) AS payin_summary ON users.id = payin_summary.user_id
// 	LEFT JOIN subordinates ON users.id = subordinates.id
//     LEFT JOIN mlm_levels ON subordinates.level = mlm_levels.id
//     WHERE users.id IN (
//         SELECT id FROM subordinates WHERE level = ?
//     )
//     GROUP BY users.id, users.u_id, mlm_levels.commission, bet_summary.total_bet_amount, payin_summary.total_cash;

// ",[$user_id, $tier, $currentDate ,$currentDate . ' %', $currentDate . ' %', $tier]);
			  
// 	$subordinates_data = \DB::select("
//     WITH RECURSIVE subordinates AS (
//         SELECT id, referrer_id, 1 AS level
//         FROM users
//         WHERE referrer_id = ?
//         UNION ALL
//         SELECT u.id, u.referrer_id, s.level + 1
//         FROM users u
//         INNER JOIN subordinates s ON s.id = u.referrer_id
//         WHERE s.level + 1 <= ?
//     )
//     SELECT 
//         users.id, 
//         users.u_id, 
//         COALESCE(SUM(payin_summary1.total_payins), 0) AS payin_count,
//         COALESCE(SUM(bettor_count.total_bettors), 0) AS bettor_count,
//         COALESCE(SUM(bet_summary.total_bet_amount), 0) AS bet_amount,
//         COALESCE(SUM(payin_summary2.total_payin_cash), 0) AS payin_amount
//     FROM users
//     LEFT JOIN (
//         SELECT userid, SUM(amount) AS total_bet_amount 
//         FROM bets 
//         WHERE created_at LIKE ? 
//         GROUP BY userid
//     ) AS bet_summary ON users.id = bet_summary.userid
    
//     LEFT JOIN (
//         SELECT user_id, SUM(cash) AS total_payin_cash
//         FROM payins 
//         WHERE status = 2 AND created_at LIKE ? 
//         GROUP BY user_id
//     ) AS payin_summary2 ON users.id = payin_summary2.user_id
    
//     LEFT JOIN (
//         SELECT user_id, COUNT(*) AS total_payins
//         FROM payins 
//         WHERE status = 2 AND created_at LIKE ? 
//         GROUP BY user_id
//     ) AS payin_summary1 ON users.id = payin_summary1.user_id

//     LEFT JOIN (
//         SELECT userid, COUNT(DISTINCT userid) AS total_bettors
//         FROM bets 
//         WHERE created_at LIKE ? 
//         GROUP BY userid
//     ) AS bettor_count ON users.id = bettor_count.userid
//     WHERE users.id IN (
//         SELECT id FROM subordinates 
//         WHERE ? = 0 OR level = ?
//     )
//     GROUP BY 
//         users.id, 
//         users.u_id
// ", [$user_id, $tier > 0 ? $tier : PHP_INT_MAX, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier, $tier]);




// 		 }
		
// $betAmountTotal = 0;
// $numberOfBettors = 0;
// $number_of_deposit = 0;
// $payin_amount = 0;
// $first_deposit = 0;
// $first_deposit_amount = 0;
		
// foreach ($subordinates_data as $data) {
// 	$number_of_deposit += $data->payin_count ?? 0;
//     $payin_amount += $data->payin_amount ?? 0;
	
//     $betAmountTotal += $data->bet_amount ?? 0;
//     $numberOfBettors += $data->bettor_count ?? 0;
	
// 	$first_deposit += $data->total_first_recharge ?? 0;
//     $first_deposit_amount += $data->total_first_deposit_amount ?? 0;
// }

// $result = [
//     'number_of_deposit' => $number_of_deposit,
//     'payin_amount' => $payin_amount,
//     'number_of_bettor' => $numberOfBettors,
//     'bet_amount' => $betAmountTotal,
//     'first_deposit' => $first_deposit,
//     'first_deposit_amount' => $first_deposit_amount,
//     'subordinates_data' => $subordinates_deposit,
// ];

		

//         return response()->json($result, 200);

//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
		
//     }
// }

// public function subordinate_data(Request $request)
// {
//     try {
//         // Validate the request
//         $validator = Validator::make($request->all(), [
//             'id' => 'required|integer',
//             'tier' => 'required|integer|min:0',
//         ]);

//         // Stop on first failure
//         $validator->stopOnFirstFailure();

//         if ($validator->fails()) {
//             return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
//         }

//         // Get input parameters
//         $userId = $request->id;
//         $tier = $request->tier;
//         $searchUid = $request->u_id;
//         $currentDate = $request->created_at ?: Carbon::now()->subDay()->format('Y-m-d');

//         // Step 1: Initialize a collection to store subordinates
//         $subordinates = collect();

//         // Step 2: Get the initial users at level 1 (direct referrals)
//         $currentLevelUsers = User::where('referrer_id', $userId)->get();
//         $currentLevel = 1; // Start at level 1

//         // Step 3: Iterate through each level to get subordinates up to the given tier
//         while ($currentLevelUsers->isNotEmpty() && $currentLevel <= $tier) {
//             // Merge current level users into the subordinates collection
//             $subordinates = $subordinates->merge($currentLevelUsers);

//             // Get the next level users (users referred by the current level users)
//             $currentLevelUsers = User::whereIn('referrer_id', $currentLevelUsers->pluck('id'))->get();
            
//             $currentLevel++; // Increment the level
//         }

//         // Get all subordinate user IDs
//         $subordinateIds = $subordinates->pluck('id');

//         // Step 4: If there is a search_uid, filter the subordinates by UID
//         if (!empty($searchUid)) {
//             $subordinateIds = User::whereIn('id', $subordinateIds)
//                                   ->where('u_id', 'like', $searchUid . '%')
//                                   ->pluck('id');
//         }

//         // Step 5: Fetch data for the filtered subordinates
//         $subordinatesData = User::whereIn('id', $subordinateIds)
//             ->with(['bets' => function ($query) use ($currentDate) {
//                 $query->whereDate('created_at', $currentDate);
//             }, 'payins' => function ($query) use ($currentDate) {
//                 $query->whereDate('created_at', $currentDate)
//                       ->where('status', 2);
//             }, 'mlmLevel'])
//             ->get();

//         // Step 6: Initialize the result array
//         $result = [
//             'number_of_deposit' => 0,
//             'payin_amount' => 0,
//             'number_of_bettor' => 0,
//             'bet_amount' => 0,
//             'first_deposit' => 0,
//             'first_deposit_amount' => 0,
//             'subordinates_data' => [],
//         ];

//         // Step 7: Calculate data for each subordinate
//         foreach ($subordinatesData as $user) {
//             // Calculate bet amount and payin amount
//             $betAmount = $user->bets->sum('amount');
//             $payinAmount = $user->payins->sum('cash');
//             $numberOfBettors = $user->bets->count();
//             $commission = ($betAmount * optional($user->mlmLevel)->commission) / 100;

//             // Add to result totals
//             $result['bet_amount'] += $betAmount;
//             $result['payin_amount'] += $payinAmount;
//             $result['number_of_bettor'] += $numberOfBettors;

//             // Add individual subordinate data to the result
//             $result['subordinates_data'][] = [
//                 'id' => $user->id,
//                 'u_id' => $user->u_id,
//                 'bet_amount' => $betAmount,
//                 'payin_amount' => $payinAmount,
//                 'commission' => $commission,
//             ];
//         }

//         // Step 8: Return the result as JSON
//         return response()->json(['status' => 200,'message' => 'data fetch successfully','data' =>$result], 200);

//     } catch (\Exception $e) {
//         // Return error message in case of exception
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }

////sudheer sir codes jupiuter
// public function subordinate_data(Request $request) 
// {
//     try {
//         // Validate the request
//         $validator = Validator::make($request->all(), [
//             'id' => 'required|integer',
//             'tier' => 'nullable|integer|min:0',
//             'created_at' => 'nullable|date'
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
//         }

//         // Get input parameters
//         $userId = $request->id;
//         $tier = $request->tier ?? 0;
//         $searchUid = $request->u_id;
//         $currentDate = $request->created_at ?: Carbon::now()->subDay()->format('Y-m-d');

//         // Step 1: Initialize a collection to store subordinates
//         $subordinates = collect();

//         // Step 2: Get the initial users at level 1 (direct referrals)
//         $currentLevelUsers = User::where('referrer_id', $userId)->get();
//         $currentLevel = 1;

//         // Step 3: Iterate through each level to get subordinates up to the given tier
//         while ($currentLevelUsers->isNotEmpty() && ($tier == 0 || $currentLevel <= $tier)) {
//             $subordinates = $subordinates->merge($currentLevelUsers);
//             $currentLevelUsers = User::whereIn('referrer_id', $currentLevelUsers->pluck('id'))->get();
//             $currentLevel++;
//         }

//         // Get all subordinate user IDs
//         $subordinateIds = $subordinates->pluck('id');

//         // If search UID is provided, filter by UID
//         if (!empty($searchUid)) {
//             $subordinateIds = User::whereIn('id', $subordinateIds)
//                                   ->where('u_id', 'like', $searchUid . '%')
//                                   ->pluck('id');
//         }

//         // Step 4: Fetch Data with Corrected Query
//         $subordinatesData = DB::table('users')
//             ->leftJoin('mlm_levels', 'users.role_id', '=', 'mlm_levels.id')
//             ->leftJoin(DB::raw("(
//                 SELECT userid, SUM(amount) as total_bet 
//                 FROM bets 
//                 WHERE DATE(created_at) = '{$currentDate}' 
//                 GROUP BY userid
//             ) as bet_data"), 'users.id', '=', 'bet_data.userid')
//             ->leftJoin(DB::raw("(
//                 SELECT user_id, SUM(cash) as total_payin, COUNT(id) as deposit_count 
//                 FROM payins 
//                 WHERE DATE(created_at) = '{$currentDate}' 
//                 AND status = 2 
//                 GROUP BY user_id
//             ) as payin_data"), 'users.id', '=', 'payin_data.user_id')
//             ->whereIn('users.id', $subordinateIds)
//             ->select([
//                 'users.id',
//                 'users.u_id',
//                 'mlm_levels.commission as commission_percentage',
//                 DB::raw('COALESCE(bet_data.total_bet, 0) as bet_amount'),
//                 DB::raw('COALESCE(payin_data.total_payin, 0) as payin_amount'),
//                 DB::raw('COALESCE(payin_data.deposit_count, 0) as number_of_deposit'),
//                 DB::raw('(COALESCE(bet_data.total_bet, 0) * COALESCE(mlm_levels.commission, 0)) / 100 as commission')
//             ])
//             ->get();

//         // Step 5: Initialize the result array
//         $result = [
//             'number_of_deposit' => 0,
//             'payin_amount' => 0,
//             'number_of_bettor' => 0,
//             'bet_amount' => 0,
//             'first_deposit' => 0,
//             'first_deposit_amount' => 0,
//             'subordinates_data' => [],
//         ];

//         // Step 6: Initialize variables
//         $first_deposit = 0;
//         $first_deposit_amount = 0;

//         // Step 7: Calculate data for each subordinate
//         foreach ($subordinatesData as $user) {
//             // Calculate values
//             $betAmount = $user->bet_amount;
//             $payinAmount = $user->payin_amount;
//             $depositCount = $user->number_of_deposit;
//             $commission = $user->commission;
            
//             $first_deposit += $user->total_first_recharge ?? 0;
//             $first_deposit_amount += $user->total_first_deposit_amount ?? 0;

//             // Update result totals
//             $result['bet_amount'] += $betAmount;
//             $result['payin_amount'] += $payinAmount;
//             $result['number_of_deposit'] += $depositCount;
            
//             if ($betAmount > 0) {
//                 $result['number_of_bettor']++;
//             }

//             // Add individual subordinate data
//             $result['subordinates_data'][] = [
//                 'id' => $user->id,
//                 'u_id' => $user->u_id,
//                 'bet_amount' => $betAmount,
//                 'payin_amount' => $payinAmount,
//                 'number_of_deposit' => $depositCount,
//                 'commission' => $commission,
//             ];
//         }

//         // Step 8: Return the result
//         return response()->json(['status' => 200, 'message' => 'Data fetched successfully', 'data' => $result], 200);

//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }

public function subordinate_data(Request $request) 
{
    try {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'tier' => 'nullable|integer|min:0',
            'created_at' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }

        // Get input parameters
        $userId = $request->id;
        $tier = $request->tier ?? 0;
        $searchUid = $request->u_id;
        $currentDate = $request->created_at ?: Carbon::now()->subDay()->format('Y-m-d');

        // Step 1: Initialize a collection to store subordinates
        $subordinates = collect();

        // Step 2: Get the initial users at level 1 (direct referrals)
        $currentLevelUsers = User::where('referrer_id', $userId)->get();
        $currentLevel = 1;

        // Step 3: Iterate through each level to get subordinates up to the given tier
        while ($currentLevelUsers->isNotEmpty() && ($tier == 0 || $currentLevel <= $tier)) {
            $subordinates = $subordinates->merge($currentLevelUsers);
            $currentLevelUsers = User::whereIn('referrer_id', $currentLevelUsers->pluck('id'))->get();
            $currentLevel++;
        }

        // Get all subordinate user IDs
        $subordinateIds = $subordinates->pluck('id');

        // If search UID is provided, filter by UID
        if (!empty($searchUid)) {
            $subordinateIds = User::whereIn('id', $subordinateIds)
                                  ->where('u_id', 'like', $searchUid . '%')
                                  ->pluck('id');
        }

        // Step 4: Fetch Data with Corrected Query
        $subordinatesData = DB::table('users')
            ->leftJoin('mlm_levels', 'users.role_id', '=', 'mlm_levels.id')
            ->leftJoin(DB::raw("(
                SELECT userid, SUM(amount) as total_bet 
                FROM bets 
                WHERE DATE(created_at) = '{$currentDate}' 
                GROUP BY userid
            ) as bet_data"), 'users.id', '=', 'bet_data.userid')
            ->leftJoin(DB::raw("(
                SELECT user_id, SUM(cash) as total_payin, COUNT(id) as deposit_count 
                FROM payins 
                WHERE DATE(created_at) = '{$currentDate}' 
                AND status = 2 
                GROUP BY user_id
            ) as payin_data"), 'users.id', '=', 'payin_data.user_id')
            ->leftJoin(DB::raw("(
                SELECT p1.user_id, COUNT(p1.id) as total_first_recharge, SUM(p1.cash) as total_first_deposit_amount 
                FROM payins p1 
                WHERE p1.status = 2 
                AND p1.created_at = (
                    SELECT MIN(p2.created_at) 
                    FROM payins p2 
                    WHERE p2.user_id = p1.user_id 
                    AND p2.status = 2
                )
                GROUP BY p1.user_id
            ) as first_deposit_data"), 'users.id', '=', 'first_deposit_data.user_id')
            ->whereIn('users.id', $subordinateIds)
            ->select([
                'users.id',
                'users.u_id',
                'mlm_levels.commission as commission_percentage',
                DB::raw('COALESCE(bet_data.total_bet, 0) as bet_amount'),
                DB::raw('COALESCE(payin_data.total_payin, 0) as payin_amount'),
                DB::raw('COALESCE(payin_data.deposit_count, 0) as number_of_deposit'),
                DB::raw('(COALESCE(bet_data.total_bet, 0) * COALESCE(mlm_levels.commission, 0)) / 100 as commission'),
                DB::raw('COALESCE(first_deposit_data.total_first_recharge, 0) as total_first_recharge'),
                DB::raw('COALESCE(first_deposit_data.total_first_deposit_amount, 0) as total_first_deposit_amount')
            ])
            ->get();

        // Step 5: Initialize the result array
        $result = [
            'number_of_deposit' => 0,
            'payin_amount' => 0,
            'number_of_bettor' => 0,
            'bet_amount' => 0,
            'first_deposit' => 0,
            'first_deposit_amount' => 0,
            'subordinates_data' => [],
        ];

        // Step 6: Calculate data for each subordinate
        foreach ($subordinatesData as $user) {
            // Calculate values
            $betAmount = $user->bet_amount;
            $payinAmount = $user->payin_amount;
            $depositCount = $user->number_of_deposit;
            $commission = $user->commission;
            $firstDeposit = $user->total_first_recharge;
            $firstDepositAmount = $user->total_first_deposit_amount;

            // Update result totals
            $result['bet_amount'] += $betAmount;
            $result['payin_amount'] += $payinAmount;
            $result['number_of_deposit'] += $depositCount;
            $result['first_deposit'] += $firstDeposit;
            $result['first_deposit_amount'] += $firstDepositAmount;

            if ($betAmount > 0) {
                $result['number_of_bettor']++;
            }

            // Add individual subordinate data
            $result['subordinates_data'][] = [
                'id' => $user->id,
                'u_id' => $user->u_id,
                'bet_amount' => $betAmount,
                'payin_amount' => $payinAmount,
                'number_of_deposit' => $depositCount,
                'commission' => $commission,
                'first_deposit' => $firstDeposit,
                'first_deposit_amount' => $firstDepositAmount,
            ];
        }

        // Step 7: Return the result
        return response()->json(['status' => 200, 'message' => 'Data fetched successfully', 'data' => $result], 200);

    } catch (\Exception $e) {
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
            $maxTier = 4;

            // Recursive query to fetch all subordinates within the maxTier levels
            $subordinatesData = DB::select("
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
            //return $subordinatesData;
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