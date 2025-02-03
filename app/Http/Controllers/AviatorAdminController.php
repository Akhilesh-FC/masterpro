<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\{AviatorResult,AviatorBet,AviatorAdminResult,AviatorSalary,AviatorSetting,Betlog,GameSetting};
use Illuminate\Support\Facades\Storage;
class AviatorAdminController extends Controller
{
  
      
  public function aviator_prediction_create(string $game_id)
    {
	    
	    $perPage = 10;
	   
		$results = DB::table('aviator_results')
			->join('game_settings', 'aviator_results.game_id', '=', 'game_settings.id')
			->where('aviator_results.game_id', $game_id)
			->orderByDesc('aviator_results.id')
			->first();

        $aviator_res = DB::table('aviator_results')->where('game_id',5)->orderByDesc('id')->paginate($perPage);

	   
        return view('aviator.result')->with('results', $results)->with('game_id', $game_id)->with('aviator_res',$aviator_res);
    }

// public function aviator_prediction_create(string $game_id)
// {
//     $perPage = 10;

//     // Fetching the latest result for the specified game using models
//     $results = AviatorResult::where('game_id', $game_id)
//         ->orderByDesc('id')
//         ->first();

//     // Fetching paginated results for the aviator game with game_id 5
//     $aviator_res = AviatorResult::where('game_id', 5)
//         ->orderByDesc('id')
//         ->paginate($perPage);

//     return view('aviator.result')
//         ->with('results', $results)
//         ->with('game_id', $game_id)
//         ->with('aviator_res', $aviator_res);
// }


    public function aviator_fetchDatacolor($game_id)
    {
		
        $bets = DB::select("SELECT betlogs.*,game_settings.winning_percentage AS percentage ,game_settings.id AS id FROM `betlogs` LEFT JOIN game_settings ON betlogs.game_id=game_settings.id where betlogs.game_id=$game_id Limit 10");

        return response()->json(['bets' => $bets, 'game_id' => $game_id]);
    }
	
// 	public function aviator_fetchDatacolor($game_id)
// {
//     $bets = BetLog::select('betlogs.*', 'game_settings.winning_percentage as percentage', 'game_settings.id as id')
//         ->leftJoin('game_settings', 'betlogs.game_id', '=', 'game_settings.id')
//         ->where('betlogs.game_id', $game_id)
//         ->limit(10)
//         ->get();

//     return response()->json(['bets' => $bets, 'game_id' => $game_id]);
// }
	
	public function aviator_store(Request $request)
	{
		
		  	date_default_timezone_set('Asia/Kolkata');
          $datetime = date('Y-m-d H:i:s');
		
	  
      $game_id =$request->game_id;
		 $game_sr_num =$request->game_sr_num;
		 $number=$request->number;
      $multiplier =$request->multiplier;
    //   dd($multiplier);
		
		DB::table('aviator_admin_results')->insert([
		'game_sr_num'=>$game_sr_num,
        'game_id'=>$game_id,
			'number'=>$multiplier,
			'multiplier'=>$multiplier,
			'status'=>1,
			'created_at'=>$datetime
		]);
		
             return redirect()->back(); 
	}

// public function aviator_store(Request $request)
// {
//     date_default_timezone_set('Asia/Kolkata');
//     $datetime = date('Y-m-d H:i:s');

//     AviatorAdminResult::create([
//         'game_sr_num' => $request->game_sr_num,
//         'game_id' => $request->game_id,
//         'number' => $request->multiplier,
//         'multiplier' => $request->multiplier,
//         'status' => 1,
//         'created_at' => $datetime,
//     ]);

//     return redirect()->back();
// }

  
  
  public function aviator_update(Request $request)
      {
	   
	     	date_default_timezone_set('Asia/Kolkata');
          $datetime = date('Y-m-d H:i:s');
	   
	   $game_id=$request->game_id;
        $percentage = $request->percentage;
	   
         //$data= DB::select("UPDATE `game_setting` SET `percentage` = '$percentage','datetime'='$datetime' WHERE `id` ='$gamid'");
	  $data =  DB::table('game_settings')->where('id',$game_id)->update(['winning_percentage'=>$percentage]);
         if($data){
        return redirect()->back();
		 }else{
			 return 'Can not update';
		 }
      }
//   public function aviator_update(Request $request)
// {
//     date_default_timezone_set('Asia/Kolkata');
//     $datetime = date('Y-m-d H:i:s');

//     $game_id = $request->game_id;
//     $percentage = $request->percentage;

//     // Assuming `GameSetting` is the model representing `game_settings` table
//     $gameSetting = GameSetting::find($game_id);

//     if ($gameSetting) {
//         $gameSetting->winning_percentage = $percentage;
//         $gameSetting->updated_at = $datetime; // Optional, if you want to set the current timestamp manually
//         $gameSetting->save();

//         return redirect()->back();
//     } else {
//         return 'Cannot update';
//     }
// }

	
// 	  public function aviator_bet_history(string $game_id)
//     {
// 		  $perPage = 10;

// 			$bets = DB::table('aviator_bets')
// 				->select('aviator_bets.id as id','aviator_bets.game_sr_num as game_sr_num','aviator_bets.amount as amount', 'aviator_bets.win as win','aviator_bets.created_at as datetime','users.u_id as username', 'users.mobile as mobile')
// 				->where('aviator_bets.game_id', $game_id)
// 				->join('users', 'aviator_bets.uid', '=', 'users.id')
// 				->orderByDesc('aviator_bets.id')
// 				->paginate($perPage);
		  
// 	    return view('aviator.bet')->with('bets', $bets); 
// 	  }
	
	public function aviator_bet_history(string $game_id)
{
    $perPage = 10;

    $bets = AviatorBet::select('aviator_bets.id as id', 'aviator_bets.game_sr_num as game_sr_num', 
                'aviator_bets.amount as amount', 'aviator_bets.win as win', 
                'aviator_bets.created_at as datetime', 'users.u_id as username', 
                'users.mobile as mobile')
            ->where('aviator_bets.game_id', $game_id)
            ->join('users', 'aviator_bets.uid', '=', 'users.id')
            ->orderByDesc('aviator_bets.id')
            ->paginate($perPage);

    return view('aviator.bet')->with('bets', $bets);
}


}


