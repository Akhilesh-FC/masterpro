<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Bet,Card,AdminWinnerResult,User,Betlog,GameSetting,VirtualGame,BetResult,MineGameBet,PlinkoBet,PlinkoIndexList};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Helper\jilli;

use Illuminate\Support\Facades\DB;

class GameApiController extends Controller
{
	 public function gameSerialNo()
    {
        $date = now()->format('Ymd');
            // wingo
            $gamesNo1 = $date . "01" . "0001";
    		$gamesNo2 = $date . "02" . "0001";
    		$gamesNo3 = $date . "03" . "0001";
    		$gamesNo4 = $date . "04" . "0001";
    		// trx
    		$gamesNo6 = $date . "06" . "0001";
    		$gamesNo7 = $date . "07" . "0001";
    		$gamesNo8 = $date . "08" . "0001";
    		$gamesNo9 = $date . "09" . "0001";
    		// D & T
    		$gamesNo10 = $date . "10" . "0001";
		 	$gamesNo11 = $date . "11" . "0001";
		 	$gamesNo12 = $date . "12" . "0001";
		 	$gamesNo13 = $date . "13" . "0001";
    		
       	    DB::table('betlogs')->where('game_id', 1)
                          ->update(['games_no' => $gamesNo1]);
    		
    		DB::table('betlogs')->where('game_id', 2)
                          ->update(['games_no' => $gamesNo2]);
    		
    		DB::table('betlogs')->where('game_id', 3)
                          ->update(['games_no' => $gamesNo3]);
    		
    		DB::table('betlogs')->where('game_id', 4)
                          ->update(['games_no' => $gamesNo4]);
                          
            DB::table('betlogs')->where('game_id', 6)
                          ->update(['games_no' => $gamesNo6]);
    		
    		DB::table('betlogs')->where('game_id', 7)
                          ->update(['games_no' => $gamesNo7]);
    		
    		DB::table('betlogs')->where('game_id', 8)
                          ->update(['games_no' => $gamesNo8]);
    		
    		DB::table('betlogs')->where('game_id', 9)
                          ->update(['games_no' => $gamesNo9]);
    
            DB::table('betlogs')->where('game_id', 10)
                          ->update(['games_no' => $gamesNo10]);
		 
		 	DB::table('betlogs')->where('game_id', 11)
                          ->update(['games_no' => $gamesNo11]);
		 
		 	DB::table('betlogs')->where('game_id', 12)
                          ->update(['games_no' => $gamesNo12]);
		 
		 	DB::table('betlogs')->where('game_id', 13)
                          ->update(['games_no' => $gamesNo13]);
		 
    }	
	

	public function dragon_bet(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
    
        'game_id' => 'required',
      
        'json'=>'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
    }
    
    $datetime=date('Y-m-d H:i:s');
    
     $testData = $request->json;
    $userid = $request->userid;
    $gameid = $request->game_id;
  // $gameno = $request->game_no;
 
  $orderid = date('YmdHis') . rand(11111, 99999);
    
    $gamesrno=DB::select("SELECT games_no FROM `betlogs` WHERE `game_id`=$gameid  LIMIT 1");
    $gamesno=$gamesrno[0]->games_no;
 
   //dd($gamesno);
    
    foreach ($testData as $item) {
        $user_wallet = DB::table('users')->select('wallet')->where('id', $userid)->first();
            $userwallet = $user_wallet->wallet;
   
        $number = $item['number'];
        $amount = $item['amount'];
        
        $commission = $amount * 0.05; // Calculate commission   
    $betAmount = $amount - $commission; // Bet amount after commission deduction

        if($userwallet >= $amount){
      if ($amount>=1) {
        DB::insert("INSERT INTO `bets`(`amount`,`trade_amount`,`commission`, `number`, `games_no`, `game_id`, `userid`, `status`,`order_id`,`created_at`,`updated_at`) 
            VALUES ('$amount','$betAmount','$commission', '$number', '$gamesno', '$gameid', '$userid', '0','$orderid','$datetime','$datetime')");

        $data1 = DB::table('virtual_games')->where('game_id',$gameid)->where('number',$number)->first();
        $multiplier = $data1->multiplier;
        $num = $data1->actual_number;
       $multiply_amt = $multiplier*$amount;
       $bet_amt = DB::table('betlogs')->where('game_id',$gameid)->where('number',$num)->update([
           'amount'=>DB::raw("amount + $multiply_amt")
           ]);
       DB::table('users')
    ->where('id', $userid)
    ->update([
        'wallet' => DB::raw("wallet - ?", [$amount]),
        'recharge' => DB::raw("GREATEST(recharge - ?, 0)", [$amount]),
    ]);


      }
      }
      else {
                $response['msg'] = "Insufficient balance";
                $response['status'] = "400";
                return response()->json($response);
            }

    }

     return response()->json([
        'status' => 200,
        'message' => 'Bet Successfully',
    ]);   
    
}
	
public function dragon_bet_old(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required|exists:users,id',
        'game_id' => 'required|exists:betlogs,game_id',
        'json' => 'required|json',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
    }

    $datetime = now();
    $testData = json_decode($request->json, true); // Decode JSON string to array
    $user = User::find($request->userid);
    $gameId = $request->game_id;

    $orderId = now()->format('YmdHis') . rand(11111, 99999);

    $gamesno = Betlog::where('game_id', $gameId)->value('games_no');

    foreach ($testData as $item) {
        $number = $item['number'];
        $amount = $item['amount'];

        if ($user->wallet >= $amount && $amount >= 1) {
            // Create a new Bet
            Bet::create([
                'amount' => $amount,
                'trade_amount' => $amount,
                'number' => $number,
                'games_no' => $gamesno,
                'game_id' => $gameId,
                'userid' => $user->id,
                'status' => 0,
                'order_id' => $orderId,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);

            // Update the relevant bet log and user's wallet
            $virtualGame = VirtualGame::where('number', $number)->first();
            if ($virtualGame) {
                $multiplyAmt = $amount * $virtualGame->multiplier;
                Betlog::where('game_id', $gameId)
                    ->where('number', $virtualGame->actual_number)
                    ->increment('amount', $multiplyAmt);
            }

            $user->decrement('wallet', $amount);
            //$user->where('recharge', '>', 0)->decrement('recharge', $amount);
        } else {
            return response()->json([
                'msg' => "Insufficient balance",
                'status' => "400",
            ]);
        }
    }

    return response()->json([
        'status' => 200,
        'message' => 'Bet Successfully',
    ]);
}
	
	private function rebateCheck($userid, $gameid)
{
    $query = "
    WITH illegal_check AS (
        SELECT games_no, 
               userid,  -- Make sure userid is selected and properly grouped
               CASE 
                    WHEN SUM(number = 40) > 0 AND SUM(number = 50) > 0 
                        AND SUM(number = 10) > 0 AND SUM(number = 30) > 0 THEN 2
                    WHEN SUM(number = 40) > 0 AND SUM(number = 50) > 0 THEN 1
                    WHEN SUM(number = 10) > 0 AND SUM(number = 30) > 0 THEN 1
                    ELSE 0 
                END AS illegal
        FROM bets
        WHERE userid = ?
        GROUP BY games_no, userid  -- Group by both games_no and userid
    )
    SELECT userid, SUM(illegal) AS final_illegal_status
    FROM illegal_check WHERE userid=?
    GROUP BY userid;
    ";

    $results = DB::select($query, [$userid, $userid]);

    $illegle_count = $results[0]->final_illegal_status;
		
		////////////////start//////////////
		 // Update illegal_status in the bets table where conditions match
       DB::update("
    UPDATE bets 
    SET illegal_status = 1 
    WHERE userid = ? 
    AND games_no IN (
        SELECT games_no FROM bets 
        WHERE userid = ? 
        GROUP BY games_no 
        HAVING 
            SUM(number = 40) > 0 AND SUM(number = 50) > 0 
            OR SUM(number = 10) > 0 AND SUM(number = 30) > 0
    )
", [$userid, $userid]);

		
		//////////////end///////////

    // Check if it's the first time the count is being set
    $user = DB::select("SELECT illegal_count FROM users WHERE id = ?", [$userid]);
    $current_illegal_count = $user[0]->illegal_count;

    // First time offense (illegal_count is 0)
    if ($illegle_count == 1 || $illegle_count == 2) {
        // Multiply recharge by 5 and store the illegal_count on the first offense
        if ($current_illegal_count == 0) {
            DB::select("UPDATE `users` SET recharge = 5 * recharge, illegal_count = $illegle_count WHERE id = ? AND illegal_count = 0", [$userid]);
        } else {
            // If illegal_count is not 0, only store the illegal_count (for subsequent offenses)
            DB::select("UPDATE `users` SET illegal_count = $illegle_count WHERE id = ?", [$userid]);
        }
    }
    
    // For illegal_count >= 2, only update the illegal_count without changing recharge
    if ($illegle_count >= 2 && $current_illegal_count != $illegle_count) {
        DB::select("UPDATE `users` SET illegal_count = $illegle_count WHERE id = ?", [$userid]);
    }
}


	private function rebateCheckold($userid,$gameid)
	{
		$query = "
    WITH illegal_check AS (
        SELECT games_no, 
               userid,  -- Make sure userid is selected and properly grouped
               CASE 
			   		WHEN SUM(number = 40) > 0 AND SUM(number = 50) > 0 
                    AND SUM(number = 10) > 0 AND SUM(number = 30) > 0 THEN 2
                   WHEN SUM(number = 40) > 0 AND SUM(number = 50) > 0 THEN 1
                   WHEN SUM(number = 10) > 0 AND SUM(number = 30) > 0 THEN 1
                   ELSE 0 
               END AS illegal
        FROM bets
        WHERE userid = ?
        GROUP BY games_no, userid  -- Group by both games_no and userid
    )
    SELECT userid, SUM(illegal) AS final_illegal_status
    FROM illegal_check WHERE userid=?
    GROUP BY userid;
";

$results = DB::select($query, [$userid,$userid]);

		
		$illegle_count=$results[0]->final_illegal_status;

		if($illegle_count=='1')
		{
			//echo "Ram";
			DB::select("UPDATE `users` SET recharge=5*recharge,illegal_count=$illegle_count WHERE id='$userid' AND illegal_count=1");
		}
		if($illegle_count>='2')
		{
			//echo "shyam";
			DB::select("UPDATE `users` SET status=1 WHERE id='$userid'"); 
		}
		
		/*
		$bets = DB::table('bets')->where('userid', $userid)->where('game_id',$gameid)->get();

		$userBetsOnGame = [];
		$illegalCount = 0;

		foreach ($bets as $bet) {
			if (!in_array($bet->number, [40, 50])) {
				continue;
			}

			$key = $bet->userid . '_' . $bet->game_id . '_' . $bet->games_no;
			echo $key;
			echo "</br>";
			print_r($userBetsOnGame);

			if (!isset($userBetsOnGame[$key])) {
				$userBetsOnGame[$key] = [];
			}

			$userBetsOnGame[$key][] = $bet->number;
			print("=====================");
			print_r($userBetsOnGame);
			// Check if both 40 and 50 are in the same bet
			if (in_array(40, $userBetsOnGame[$key]) && in_array(50, $userBetsOnGame[$key])) {
				$illegalCount++;
			}
		}
		echo $illegalCount;die;

		// Get current user details
		$user = DB::table('users')->where('id', $userid)->first();

		// Pehli baar illegal bet pe recharge 5x karna hai (only recharge, no block)
		if ($illegalCount == 1 && $user) {
			DB::table('users')
				->where('id', $userid)
				->update(['recharge' => $user->recharge * 5]);
		}

		// Dusri baar illegal bet kare tabhi block hoga (status update)
		if ($illegalCount >= 2) {
			DB::table('users')
				->where('id', $userid)
				->update(['status' => 0]);
		}
		*/
	}
	public function bet(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'userid' => 'required|exists:users,id',
        'game_id' => 'required|exists:virtual_games,game_id',
        'number' => 'required',
        'amount' => 'required|numeric|min:1',
		'games_no' => 'required',
    ]);
	$ip=$_SERVER["REMOTE_ADDR"];

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }
    // $amt=$request->amount;
    // dd($amt);
	$uid=$request->userid;
	//dd($uid);
	//$update_wallet = jilli::update_user_wallet($uid);
    $user = User::findOrFail($request->userid);
    
	$amount=$request->amount;
    // Check user wallet balance
    if ($user->wallet < $request->amount) {
        return response()->json(['status' => 400, 'message' => 'Insufficient balance']);
    }

    $commission = $request->amount * 0.02; // Calculate commission 0.05
    //dd($commission); 
    $betAmount = $request->amount - $commission; // Net bet amount
    //dd($betAmount);
    // Get virtual games data
    $virtualGames = VirtualGame::where('number', $request->number)
        ->where('game_id', $request->game_id)
        ->get(['multiplier', 'actual_number']);

    // Create a new bet
	if($request->game_id=='6'){
		$data = DB::table('trx_one_min_result')->whereRaw('SECOND(blocktime) = 54')->orderBy('blocktime', 'desc')->limit(1)->get();

		// Check if data is not empty
		if ($data->isNotEmpty()) {
			// Access the first row of data
			$period = $data->first()->period;

			// Increment the period

			$games_no = $period + 1;
			//$periods=20250207103010663;

		} else {
			// Handle the case where no data is found
			$games_no = "" ;// Or any default value you want
			//$periods=20250207103010731;
		}
	}
	else{
		//$games_no=Betlog::where('game_id', $request->game_id)->value('games_no');
		$games_no=$request->games_no;
	}
    $bet = Bet::create([
        'amount' => $request->amount,
        'trade_amount' => $betAmount,
        'commission' => $commission,
        'number' => $request->number,
        'games_no' => $games_no,
        'game_id' => $request->game_id,
        'userid' => $user->id,
        'order_id' => now()->format('YmdHis') . rand(11111, 99999),
        'created_at' => now(),
        'updated_at' => now(),
        'status' => 0,
		'ip'=>$ip
    ]);
    //dd($bet);

    // Update bet logs
     // Update the bet logs
    foreach ($virtualGames as $game) {
        Betlog::where('game_id', $request->game_id)
            ->where('number', $game->actual_number)
            ->increment('amount', $request->amount * $game->multiplier);
    }

    // Check the user's recharge balance and deduct from wallet
    $recharge = $user->recharge;
		$this->rebateCheck($user->id,$request->game_id);

    if ($recharge >= $amount) {
        // If recharge balance is sufficient, deduct from both recharge and wallet
        DB::table('users')->where('id', $uid)->update([
            'recharge' => DB::raw("recharge - $amount"),
            'wallet' => DB::raw("wallet - $amount")
        ]);
    } else {
        // If recharge is insufficient, reset recharge to 0 and deduct from wallet
        DB::table('users')->where('id', $uid)->update([
            'recharge' => 0,
            'wallet' => DB::raw("wallet - $amount")
        ]);
    }

    // Return success response if bet is placed successfully
    return response()->json(['status' => 200, 'message' => 'Bet Successfully Placed']);
}


public function bet_old(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'userid' => 'required|exists:users,id',
        'game_id' => 'required|exists:virtual_games,game_id',
        'number' => 'required',
        'amount' => 'required|numeric|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }
    // $amt=$request->amount;
    // dd($amt);
	$uid=$request->userid;
	//dd($uid);
	//$update_wallet = jilli::update_user_wallet($uid);
    $user = User::findOrFail($request->userid);
    
	$amount=$request->amount;
    // Check user wallet balance
    if ($user->wallet < $request->amount) {
        return response()->json(['status' => 400, 'message' => 'Insufficient balance']);
    }

    $commission = $request->amount * 0.02; // Calculate commission 0.05
    //dd($commission); 
    $betAmount = $request->amount - $commission; // Net bet amount
    //dd($betAmount);
    // Get virtual games data
    $virtualGames = VirtualGame::where('number', $request->number)
        ->where('game_id', $request->game_id)
        ->get(['multiplier', 'actual_number']);

    // Create a new bet
    $bet = Bet::create([
        'amount' => $request->amount,
        'trade_amount' => $betAmount,
        'commission' => $commission,
        'number' => $request->number,
        'games_no' => Betlog::where('game_id', $request->game_id)->value('games_no'),
        'game_id' => $request->game_id,
        'userid' => $user->id,
        'order_id' => now()->format('YmdHis') . rand(11111, 99999),
        'created_at' => now(),
        'updated_at' => now(),
        'status' => 0,
    ]);
    //dd($bet);

    // Update bet logs
    foreach ($virtualGames as $game) {
        Betlog::where('game_id', $request->game_id)
            ->where('number', $game->actual_number)
            ->increment('amount', $betAmount * $game->multiplier);
    }
 DB::table('users')
    ->where('id', $userid)
    ->update([
        'wallet' => DB::raw("wallet - ?", [$amount]),
        'recharge' => DB::raw("GREATEST(recharge - ?, 0)", [$amount]),
    ]);
    // Update user's wallet and recharge
    //$user->decrement('wallet', $request->amount);
    //$user->decrement('recharge', $request->amount);
    $user->increment('today_turnover', $request->amount);
	
		
	///rebate check start  //
	
	 		$this->rebateCheck($user->id,$request->game_id);
	
	//rebate check end ///
	
	
	//$deduct_jili = jilli::deduct_from_wallet($uid,$amount);'jili'=>$deduct_jili

    return response()->json(['status' => 200, 'message' => 'Bet Successfully']);
}
	
	public function win_amountold(Request $request)
{
    $validator = Validator::make($request->all(), [ 
        'userid' => 'required|integer',
        'game_id' => 'required|integer',
        'games_no' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }

    $game_id = $request->game_id;
    $userid = $request->userid;
    $game_no = $request->games_no;
   
    $win_amount = Bet::selectRaw('SUM(win_amount) AS total_amount, games_no, game_id AS gameid, win_number AS number, 
        CASE WHEN SUM(win_amount) = 0 THEN "lose" ELSE "win" END AS result')
        ->where('games_no', $game_no)
        ->where('game_id', $game_id)
        ->where('userid', $userid)
        ->groupBy('games_no', 'game_id', 'win_number')
        ->first();

    // Check if data exists
    if (!$win_amount) {
        return response()->json(['msg' => 'No record found', 'status' => 400], 200);
    }

    // Assign total amount safely
    $amount = $win_amount->total_amount ?? 0;

    // Calculate tax only if amount > 0
   // if ($amount > 0) {
    //    $taxDeduction = $amount * 0.04;  // 4% tax
   //     $finalAmount = $amount - $taxDeduction;  // Final amount after tax deduction
   // } else {
   //     $finalAmount = 0;
  //  }

    $win = [
        'win' => $amount,	 
        'games_no' => $win_amount->games_no,
        'result' => $win_amount->result,
        'gameid' => $win_amount->gameid,
        'number' => $win_amount->number
    ];
        
    return response()->json([
        'message' => 'Successfully',
        'status' => 200,
        'data' => $win,
    ], 200);
}


public function win_amount(Request $request)
{
    $validator = Validator::make($request->all(), [ 
        'userid' => 'required|integer',
        'game_id' => 'required|integer',
        'games_no' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }

    $game_id = $request->game_id;
    $userid = $request->userid;
    $game_no = $request->games_no;
    
    // echo "$game_id,$userid,$game_no";
    // die;
   
    $win_amount = Bet::selectRaw('SUM(win_amount) AS total_amount, games_no, game_id AS gameid, win_number AS number, 
        CASE WHEN SUM(win_amount) = 0 THEN "lose" ELSE "win" END AS result')
        ->where('games_no', $game_no)
        ->where('game_id', $game_id)
        ->where('userid', $userid)
        ->groupBy('games_no', 'game_id', 'win_number')
        ->first();
       
    if ($win_amount) {
         $win = [
    'win' => $win_amount->total_amount,
    'games_no' => $win_amount->games_no,
    'result' => $win_amount->result,
    'gameid' => $win_amount->gameid,
    'number' => $win_amount->number
];
        
        return response()->json([
            'message' => 'Successfully',
            'status' => 200,
            'data' => $win,
            
        ], 200);
    } else {
        return response()->json(['msg' => 'No record found', 'status' => 400], 200);
    }
}

 public function results(Request $request)
{
    $validator = Validator::make($request->all(), [
        'game_id' => 'required',
        'limit' => 'required|integer',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }

    $game_id = $request->game_id;
    $limit = $request->limit;
    $offset = $request->offset ?? 0;
    $from_date = $request->from_date;
    $to_date = $request->to_date;
    $status = $request->status;

    // Build the query
    $query = BetResult::where('game_id', $game_id);

    if (!empty($from_date) && !empty($to_date)) {
        $query->whereBetween('created_at', [$from_date, $to_date]);
    }

    if (!empty($status)) {
        $query->where('status', $status);
    }

    // Retrieve the results with limit and offset
    $results = $query->orderBy('id', 'desc')
                     ->offset($offset)
                     ->limit($limit)
                     ->get();

    // Get the total count of bet_results for the game_id
    $total_result = BetResult::where('game_id', $game_id)->count();

    return response()->json([
        'status' => 200,
        'message' => 'Data found',
        'total_result' => $total_result,
        'data' => $results,
    ]);
}

//// last
public function lastFiveResults(Request $request)
{
    $validator = Validator::make($request->all(), [
        'game_id' => 'required',
        'limit' => 'required|integer'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }
    
    $game_id = $request->game_id;
    $limit = (int) $request->limit;
    $offset = (int) ($request->offset ?? 0);
    $from_date = $request->from_date;
    $to_date = $request->to_date;

    $query = BetResult::where('game_id', $game_id);

    // Apply date range filter if provided
    if ($from_date && $to_date) {
        $query->whereBetween('created_at', [$from_date, $to_date]);
    }

    // Fetch the results with limit and offset
    $results = $query
        ->orderBy('id', 'desc')
        ->offset($offset)
        ->limit($limit)
        ->get();

    return response()->json([
        'status' => 200,
        'message' => 'Data found',
        'data' => $results
    ]);
}

// last result ///
public function lastResults(Request $request)
{
    $validator = Validator::make($request->all(), [
        'game_id' => 'required',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }
    
    
    $game_id = $request->game_id;
    // $offset = (int) ($request->offset ?? 0);
    // $from_date = $request->from_date;
    // $to_date = $request->to_date;
    
    
    $results= BetResult::where('game_id', $game_id)->latest()->first();

    // $query = BetResult::where('game_id', $game_id);

    // // Apply date range filter if provided
    // if ($from_date && $to_date) {
    //     $query->whereBetween('created_at', [$from_date, $to_date]);
    // }

    // // Fetch the results with limit and offset
    // $results = $query
    //     ->orderBy('id', 'desc')
    //     ->offset($offset)
    //     ->limit(1)
    //     ->get();

    return response()->json([
        'status' => 200,
        'message' => 'Data found',
        'data' => $results
    ]);
}
	
public function bet_history(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'userid' => 'required|integer',
        'game_id' => 'required|integer',
        'limit' => 'integer|nullable',
        'offset' => 'integer|nullable',
        'from_date' => 'date|nullable',
        'to_date' => 'date|nullable',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }

    // Extract validated data
    $userid = $request->userid;
    $game_id = $request->game_id;
    $limit = $request->limit ?? 10000;
    $offset = $request->offset ?? 0;

    // Build the query
    $query = DB::table('bets')
        ->select('bets.*', 'game_settings.name AS game_name', 'virtual_games.name AS virtual_game_name')
        ->leftJoin('game_settings', 'game_settings.id', '=', 'bets.game_id')
        ->leftJoin('virtual_games', function ($join) {
            $join->on('virtual_games.game_id', '=', 'bets.game_id')
                 ->on('virtual_games.number', '=', 'bets.number');
        })
        ->where('bets.userid', $userid)
        ->where('bets.game_id', $game_id);

    // Apply date filters if provided
    if ($request->from_date) {
        $query->where('bets.created_at', '>=', $request->from_date);
    }

    if ($request->to_date) {
        $query->where('bets.created_at', '<=', $request->to_date);
    }
    // Apply pagination
    $results = $query->orderBy('bets.id', 'DESC')
                     ->offset($offset)
                     ->limit($limit)
                     ->distinct()
                     ->get();
    // Get total bets count for the user
   $total_bet = DB::table('bets')
    ->where('userid', $userid)
    ->where('game_id', $game_id)
    ->count(); 
      
    
    // Prepare the response
    if ($results->isNotEmpty()) {
        return response()->json([
            'status' => 200,
            'message' => 'Data found',
            'total_bets' => $total_bet,
            'data' => $results
        ]);
    } else {
        return response()->json([
            'status' => 200,
            'message' => 'No Data found',
            'data' => []
        ]);
    }
}
	
	 public function cron($game_id)
    {
              $per=DB::select("SELECT game_settings.winning_percentage as winning_percentage FROM game_settings WHERE game_settings.id=$game_id");
        $percentage = $per[0]->winning_percentage;  

            $gameno=DB::select("SELECT * FROM betlogs WHERE game_id=$game_id LIMIT 1");
            //
            
            ///
            $game_no=$gameno[0]->games_no;
             $period=$game_no;
            
				
            $sumamt=DB::select("SELECT SUM(amount) AS amount FROM bets WHERE game_id = '$game_id' && games_no='$game_no'");


				
            $totalamount=$sumamt[0]->amount;
		
            $percentageamount = $totalamount*$percentage*0.01; 
			
            $lessamount=DB::select(" SELECT * FROM betlogs WHERE game_id = '$game_id'  && games_no='$game_no' && amount <= $percentageamount ORDER BY amount asc LIMIT 1 ");
				if(count($lessamount)==0){
				$lessamount=DB::select(" SELECT * FROM betlogs WHERE game_id = '$game_id'  && games_no='$game_no' && amount >= '$percentageamount' ORDER BY amount asc LIMIT 1 ");
				}
            $zeroamount=DB::select(" SELECT * FROM betlogs WHERE game_id =  '$game_id'  && games_no='$game_no' && amount=0 ORDER BY RAND() LIMIT 1 ");
            $admin_winner=DB::select("SELECT * FROM admin_winner_results WHERE gamesno = '$game_no' AND gameId = '$game_id' ORDER BY id DESC LIMIT 1");
		 //dd($admin_winner);
            //  dd($admin_winner);
            $min_max=DB::select("SELECT min(number) as mins,max(number) as maxs FROM betlogs WHERE game_id=$game_id;");
        if(!empty($admin_winner)){
            echo 'a ';
            $number=$admin_winner[0]->number;
        }
      
        if (!empty($admin_winner)) {
            echo 'b ';
            $res=$number;
        } 
         elseif ( $totalamount< 450) {
             echo 'c ';
            $res= rand($min_max[0]->mins, $min_max[0]->maxs);
        }elseif($totalamount > 450){
            echo 'd ';
            $res=$lessamount[0]->number;
        }
        //$result=$number;
        $result=$res;
    
     
       //  $this->resultannounce($game_id,$period,$result);
				
				//$this->colour_prediction_and_bingo($game_id,$period,$result);
				// $this->trx($game_id,$period,$result);
				if ($game_id == 1 || $game_id == 2 || $game_id == 3 || $game_id == 4) {
    $this->colour_prediction_and_bingo($game_id, $period, $result);
					
} elseif ($game_id == 10 ) {
    $this->dragon_tiger($game_id, $period, $result);
} elseif ($game_id == 6 || $game_id == 7 || $game_id == 8 || $game_id == 9) {
    $this->trx($game_id, $period, $result);
}elseif ($game_id == 13 ) {
    $this->andarbaharpatta($game_id, $period, $result);
}
elseif ($game_id == 14 ) {
    $this-> head_tail($game_id, $period, $result);
}

            
                
    }


	private function andarbaharpatta($game_id,$period,$result)
     {
		 //dd($game_id);
      $lastimage=DB::select("SELECT cards.*, bet_results.random_card AS rand_card, bet_results.game_id AS gameiid,bet_results.id as rid FROM cards JOIN bet_results ON cards.card = bet_results.random_card WHERE bet_results.game_id = $game_id ORDER BY bet_results.id DESC LIMIT 1; 
");
		 //dd($lastimage);
 
            //card id
         $rescardid = $lastimage[0]->id ?? 1;
       
         $res=$lastimage[0]->card ?? 1;//  card number
             
        
     $randomNumber = rand(1, 11); 
     $evenNumber = $randomNumber * 2; 
    


 $randomNumbers = rand(1, 11); 
     $evenNumbersss = $randomNumbers % 2; 
      
if($evenNumbersss ==1){
$dragon=$randomNumbers;

}else{
    $dragon=$randomNumbers-1;
    
}
     //echo $dragon; 
     $limit=$dragon-1;
     $patta=DB::select("SELECT * FROM cards where card != $res  ORDER BY RAND(colour) LIMIT $limit");
     
     $pattafinal =DB::select("SELECT * FROM cards where card = $res  && id !=$rescardid ORDER BY RAND(id) LIMIT 1");
     $cards=array();
     foreach($patta as $item)
     {
  
     $image = $item->card;
     $cards[] = $image;

    
     }
    
      $cards[]=DB::select("SELECT * FROM cards where card = $res  && id !=$rescardid ORDER BY RAND(id) LIMIT 1")[0]->card ?? 1;
     $dataa=json_encode($cards);
    $nextresultcard =DB::select("SELECT * FROM cards where id !=$rescardid ORDER BY RAND(colour) LIMIT 1")[0]->card ?? 1;
    
   
    
     DB::select("INSERT INTO `bet_results`( `number`, `games_no`, `game_id`, `status`,`json`,`random_card`) VALUES ('$result','$period','$game_id','1','$dataa','$nextresultcard')"); 
     $this->amountdistributioncolors($game_id,$period,$result);
      DB::select("UPDATE `betlogs` SET amount=0,games_no=games_no+1 where game_id =  '$game_id'"); 
      

      
     return true;
     
  }  
  
 

private function colour_prediction_and_bingo($game_id, $period, $result)
{
    //echo"$game_id,$period,$res";
    // Fetch the colours associated with the given game_id and result
    $colours = VirtualGame::where('actual_number', $result)
        ->where('game_id', $game_id)
        ->where('multiplier', '!=', '1.5')
        ->pluck('name');
//dd($colours);
    // Convert the collection to JSON
    $pdata = json_encode($colours);
    //dd($pdata);
    // Insert the bet result
    BetResult::create([
        'number' => $result,
        'games_no' => $period,
        'game_id' => $game_id,
        'status' => 1,
        'json' => $pdata,
        'random_card' => $result
    ]);

    
    
    // Call the amount distribution method
    $this->amountdistributioncolors($game_id, $period, $result);
    // Update bet logs
    Betlog::where('game_id', $game_id)
        ->update(['amount' => 0, 'games_no' => \DB::raw('games_no + 1')]);

    return true;

}
/// trx ////

private function trx($game_id,$period,$result)
   {
      
       $colour=DB::select("SELECT `name` FROM `virtual_games` WHERE actual_number=$result && game_id=$game_id && `multiplier` !='1.5'");
      
       $tokens=$this->generateRandomString().$result;
		 
       $json=[];
       foreach ($colour as $item){
           $json[]=$item->name;
       }
       $pdata=json_encode($json);
		 $blockk = DB::table('bet_results')
            ->selectRaw('`block` + CASE 
                            WHEN ? = 6 THEN 20 
                            WHEN ? = 7 THEN 60 
                            WHEN ? = 8 THEN 100 
                            ELSE 200 
                          END AS adjusted_block', [$game_id, $game_id, $game_id])
            ->where('game_id', $game_id)
            ->orderByDesc('id')
            ->limit(1)
            ->first();
	$block=$blockk->adjusted_block;
       DB::select("
     INSERT INTO `bet_results` (`number`, `games_no`, `game_id`, `status`, `json`, `random_card`, `token`,`block`)VALUES ('$result', '$period', '$game_id', '1', '$pdata','$result', '$tokens','$block')");
          $this->amountdistributioncolors($game_id,$period,$result);
         DB::select("UPDATE `bets` SET `status`=2 WHERE `games_no`='$period' && `game_id`=  '$game_id' && number ='$result' && `status`=0;");
         DB::select("UPDATE `betlogs` SET amount=0,games_no=games_no+1 where game_id =  '$game_id';");
      return true;
			
   }
   
     private function generateRandomString($length = 4) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    $maxIndex = strlen($characters) - 1;

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $maxIndex)];
    }
    return $randomString;
}


private function dragon_tiger($game_id, $period, $result)
{
    $data = [];
    
    try {
        if ($result == 1) {
            $rand = rand(2, 13);
            $card1 = Card::where('card', '>', $rand)
                ->inRandomOrder()
                ->first();
                
            $rand2 = rand(2, $rand - 2);
            $card2 = Card::where('card', '>', $rand2)
                ->inRandomOrder()
                ->first();
                
            $data = [$card1->card ?? null, $card2->card ?? null];
        } elseif ($game_id == 2) {
            $rand = rand(2, 13);
            $card2 = Card::where('card', '>', $rand)
                ->inRandomOrder()
                ->first();
                
            $rand2 = rand(2, $rand - 2);
            $card1 = Card::where('card', '>', $rand2)
                ->inRandomOrder()
                ->first();
                
            $data = [$card1->card ?? null, $card2->card ?? null];
        } else {
            $rand = rand(2, 13);
            $card2 = Card::where('card', $rand)
                ->orderBy('id', 'asc')
                ->first();
                
            $card1 = Card::where('card', $rand)
                ->orderBy('id', 'desc')
                ->first();
                
            $data = [$card1->card ?? null, $card2->card ?? null];
        }

        $resJson = json_encode($data);
        
        BetResult::create([
            'number' => $result,
            'games_no' => $period,
            'game_id' => $game_id,
            'status' => 1,
            'json' => $resJson,
        ]);

        $this->amountDistributionColors($game_id, $period, $result);
        
        Betlog::where('game_id', $game_id)
            ->update(['amount' => 0, 'games_no' => DB::raw('games_no + 1')]);

    } catch (\Exception $e) {
        Log::error('Error in dragonTiger function: ' . $e->getMessage());
    }
}

private function amountdistributioncolors($game_id, $period, $result)
{
    $virtualGames = VirtualGame::where('actual_number', $result)
        ->where('game_id', $game_id)
        ->where(function ($query) {
            $query->where('type', '!=', 1)->where('multiplier', '!=', '1.5')
                  ->orWhere(function ($query) {
                      $query->where('type', 1)->where('multiplier', '1.5');
                  });
        })
        ->get();
    //dd($virtualGames);
    foreach ($virtualGames as $winAmount) {
        $multiple = $winAmount->multiplier;
        $number = $winAmount->number;
 
        if (!empty($number)) {
            // Update bet for result '0'
            //dd($number);
            if ($result == '0') {
                //dd("hii");
                $test= Bet::where('games_no', $period)
                    ->where('game_id', $game_id)
                    ->where('number', $result)
                    ->update(['win_amount' => DB::raw('(trade_amount * 9) - ((trade_amount * 9) * 4 / 100)'),
        'win_number' => '0',
        'status' => 1]);
                   //dd($test); 
            }
			
           $test1= Bet::where('games_no', $period)
                ->where('game_id', $game_id)
                ->where('number', $number)
                //->update(['win_amount' => DB::raw("(trade_amount * $multiple) - ((trade_amount * $multiple) * 4 / 100)"),
			   ->update(['win_amount' => DB::raw("trade_amount * $multiple"), 'win_number' => $result, 'status' => 1]);
       
        }
    }

    // Update users' wallets based on the winning amounts
    $winningBets = Bet::where('win_number', '>=', 0)
        ->where('games_no', $period)
        ->where('game_id', $game_id)
        ->get();

    foreach ($winningBets as $bet) {
        $amount = $bet->win_amount;
        $userId = $bet->userid;

      $amount = (float) $amount;
		 // Calculate 4% tax deduction
   // $taxDeduction = $amount * 0.04;  // 4% tax
   // $finalAmount = $amount - $taxDeduction;  // Final amount after tax deduction

User::where('id', $userId)
    ->update([
        'wallet' => DB::raw("wallet + {$amount}"),
            'winning_wallet' => DB::raw("winning_wallet + {$amount}"),
            'updated_at' => now()
    ]); 
		///jilli///
		
		$add_jili = jilli::add_in_jilli_wallet($userId, $amount);

		
		///end jilli////


    }

    // Update bets with no winning amount
    Bet::where('games_no', $period)
        ->where('game_id', $game_id)
        ->where('status', 0)
        ->where('win_amount', 0)
        ->update(['status' => 2, 'win_number' => $result]);
}


////// Mine Game Api ///////

public function mine_bet(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'game_id' => 'required',
        'amount' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }

    $userid = $request->userid;
    $gameid = $request->game_id;
    $amount = $request->amount;

    date_default_timezone_set('Asia/Kolkata');
    $datetime = now(); // Using Laravel's now() function
    $orderid = now()->format('YmdHis') . rand(11111, 99999);
    //dd($orderid);
    $tax = 0.00;
    $commission = $amount * $tax;
    $betAmount = $amount - $commission;

    $user = User::find($userid);
          
    if ($amount >= 10) {
        if ($user && $user->wallet >= $amount) {
            // Create the bet record
            MineGameBet::create([
                'amount' => $amount,
                'game_id' => $gameid,
                'userid' => $userid,
                'status' => 0,
                'created_at' => $datetime,
                'updated_at' => $datetime,
                'tax' => $tax,
                'after_tax' => $betAmount,
                'order_id' => $orderid   
            ]);

            // Update the user's wallet
            $user->decrement('wallet', $amount);

            return response()->json(['status' => 200, 'message' => 'Bet placed successfully'], 200);
        } else {
            return response()->json(['status' => 400, 'message' => 'Insufficient balance'], 400);
        }
    } else {
        return response()->json(['status' => 400, 'message' => 'Bet placed minimum 10 rupees'], 400);
    }
}

public function mine_cashout(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required|integer',
        'win_amount' => 'required|numeric',
        'multipler' => 'required|numeric',
        'status' => 'required|integer'
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
    }

    $userid = $request->userid;
    $win_amount = $request->win_amount;
    $status = $request->status;
    $multipler = $request->multipler;

    date_default_timezone_set('Asia/Kolkata');
    $datetime = now(); // Use Laravel's helper function for current timestamp

    $user = User::find($userid);
    if (!$user) {
        return response()->json(['status' => 400, 'message' => 'User does not exist'], 400);
    }

    $minegame_bet = MinegameBet::where('userid', $userid)
        ->where('Status', 0)
        ->orderBy('id', 'asc')
        ->first();

    if (!$minegame_bet) {
        return response()->json(['status' => 400, 'message' => 'No active minegame bet found for the user'], 400);
    }

    $minegame_bet->update([
        'Status' => $status,
        'multipler' => $multipler,
        'win_amount' => $win_amount
    ]);

    $user->increment('wallet', $win_amount); // This updates the wallet by adding the win_amount

    return response()->json([
        'status' => 200,
        'message' => 'CashOut successfully',
        'win_amount' => $win_amount
    ], 200);
}

public function mine_result(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    $userid = $request->userid;
    $limit = $request->limit ?? 0;
    $offset = $request->offset ?? 0;

    $query = MinegameBet::where('userid', $userid)
                        ->where(function ($query) {
                            $query->where('status', 1)
                                  ->orWhere('status', 2);
                        })
                        ->orderBy('id', 'DESC');

    // Apply pagination if limit is provided
    if ($limit > 0) {
        $data = $query->skip($offset)->take($limit)->get();
    } else {
        $data = $query->get();
    }

    $count = $query->count();

    if (!$data->isEmpty()) {  
        return response()->json([
            'status' => 200,
            'message' => 'Success',
            'count' => $count,
            'data' => $data
        ], 200);
    } else {
        return response()->json([
            'status' => 200,
            'message' => 'No data found'
        ], 200);
    }
}

public function mine_multiplier() 
{
    $multipliers = DB::table('mine_multipliers')
                ->select('id','name', 'multiplier')
                ->get(); // Use the Card model to fetch all records

    if ($multipliers->isNotEmpty()) { // Check if the collection is not empty
        $response['status'] = 200;
        $response['data'] = $multipliers;
    } else {
        $response['status'] = "400";
        $response['data'] = [];
    }

    return response()->json($response);
}

public function plinkoBet(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'game_id' => 'required',
        'amount' => 'required|numeric|min:0',
        'type' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }

    $userid = $request->userid;
	//$update_wallet = jilli::update_user_wallet($userid);
    $gameid = $request->game_id;
    $amount = $request->amount; 
    $type = $request->type; 
	date_default_timezone_set('Asia/Kolkata');
    $datetime = date('Y-m-d H:i:s');
    $orderid = date('YmdHis') . rand(11111, 99999);
    $tax = 0.00;
    $commission = $amount * $tax; // Calculate commission
    $betAmount = $amount - $commission;
    $userWallet = DB::table('users')->where('id', $userid)->value('wallet');
   if($amount >= 10){
       
    // DB::table('plinko_bet')->where('userid', $userid)->where('status', 0)->where('multipler', 0)->where('indexs', 0)->delete();
       
   $alreadyBet = DB::table('plinko_bets')->where('userid', $userid)->where('status', 0)->orderBy('id', 'DESC')->first();

    if (empty($alreadyBet)) {
        if ($userWallet >= $amount) {
           $plinkoBetId =  DB::table('plinko_bets')->insertGetId([
                'amount' => $amount,
                'game_id' => $gameid,
                'type' => $type,
                'userid' => $userid,
                'status' => 0,
                'created_at' => $datetime,
                'tax' => $tax,
                'after_tax' => $betAmount,
                'orderid' => $orderid
            ]);
            
            

            DB::update("UPDATE users SET wallet = wallet - $amount WHERE id = $userid");
			$deduct_jili = jilli::deduct_from_wallet($userid,$amount);
			
           $plinkoBet = DB::table('plinko_bets')->where('id',$plinkoBetId)->first();
            return response()->json(['status' => 200, 'message' => 'Bet placed successfully', 'data'=>$plinkoBet,'jili'=>$deduct_jili ], 200);
        } else {
            return response()->json(['status' => 400, 'message' => 'Insufficient balance'], 400);
        }
    } else {
       
        return response()->json(['status' => 400, 'message' => 'Already Bet placed'], 400);
         
    }
} else {
    return response()->json(['status' => 400, 'message' => 'Bet placed minimum 10 rupees'], 400);
}

}	
	
public function plinko_index_list(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'type' => 'required',
    ]);

    $validator->stopOnFirstFailure();
    
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }
    
    $type = $request->type;
    
    $data = DB::table('plinko_index_lists')
        ->where('type', $type)
        ->get();

    if (!$data->isEmpty()) {  
        return response()->json([
            'status' => 200,
            'message' => 'Success',
            'data' => $data
        ], 200);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'No data found'
        ], 400);
    }
}

	public function plinko_multiplier(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'userid' => 'required|integer',
        'index' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
    }

    $userid = $request->userid;
    $index = $request->index;
	date_default_timezone_set('Asia/Kolkata');	
    $datetime = date('Y-m-d H:i:s');

    $plinko_bet = DB::table('plinko_bets')
        ->where('userid', $userid)
        ->where('Status', 0)
        ->orderBy('id', 'asc')
        ->first();

    if (!$plinko_bet) {
        return response()->json(['status' => 400, 'message' => 'No active plinko bet found for the user'], 400);
    }

    $bet_amount = $plinko_bet->amount;
    $type = $plinko_bet->type;


    $index_multiplier = DB::table('plinko_index_lists')
        ->where('type', $type)
        ->where('indexs', $index)
        ->first();


    if (empty($index_multiplier)) {
        DB::table('plinko_bets')
            ->where('id', $plinko_bet->id)
            ->update(['Status' => 1, 'indexs' => $index, 'multipler' => 'out', 'win_amount' => 0]);

        return response()->json([
            'status' => 200,
            'message' => 'Plinko result calculated successfully',
            'win_amount' => '0'
        ], 200);
    }
    $multipler=$index_multiplier->multiplier;
  
    $win_amount = $bet_amount * $multipler;

 
    DB::table('plinko_bets')
        ->where('id', $plinko_bet->id)
        ->update(['Status' => 1, 'indexs' => $index, 'multipler' => $multipler,'win_amount' => $win_amount]);

     DB::update("UPDATE users SET wallet = wallet + $win_amount  WHERE id = $userid");
		
		///jilli///
		
		$add_jili = jilli::add_in_jilli_wallet($userid,$win_amount);

		
		///end jilli////
		
    return response()->json([
        'status' => 200,
        'message' => 'Plinko result calculated successfully',
        'win_amount' => $win_amount
    ],200);
} 

public function plinko_result(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
    ]);

    
    $validator->stopOnFirstFailure();
    
    
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }
    
   
    $userid = $request->userid;
    $limit = $request->limit??0;
	$offset = $request->offset ?? 0;


   if (empty($limit)) {
        $data = DB::table('plinko_bets')->where('userid', $userid)->where('status', 1)->orderBy('id', 'DESC')->get();
    } else {
        $data = DB::table('plinko_bets')->where('userid', $userid)->where('status', 1)->orderBy('id', 'DESC')->skip($offset)->take($limit)->get();
    }   
  
    if (!$data->isEmpty()) {  
        return response()->json([
            'status' => 200,
            'message' => 'Success',
            'data' => $data
        ], 200);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'No data found'
        ], 400);
    }
}


}
