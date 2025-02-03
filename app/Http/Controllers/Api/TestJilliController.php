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


class TestJilliController extends Controller{

	   function end_user_register(){
		   	$mobile = 9999999999;
			$email = 'test@gmail.com';   
			$manager_key = 'FEGISo8cR74cf';
			$apiUrl = 'https://api.gamebridge.co.in/seller/v1/end-user-registration';
			$headers = ['authorization' => 'Bearer ' . $manager_key];
			$requestData  = ['email'=>$email,'mobile'=>$mobile];
			$requestData  = json_encode($requestData);
			$requestData  = base64_encode($requestData);
		    $payload = ['payload'=>$requestData];
		   
			try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payload);
				// Log response
			   // Log::info('PayIn API Response:', ['response' => $response->body()]);
			   // Log::info('PayIn API Status Code:', ['status' => $response->status()]);
				$apiResponse = json_decode($response->body());
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					  $account_token = $apiResponse->account_token;
					  $inserted_id = DB::table('users')->insertGetId(['mobile'=>$mobile,'email'=>$email,'account_token'=>$account_token ]);
					  return response()->json([
						  'status' => 200,
						  'message' => 'user registered successfully.',
						   'data' =>$apiResponse,'id'=>$inserted_id
					  ], 200); 
				}
				return response()->json(['status' => 400,'message' => 'Failed to register.', 'api_response' => $response->body()], 400);
			} catch (\Exception $e) {
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				return response()->json(['status' => 400, 'message' => 'Internal Server Error','error' => $e->getMessage()], 400);
			}
	   }
	
	function get_all_game_list(){
		    $apiUrl = 'https://api.gamebridge.co.in/seller/v1/get-all-games-list';
			$token = 'FEGISo8cR74cf';
			$headers = ['authorization' => 'Bearer ' .$token];
			$payload = ['payload'=>''];
		   	if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					  return response()->json([
						  'status' => 200,
						  'message' => 'Game list..',
						   'data' =>$apiResponse->data,
						  'fish' =>$apiResponse->fish,
						  'slot' =>$apiResponse->slot,
						  'tableandcard' =>$apiResponse->tableandcard,
						  'crash' =>$apiResponse->crash
					  ], 200); 
				}
				return response()->json(['status' => 400,'message' => 'Failed to get game list.', 'api_response' => $response->body()], 400);
			} catch (\Exception $e) {
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				return response()->json(['status' => 400, 'message' => 'Internal Server Error','error' => $e->getMessage()], 400);
			}
	   }
	
	function get_game_url_gameid(){
			    $user_id = 1;
				$game_id =403;
		        $apiUrl = 'https://api.gamebridge.co.in/seller/v1/get-game-url-by-gameid';
		        $account_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImppbGxpX2FjY291bnRfaWQiOiJsbjFqeWh1OG8yNmVxdW9leTN6bjMzZDA5byJ9LCJhbGdvcml0aG0iOiJSUzI1NiIsImlhdCI6MTczNTAxNjM4Mn0.X6gBhKzMlTSWTqc0KCBWdBSvGYqGFlsz1SPTtDiWWsc';
		        $manager_key = 'FEGISo8cR74cf';
		        $headers = [
							'authorization' => 'Bearer ' .$manager_key,
							'validateuser' => 'Bearer '.$account_token
						];
		       $pay_load = ['game_id'=>$game_id];
		       $pay_load = json_encode($pay_load);
		       $pay_load = base64_encode($pay_load);
		       $payloadpar = ['payload'=>$pay_load]; 
		   	try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
				$apiResponse = json_decode($response->body());
			    $data = $apiResponse->data;
		        $game_url = $data->Data;
				
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					  return response()->json([
						  'status' => 200,
						  'message' => 'Game url..',
						  'game_url'=>$game_url,
						   'data' =>$apiResponse->data,
					  ], 200); 
				}
				return response()->json(['status' => 400,'message' => 'Failed to get game list.', 'api_response' => $response->body()], 400);
			} catch (\Exception $e) {
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				return response()->json(['status' => 400, 'message' => 'Internal Server Error','error' => $e->getMessage()], 400);
			}
	   }

	function add_amount_to_user(){
		 $user_id = 1;
		$amount = 2;
		 $account_token ='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImppbGxpX2FjY291bnRfaWQiOiJsbjFqeWh1OG8yNmVxdW9leTN6bjMzZDA5byJ9LCJhbGdvcml0aG0iOiJSUzI1NiIsImlhdCI6MTczNTAxNjM4Mn0.X6gBhKzMlTSWTqc0KCBWdBSvGYqGFlsz1SPTtDiWWsc';
		$apiUrl = 'https://api.gamebridge.co.in/seller/v1/transfer-amount-to-user';
		$manager_key = 'FEGISo8cR74cf';
	    $headers = [
				'authorization' => 'Bearer ' .$manager_key,
				'validateuser' => 'Bearer '.$account_token
			];
		$pay_load = ['transfer_amount'=>$amount];
		$pay_load = json_encode($pay_load);
		$pay_load = base64_encode($pay_load);
		$payloadpar = ['payload'=>$pay_load];
		
		try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
				$apiResponse = json_decode($response->body());
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					return response()->json(['status'=>200,'message'=>$apiResponse->msg,'utr_no'=>$apiResponse->utr_no]);
				}
				return response()->json(['status'=>400,'message'=>$apiResponse->msg]);
			} catch (\Exception $e) {
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				return response()->json(['status'=>400,'message'=>$e->getMessage()]);
			}
	   }


	function get_jilli_transaction_details(){
		$user_id =1;
		$account_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImppbGxpX2FjY291bnRfaWQiOiJsbjFqeWh1OG8yNmVxdW9leTN6bjMzZDA5byJ9LCJhbGdvcml0aG0iOiJSUzI1NiIsImlhdCI6MTczNTAxNjM4Mn0.X6gBhKzMlTSWTqc0KCBWdBSvGYqGFlsz1SPTtDiWWsc';
		$apiUrl = 'https://api.gamebridge.co.in/seller/v1/get-jilli-transactons-details';
	    $manager_key = 'FEGISo8cR74cf';
	    $headers = [
					'authorization' => 'Bearer ' .$manager_key,
					'validateuser' => 'Bearer '.$account_token
				   ];
		$payloadpar = ['payload'=>''];
		try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
				$apiResponse = json_decode($response->body());
				// Check if API call was successful
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					  return response()->json([
						  'status' => 200,
						  'message' => 'Transaction details..',
						  'data' =>$apiResponse->data
					  ], 200); 
				}
				return response()->json(['status' => 400,'message' => 'Failed to get transaction details.', 'api_response' => $response->body()], 400);
			} catch (\Exception $e) {
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				return response()->json(['status' => 400, 'message' => 'Internal Server Error','error' => $e->getMessage()], 400);
			}
	   }


	function wallet_deduct_from_user(){
		   
	   }
	
	function get_bet_history(){
		   
	   }
	function get_user_info(){
		   
	   }
	
	function get_reseller_info(){
		
	}
	
}


