<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Betlog;
use App\Models\AdminWinnerResult;
use App\Models\GameSetting;
use Illuminate\Support\Facades\Storage;
use DB;

class ColourPredictionController extends Controller
{
//   public function colour_prediction_create($gameid)
//     {
//         $bets = DB::select("SELECT betlogs.*,game_settings.winning_percentage AS parsantage ,game_settings.id AS id FROM `betlogs` LEFT JOIN game_settings ON betlogs.game_id=game_settings.id where betlogs.game_id=$gameid Limit 10;");
//         //dd($bets);

//         return view('colour_prediction.index')->with('bets', $bets)->with('gameid', $gameid);
//     }



public function colour_prediction_create($gameid)
{
    // Fetch bets with game settings' winning percentage using models
    $bets = Betlog::where('game_id', $gameid)
                ->leftJoin('game_settings', 'betlogs.game_id', '=', 'game_settings.id')
                ->select('betlogs.*', 'game_settings.winning_percentage as parsantage', 'game_settings.id as game_setting_id')
                ->limit(10)
                ->get();

    return view('colour_prediction.index')->with('bets', $bets)->with('gameid', $gameid);
}


    // public function fetchData($gameid)
    // {
    //     $bets = DB::select("SELECT betlogs.*,game_settings.winning_percentage AS parsantage ,game_settings.id AS id FROM `betlogs` LEFT JOIN game_settings ON betlogs.game_id=game_settings.id where betlogs.game_id=$gameid Limit 10;");

    //     return response()->json(['bets' => $bets, 'gameid' => $gameid]);
    // }
    
    
	
	public function fetchData($gameid)
{
    $bets = BetLog::with('gameSetting:id,winning_percentage')
        ->where('game_id', $gameid)
        ->limit(10)
        ->get(['*']); // Adjust columns as necessary

    return response()->json(['bets' => $bets, 'gameid' => $gameid]);
}

	
// 	public function store(Request $request)
// 	{
// 	    //dd($request);
	    
// 	    $validatedData = $request->validate([
//             'game_no' => 'required|unique:admin_winner_results,gamesno',
//         ]);
		
// // 	$datetime=now();
// 	  //$gamesno=$request->gamesno+1;
//       $gameid=$request->game_id;
// 		 $gamesno=$request->game_no;
//          $number=$request->number;
	
		 
//         DB::insert("INSERT INTO `admin_winner_results`( `gamesno`, `gameId`, `number`, `status`) VALUES ('$gamesno','$gameid','$number','1')");
         
        
//              return redirect()->back(); 
// 	}
  
  public function store(Request $request)
{
    // Validate the incoming request data
    $validatedData = $request->validate([
        'game_no' => 'required|unique:admin_winner_results,gamesno',
    ]);

    // Retrieve data from the request
    $gameid = $request->game_id;
    $gamesno = $request->game_no;
    $number = $request->number;


    // Use the model to create a new record
    AdminWinnerResult::create([
        'gamesno' => $gamesno,
        'gameId' => $gameid,
        'number' => $number,
        'status' => 1,
    ]);

    // Redirect back after storing the record
    return redirect()->back();
}
  
//   public function update(Request $request)
//       {
	   

// 	   $gamid=$request->id;
	
//         $parsantage=$request->parsantage;
//               $data= DB::select("UPDATE `game_settings` SET `winning_percentage` = '$parsantage' WHERE `id` ='$gamid'");
	         
         
//              return redirect()->back();
          
//       }


   
     public function update(Request $request)
{
    $gameId = $request->id;
    $percentage = $request->parsantage;

    // Find the record by id and update the winning percentage
    $gameSetting = GameSetting::find($gameId);
    if ($gameSetting) {
        $gameSetting->winning_percentage = $percentage;
        $gameSetting->save();
    }

    return redirect()->back();
} 

}
