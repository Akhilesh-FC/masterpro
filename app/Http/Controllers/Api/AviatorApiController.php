<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{AviatorBet,AviatorResult,AviatorAdminResult,User,BusinessSetting,AviatorSetting,WalletHistory};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller; // Add this line
use Illuminate\Http\JsonResponse;
use App\Helper\jilli;


class AviatorApiController extends Controller
{
    
    public function aviatorBet(Request $request)
{
    // Set timezone
    date_default_timezone_set('Asia/Kolkata');
    
    // Validate the request
    $validator = Validator::make($request->all(), [
        'uid' => 'required|exists:users,id',
        'number' => 'required',
        'amount' => 'required|numeric|min:1',
        'game_id' => 'required|in:5',
        'game_sr_num' => 'required'
    ]);
    
    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()],200);
    }
		  $uid = $request->uid;
     //$wallet_update = jilli::update_user_wallet($uid);
    // Extract request data
  
    $amount = $request->amount;
    $sr_num = $request->game_sr_num;
    $number = $request->number;
    $game_id = $request->game_id;
    $stop_multiplier = $request->stop_multiplier ?? 0;

    // Check if the bet already exists
    $existingBet = AviatorBet::where('game_id', 5)
        ->where('number', $number)
        ->where('game_sr_num', $sr_num)
        ->where('uid', $uid)
        ->where('status', 0)
        ->first();

    if ($existingBet) {
        return response()->json(['status' => 400, 'message' => 'Already bet created!'],200);
    }

    // Get user and validate wallet
    $user = User::find($uid);

    if (!$user) {
        return response()->json(['status' => 400, 'message' => 'Failed to get data of user!'],200);
    }

    if ($user->wallet < $amount) {
        return response()->json(['status' => 400, 'message' => 'Insufficient funds!'],200);
    }

    // Update user wallet
    $walletAdjustment = $amount;
    if ($amount > $user->winning_wallet) {
        $walletAdjustment += $user->winning_wallet;
        $user->winning_wallet = 0;
    } else {
        $user->winning_wallet -= $amount;
    }
    
    $user->wallet -= $amount;
    $user->save();
		
	$deduct_jili = jilli::deduct_from_wallet($uid,$amount);

    // Prepare data for the bet
    $bettingFee = BusinessSetting::find(10);
    $percentageBet = $bettingFee->longtext ?? 0;
    $commission = $amount * $percentageBet;
    $datetime = now();

    // Create new bet
    $bet = AviatorBet::create([
        'uid' => $uid,
        'amount' => $amount,
        'number' => $number,
        'game_id' => $game_id,
        'totalamount' => $amount,
        'color' => 'Aviator',
        'game_sr_num' => $sr_num,
        'commission' => $commission,
        'status' => 0,
        'stop_multiplier' => $stop_multiplier,
        //'datetime' => $datetime,
        'created_at' => $datetime,
        
    ]);

    // Return success response
  if ($bet) {
    return response()->json(['status' => 200, 'message' => 'Bet placed successfully. ' . $sr_num,'jili'=>$deduct_jili]);
} else {
    return response()->json(['status' => 400, 'message' => 'Something Went Wrong.']);
}

}

    
    public function aviator_cashout(Request $request)
{
    //dd($request);
    $requests = json_decode(base64_decode($request->salt));
    
    date_default_timezone_set('Asia/Kolkata');
    $datetime = now()->format('Y-m-d H:i:s');

    $uid = $requests->uid;
   
    $multiplier = $requests->multiplier;
    $game_sr_num = $requests->game_sr_num;
    $number = $requests->number;
    //dd($number);

    // Find bet details
    $bet_details = AviatorBet::where('game_sr_num', $game_sr_num)
        ->where('game_id', 5)
        ->where('uid', $uid)
        ->where('number', $number)
        ->where('status', 0)
        ->where('result_status', 0)
        ->first();
      //dd($bet_details);
    if (!$bet_details) {
        return response()->json(['message' => 'Already cashed out..!','status' => 400],200);
    }

    $amount_trade = $bet_details->totalamount;
    $win_amount = $amount_trade * $multiplier;
     //dd($win_amount);

    // Update the bet
   $update = AviatorBet::where('uid', $uid)
            ->where('number', $number)
            ->where('game_id', 5) // 5 for aviator
            ->where('status', 0)
            ->where('game_sr_num', $game_sr_num)
            ->where('result_status', 0)
            ->update([
                'status' => 1,
                'result_status' => 1,
                'multiplier' => $multiplier,
                'win' => $win_amount
            ]);


    // Update the user’s wallet
    $user = User::find($uid);
    if ($user) {
        $user->increment('winning_wallet', $win_amount);
        $user->increment('wallet', $win_amount);

        // Log the wallet history
        WalletHistory::create([
            'user_id' => $uid,
            'amount' => $win_amount,
            'type_id' => 4, // 24 for aviator
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => "$win_amount Rs. Cashout Successfully", 'status' => 200],200);
    }

    return response()->json(['message' => 'Internal Error', 'status' => 400],200);
}
    
    public function aviator_history(Request $request)
{
    // Validate incoming request
    $validator = Validator::make($request->all(), [
        'uid' => 'required',
        'game_id' => 'required',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()],200);
    }

    $userid = $request->uid;
    $game_id = $request->game_id;
    $limit = $request->limit ?? 10000;
    $offset = $request->offset ?? 0;
    $from_date = $request->created_at;
    $to_date = $request->created_at;

    // Build the query using Eloquent
    $query = AviatorBet::select('aviator_bets.*', 
                                'aviator_bets.win AS cashout_amount', 
                                'aviator_bets.multiplier AS multiplier', 
                                'aviator_results.price AS crash_point')
                        ->leftJoin('aviator_results', 'aviator_bets.game_sr_num', '=', 'aviator_results.game_sr_num')
                        ->where('aviator_bets.uid', $userid)
                        ->where('aviator_bets.game_id', $game_id);

    // Apply date filters if provided
    if (!empty($from_date) && !empty($to_date)) {
        $query->whereBetween('aviator_bets.created_at', [$from_date, $to_date]);
    } elseif (!empty($from_date)) {
        $query->where('aviator_bets.created_at', 'like', "$from_date%");
    } elseif (!empty($to_date)) {
        $query->where('aviator_bets.created_at', 'like', "$to_date%");
    }

    // Fetch the results with pagination
    $results = $query->orderBy('id', 'desc')->offset($offset)->limit($limit)->get();

    // Return response
    if ($results->isNotEmpty()) {
        return response()->json([
            'status' => 200,
            'message' => 'Data found',
            'data' => $results,
        ],200);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'No Data found',
            'data' => [],
        ],200);
    }
}
    public function bet_cancel(Request $request)
    {
        //dd($request);
        $validator = Validator::make($request->all(),[
		    'userid'=>'required|exists:users,id|exists:aviator_bets,uid',
		    'number'=>'required|numeric|exists:aviator_bets,number',
		    'game_sr_num'=>'required'
		]);
		
		if($validator->fails()){
	        return response()->json(['message'=>$validator->errors()->first()]);
		}
		
		$userId = $request->userid;
		$number = $request->number;
		$gameno= $request->game_sr_num;
      	$gamesno = $request->game_sr_num + 1;

		
		$cancelCount = AviatorBet::where('uid', $userId)
            ->where('number', $number)
            ->where('status', 4)
            ->whereIn('game_sr_num', [$gamesno, $gameno])
            ->count();
			//dd($cancelCount);	
			if($cancelCount >= 1 ){
				 return response()->json(['message'=>'Cancel limit exits...!','status'=>400]);
			}
			
		$userBet=AviatorBet::where('uid', $userId)
            ->where('number', $number)
            ->where('status', 0)
            ->whereIn('game_sr_num', [$gamesno, $gameno])
            //->toRawSql();
            ->first();
            //dd($userBet);
        if($userBet){
           //dd("hii");
           $refundAmount = User::where('id', $userId)
                ->update(['wallet' => DB::raw('wallet + ' . $userBet->amount)]);
                 //dd($refundAmount);
            if($refundAmount){
                //dd("hello");
            $cancelBet = AviatorBet::where('uid', $userId)
                ->where('number', $number)
                ->where('status', 0)
                ->whereIn('game_sr_num', [$gamesno, $gameno])
                ->update(['status' => 4]);

                //dd($cancelBet);
                if($cancelBet){
                    return response()->json([ 'success'=>true, 'message'=>'Bet Cancel Successfully'],200);
                }else{
                    return response()->json([ 'success'=>false, 'message'=>'internal error..!'],500);
                }
                
            }
        }
        
    }
// public function bet_cancel(Request $request)
// {
//     date_default_timezone_set('Asia/Kolkata');
//     $datetime = now()->format('Y-m-d H:i:s');

//     $validator = Validator::make($request->all(), [
//         'userid' => 'required|exists:users,id',
//         'number' => 'required|exists:aviator_bets,number',
//         'game_sr_num' => 'required|exists:aviator_bets,game_sr_num'
//     ]);

//     $validator->stopOnFirstFailure();

//     if ($validator->fails()) {
//         return response()->json(['status' => 400, 'message' => $validator->errors()->first()],200);
//     }

//     $uid = $request->userid;
//     $game_sr_num = $request->game_sr_num;
//     $number = $request->number;

//     // Retrieve the user's bet
//     $user_bet_data = AviatorBet::where('uid', $uid)
//         ->where('number', $number)
//         ->where('status', 0)
//         ->where('game_sr_num', $game_sr_num)
//         ->first();

//     if (!$user_bet_data) {
//         return response()->json(['message' => 'No active bet found.', 'status' => 400],200);
//     }

//     $bet_amount = $user_bet_data->amount;

//     // Update user's wallet and winnings
//     $user = User::find($uid);
//     $user->winning_wallet += $bet_amount;
//     $user->wallet += $bet_amount;

//     if ($user->save()) {
//         // Delete the bet
//         if ($user_bet_data->delete()) {
//             return response()->json(['message' => 'Bet Cancel Successfully', 'status' => 200],200);
//         } else {
//             return response()->json(['message' => 'Cannot Cancel Bet', 'status' => 200],200);
//         }
//     } else {
//         return response()->json(['message' => 'Internal error...!', 'status' => 500]);
//     }
// }

public function last_five_result()
{
    // Fetch the last 30 results where status is 1, using the model
    $results = AviatorResult::select('price')
        ->where('status', 1)
        ->orderByDesc('id')
        ->limit(30)
        ->get();

    if ($results->isNotEmpty()) {
        $response = [
            'status' => "200",
            'message' => 'success',
            'data' => $results,
        ];
        return response()->json($response, 200);
    } else {
        $response = [
            'status' => "400",
            'message' => 'Data Not Found'
        ];
        return response()->json($response, 200);
    }
}

// public function aviator_last_result()
// {
//     $results = AviatorResult::select('price', 'game_sr_num')
//     ->where('status', 1)
//     ->orderByDesc('id')
//     ->limit(30)
//     ->get();

// $mappedResults = $results->map(function ($item) {
//     return [
//         'multiplier' => $item->price,     
//         'period_no' => $item->game_sr_num
//     ];
// });
// if ($mappedResults->isNotEmpty()) {
    
//     $response = [
//         'status' => 200,
//         'message' => 'success',
//         'last_result' => $mappedResults,
//     ];
//     return response()->json($response, 200);
// } else {
//     $response = [
//         'status' => 400,
//         'message' => 'Data Not Found'
//     ];
//     return response()->json($response, 200);
// }

// }
    
    // public function result_half_new(Request $request): JsonResponse
    // {
       
    //         // Fetch data using Eloquent
    //         $result = AviatorBet::selectRaw("
    //             COALESCE(SUM(amount), 0) AS totalamount, 
    //             COALESCE(COUNT(`uid`), 0) AS totalusers, 
    //             COALESCE(MAX(`amount`), 0) AS highestamount, 
    //             (
    //                 SELECT COALESCE(MAX(game_sr_num), 0) + 1 
    //                 FROM aviator_results 
    //                 LIMIT 1
    //             ) AS game_sr, 
    //             (
    //                 SELECT COALESCE(
    //                     (
    //                         SELECT COALESCE(`multiplier`, 0) 
    //                         FROM `aviator_admin_results` 
    //                         WHERE `game_sr_num` = (
    //                             SELECT COALESCE(MAX(`game_sr_num`), 0) + 1 
    //                             FROM `aviator_results`
    //                         ) 
    //                         AND `game_id` = '5' 
    //                         LIMIT 1
    //                     ), 0
    //                 )
    //             ) AS adminmultiply, 
    //             (
    //                 SELECT `winning_percentage` 
    //                 FROM `game_settings` 
    //                 WHERE `id` = 5 
    //                 LIMIT 1
    //             ) AS adminpercent 
    //         ")
    //         ->where('game_id', '5')
    //         ->where('game_sr_num', function($query) {
    //             $query->selectRaw('COALESCE(MAX(game_sr_num), 0) + 1 FROM aviator_results');
    //         })
    //         ->first();

    //         if ($result) {
    //             $game_sr = $result->game_sr;

    //             $multiplier = AviatorAdminResult::where('game_sr_num', $game_sr)->value('number') ?? 0;

    //             $total_win = AviatorBet::where('game_sr_num', $game_sr)->where('game_id', 5)->sum('win');

    //             $aviatorSetting = AviatorSetting::find(1);

    //             return response()->json([
    //                 "message" => "Data fetched successfully",
    //                 "status" => 200,
    //                 'totalamount' => intval($result->totalamount),
    //                 'totalusers' => intval($result->totalusers),
    //                 'game_sr' => intval($game_sr),
    //                 'highestamount' => intval($result->highestamount),
    //                 'adminmultiply' => $multiplier,
    //                 'adminpercent' => $aviatorSetting->win_per ?? 0,
    //                 'loss_per' => $aviatorSetting->loss_per ?? 0,
    //                 'min_amount' => $aviatorSetting->amount ?? 0,
    //                 'total_win' => $total_win
    //             ]);
    //         } else {
    //             return response()->json(["message" => "No data found", "status" => 404]);
    //         }
        
    // }
    
     public function result_half_new(Request $request): JsonResponse
    {
    
        // Fetch data using Eloquent
        $result = AviatorBet::selectRaw("
            COALESCE(SUM(amount), 0) AS totalamount, 
            COALESCE(COUNT(`uid`), 0) AS totalusers, 
            COALESCE(MAX(`amount`), 0) AS highestamount, 
            (
                SELECT COALESCE(MAX(game_sr_num), 0) + 1 
                FROM aviator_results 
                LIMIT 1
            ) AS game_sr, 
            (
                SELECT COALESCE(
                    (
                        SELECT COALESCE(`multiplier`, 0) 
                        FROM `aviator_admin_results` 
                        WHERE `game_sr_num` = (
                            SELECT COALESCE(MAX(`game_sr_num`), 0) + 1 
                            FROM `aviator_results`
                        ) 
                        AND `game_id` = '5' 
                        LIMIT 1
                    ), 0
                )
            ) AS adminmultiply, 
            (
                SELECT `winning_percentage` 
                FROM `game_settings` 
                WHERE `id` = 5 
                LIMIT 1
            ) AS adminpercent 
        ")
        ->where('game_id', '5')
        ->where('game_sr_num', function($query) {
            $query->selectRaw('COALESCE(MAX(game_sr_num), 0) + 1 FROM aviator_results');
        })
        ->first();

        if ($result) {
            $game_sr = $result->game_sr;

            $multiplier = AviatorAdminResult::where('game_sr_num', $game_sr)->value('number') ?? 0;

            $total_win = AviatorBet::where('game_sr_num', $game_sr)->where('game_id', 5)->sum('win');

            $aviatorSetting = AviatorSetting::find(1);

            return response()->json([
                "message" => "Data fetched successfully",
                "status" => 200,
                'totalamount' => intval($result->totalamount),
                'totalusers' => intval($result->totalusers),
                'game_sr' => intval($game_sr),
                'highestamount' => intval($result->highestamount),
                'adminmultiply' => $multiplier,
                'adminpercent' => $aviatorSetting->win_per ?? 0,
                'loss_per' => $aviatorSetting->loss_per ?? 0,
                'min_amount' => $aviatorSetting->amount ?? 0,
                'total_win' => $total_win
            ])->withHeaders([
                    'Access-Control-Allow-Methods' => 'GET',
                    'Content-Type' => 'application/json; charset=UTF-8',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Headers' => 'Origin, Content-Type',
                    'Expires' => '0',
                    'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                    'Pragma' => 'no-cache',
            ]);
        } else {
            return response()->json(["message" => "No data found", "status" => 404]);
        }
    }

// public function result_insert_new(Request $request)
//     {
//         // Validate input
//         $request->validate([
//             'adminmultiply' => 'required|numeric',
//             'game_sr' => 'required|string',
//         ]);

//         $game_sr = $request->input('game_sr');
//         $multiplier = $request->input('adminmultiply');

//         // Check if the game_sr already exists
//         if (AviatorResult::where('game_sr_num', $game_sr)->exists()) {
//             return response()->json(["message" => "Game Sr No. Already Exist", "status" => 400], 200);
//         }

//         // Create a new AviatorResult
//         $aviatorResult = AviatorResult::create([
//             'color' => 'Aviator',
//             'game_sr_num' => $game_sr,
//             'game_id' => 5,
//             'price' => $multiplier,
//             'status' => 1,
//             'datetime' => now(),
//         ]);

//         // Retrieve relevant bets
//         $bets = AviatorBet::where('game_sr_num', $game_sr)
//             ->where('game_id', 5)
//             ->where('status', 0)
//             ->where('win', 0)
//             ->where('result_status', 0)
//             ->get();

//         foreach ($bets as $bet) {
//             if ($bet->stop_multiplier > 0 && $bet->stop_multiplier <= $multiplier) {
//                 $win_amount = $bet->totalamount * $bet->stop_multiplier;

//                 // Update bet status
//                 $bet->update([
//                     'status' => 1,
//                     'result_status' => 1,
//                     'win' => $win_amount,
//                 ]);

//                 // Create a wallet history entry
//                 WalletHistory::create([
//                     'userid' => $bet->uid,
//                     'amount' => $win_amount,
//                     'subtypeid' => 24,
//                 ]);

//                 // Update user wallet
//                 $user = User::findOrFail($bet->uid);
//                 $user->increment('wallet', $win_amount);
//                 $user->increment('winning_wallet', $win_amount);
//             } else {
//                 // Update bet status for non-winning bets
//                 $bet->update([
//                     'status' => 2,
//                     'result_status' => 1,
//                 ]);
//             }
//         }

//         return response()->json(["message" => "Result declared, user bet history updated.", "status" => 200], 200);
//     }




  
    public function result_insert_new(Request $request) {
        
    //     $validator = Validator::make($request->all(), [
    //   'adminmultiply' => 'required|numeric|min:0',
    //         'game_sr' => 'required|string|max:255',
    // ]);

    // $validator->stopOnFirstFailure();

    // if ($validator->fails()) {
    //     return response()->json(['status' => 400, 'message' => $validator->errors()->first()],200);
    // }
    

        $datetime = now()->format('Y-m-d H:i:s');
        $game_sr = $request->game_sr;
        $multiplier = $request->adminmultiply;

        // Check if result already exists
        if (AviatorResult::where('game_sr_num', $game_sr)->exists()) {
            return response()->json(["message" => "Game Sr No. Already Exists", "status" => 400]);
        }

        // Insert new result
        $result = AviatorResult::create([
            'color' => 'Aviator',
            'game_sr_num' => $game_sr,
            'game_id' => 5,
            'price' => $multiplier,
            'status' => 1,
            'datetime' => $datetime,
        ]);

        if ($result) {
            // Update user bets
            $bets = AviatorBet::where('game_sr_num', $game_sr)
                ->where('game_id', 5)
                ->where('status', 0)
                ->where('win', 0)
                ->where('result_status', 0)
                ->get();

            foreach ($bets as $bet) {
                $id = $bet->id;
                $uid = $bet->uid;
                $stop_multiplier = $bet->stop_multiplier;

                if ($stop_multiplier != 0 && $stop_multiplier <= $multiplier) {
                    $trade_amount = $bet->totalamount;
                    $win_amount = $trade_amount * $stop_multiplier;

                    // Update bet status
                    $bet->update(['status' => 1, 'result_status' => 1, 'win' => $win_amount]);

                    // Insert into wallet history
                    WalletHistory::create(['userid' => $uid, 'amount' => $win_amount, 'type_id' => 4]);

                    // Update user wallet
                    $user = User::find($uid);
                    if ($user) {
                        $user->wallet += $win_amount;
                        $user->winning_wallet += $win_amount;
                        $user->save();
                    }
                } else {
                    // Update bet status for losing bets
                    $bet->update(['status' => 2, 'result_status' => 1]);
                }
            }

            return response()->json(["message" => "Result declared, user bet history updated.", "status" => 200]);
        }

        return response()->json(["message" => "Failed to insert result!", "status" => 400]);
    }




    
}