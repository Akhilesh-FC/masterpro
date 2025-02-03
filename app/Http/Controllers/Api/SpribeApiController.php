<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\Slider;
use App\Models\BankDetail; // Import your model
use Carbon\Carbon;
use App\Models\Payin;
use App\Models\WalletHistory;
use App\Models\withdraw;
use App\Models\GiftCard;
use App\Models\GiftClaim;
use App\Models\CustomerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class SpribeApiController extends Controller
{
	public function get_reseller_info(?string $manager_key=null){
		$manager_key = $manager_key??'FEGISo8cR74cf';
		$apiUrl = 'https://api.gamebridge.co.in/seller/v1/get-spribe-game-list';
		//$manager_key = 'FEGISo8cR74cf';
	    $headers = ['authorization' => 'Bearer ' .$manager_key];
		//dd($headers);
		
		try {
			
				$response = Http::withHeaders($headers)->get($apiUrl);
				//dd($response->body());
				$apiResponse = json_decode($response->body());
			    // dd($apiResponse);
			
			
               if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					return response()->json(['status'=>200,'message'=>$apiResponse,]);
				}
				// Handle API errors
				return response()->json(['status'=>400,'message'=>$apiResponse]);
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return response()->json(['status'=>400,'message'=>$e->getMessage()]);
			}
	}
	
	public function get_spribe_game_urls_old(Request $request)
{
    // Validate incoming request data
    $validator = Validator::make($request->all(), [
        'userId' => 'required|exists:users,id',
        'gameId' => 'required',
        'money' => 'required',
        'home_url' => 'required'
    ]);
    
    $validator->stopOnFirstFailure();
    
    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }
    
    // Assigning the request data to variables
    $user_id = $request->userId;
    $game_id = $request->gameId;
    $money = $request->money;
    $home_url = $request->home_url;

    // API URL and Manager Key
    $apiUrl = 'https://api.gamebridge.co.in/seller/v1/get-spribe-game-urls';
    $manager_key = 'FEGISo8cR74cf';
    
    // Set up headers for the API request
    $headers = [
        'authorization' => 'Bearer ' . $manager_key
    ];
		//dd($headers);

    // Build the payload array with the necessary fields
    $pay_load = [
        'userId' => $user_id,
        'gameId' => $game_id,
        'money' => $money,
        'home_url' => $home_url
    ];
		//dd($pay_load);

    // Convert the payload array to JSON
    $pay_load_json = json_encode($pay_load);
    // Base64 encode the JSON string
    $encoded_payload = base64_encode($pay_load_json);
		
    // Final payload structure
   // $payloadpar = ['payload' => $encoded_payload];
		$payloadpar=$encoded_payload;
    //dd($payloadpar);
    try {
        // Send the POST request
        $response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
        //dd($response->body());
        // Decode the JSON response from the API
        $apiResponse = json_decode($response->body(), true);
		//dd($apiResponse);
        
        // Check if the API call was successful and if the error flag is false
        if ($response->successful() && isset($apiResponse['error']) && $apiResponse['error'] == false) {
            return response()->json([
                'error' => false,
                'msg' => 'Data fetched successfully',
                'data' => [
                    'code' => 0,
                    'msg' => 'Success',
                    'payload' => [
                        'game_launch_url' => $apiResponse['data']['game_launch_url'] ?? null
                    ]
                ]
            ], 200); 
        }

        // Handle failed API response
        return response()->json([
            'error' => true,
            'msg' => 'Something went wrong in node api\'s..',
        ], 400);

    } catch (\Exception $e) {
        // Log exception
        Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
        
        // Return server error response
        return response()->json([
            'status' => 400, 
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 400);
    }
}

	public function get_spribe_game_urls(Request $request)
{
    // Validate incoming request data
    $validator = Validator::make($request->all(), [
        'userId' => 'required|exists:users,id',
        'gameId' => 'required',
        'money' => 'required',
        'home_url' => 'required'
    ]);
    
    $validator->stopOnFirstFailure();
    
    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }
    
    // Assigning the request data to variables
    $user_id = $request->userId;
    $game_id = $request->gameId;
    $money = $request->money;
    $home_url = $request->home_url;

    // API URL and Manager Key
    $apiUrl = 'https://api.gamebridge.co.in/seller/v1/get-spribe-game-urls';
    $manager_key = 'FEGISo8cR74cf';
    
    // Set up headers for the API request
    $headers = [
        'authorization' => 'Bearer ' . $manager_key,
        'Content-Type' => 'application/json'  // Ensure content type is JSON
    ];

    // Build the payload array with the necessary fields
    $pay_load = [
        'userId' => $user_id,
        'gameId' => $game_id,
        'money' => $money,
        'home_url' => $home_url
    ];

    // Convert the payload array to JSON
    $pay_load_json = json_encode($pay_load);

    // Base64 encode the JSON string if required by the API
    $encoded_payload = base64_encode($pay_load_json);
    
    // Send the POST request with the correctly formatted JSON payload
    try {
        // Send the POST request
        $response = Http::withHeaders($headers)->post($apiUrl, [
            'payload' => $encoded_payload  // Ensure the payload is wrapped inside a 'payload' field
        ]);
         //dd($response->body());
        // Decode the JSON response from the API
        $apiResponse = json_decode($response->body(), true);
        
        // Check if the API call was successful and if the error flag is false
        if ($response->successful() && isset($apiResponse['error']) && $apiResponse['error'] == false) {
            return response()->json([
                'error' => false,
                'msg' => 'Data fetched successfully',
                'data' => [
                    'code' => 0,
                    'msg' => 'Success',
                    'payload' => [
                        'game_launch_url' => $apiResponse['data']['game_launch_url'] ?? null
                    ]
                ]
            ], 200); 
        }

        // Handle failed API response
        return response()->json([
            'error' => true,
            'msg' => 'Something went wrong in node api\'s..',
        ], 400);

    } catch (\Exception $e) {
        // Log exception
        Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
        
        // Return server error response
        return response()->json([
            'status' => 400, 
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 400);
    }
}

	
}