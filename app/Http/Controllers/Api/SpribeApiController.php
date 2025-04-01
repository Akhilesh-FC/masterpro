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
	
	//// new api////
	
	public function spribe_user_register(Request $request){
	
         $validator = Validator::make($request->all(), [
					'userId' => 'required|unique:users,spribe_id'
					//'email' => 'required|email|unique:users,email'
				]);
				$validator->stopOnFirstFailure();
				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				}     
			$userid = $request->userId;
			//$email = $request->email;   

			$manager_key = 'FEGIS935E6Xun';
		$authorizationtoken='1740198329635';
			$apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/new-spribe-registration';
			
			    // Custom headers
			$headers = ['authorization' => 'Bearer ' . $manager_key,'authorizationtoken' => 'Bearer '.$authorizationtoken];

				//   request data //
			$requestData = ['userId' => $userid];
			$requestData  = json_encode($requestData);
			$requestData  = base64_encode($requestData);
		    $payload = ['payload'=>$requestData];
		
			try {
				// Make API request with headers and JSON body
				$response = Http::withHeaders($headers)->post($apiUrl, $payload);

				// Log response
			   // Log::info('PayIn API Response:', ['response' => $response->body()]);
			   // Log::info('PayIn API Status Code:', ['status' => $response->status()]);
                //dd($response->body());
				// Parse API response
				$apiResponse = json_decode($response->body());
				//dd($apiResponse);
				

				// Check if API call was successful
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					  $account_no = $apiResponse->userId;
					//dd($account_token);
					  $inserted_id = DB::table('users')->insertGetId(['spribe_id'=>$account_no]);
					  return response()->json([
						  'status' => 200,
						  'message' => 'user registered successfully.',
						   'data' =>$apiResponse,'id'=>$inserted_id
					  ], 200); 
				}

				// Handle API errors
				return response()->json(['status' => 400,'message' => 'Failed to register.', 'api_response' => $response->body()], 400);
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return response()->json(['status' => 400, 'message' => 'Internal Server Error','error' => $e->getMessage()], 400);
			}
       }
	
	
	public function spribe_transactons_details(Request $request){
                 $validator = Validator::make($request->all(), [
								'user_id' => 'required|exists:users,id'
							]);
				$validator->stopOnFirstFailure();
				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				} 
		$user_id = $request->user_id;
		$account_token = DB::table('users')->where('id',$user_id)->value('spribe_id');
		//dd($account_token);
		$apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/spribe-user-transaction-history';
	    $manager_key = 'FEGIS935E6Xun';
		$authorizationtoken='1740198329635';
	    $headers = [
					'authorization' => 'Bearer ' .$manager_key,
					'validateuser' => 'Bearer '.$account_token,
			'authorizationtoken' => 'Bearer '.$authorizationtoken
				   ];
		//dd($headers);
		 $payload = base64_encode(json_encode(['userId' => $account_token]));
		//dd($payload);
        $payloadpar = ['payload' => $payload];
		//$payloadpar = ['payload'=>''];
		
		try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
			    //dd($response);
				$apiResponse = json_decode($response->body());
			    //dd($apiResponse);
				// Check if API call was successful
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					  return response()->json([
						  'status' => 200,
						  'message' => 'Transaction details..',
						  'data' =>$apiResponse->data
					  ], 200); 
				}

				// Handle API errors
				return response()->json(['status' => 400,'message' => 'Failed to get transaction details.', 'api_response' => $response->body()], 400);
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return response()->json(['status' => 400, 'message' => 'Internal Server Error','error' => $e->getMessage()], 400);
			}
	}
	
	//deduct-newjilliuser-wallet-by-id
	
	public function scribe_deduct_from_wallet(Request $request){  
		 $validator = Validator::make($request->all(), [
								'user_id' => 'required|exists:users,id',
			                     'amount'=>'required|numeric|gt:0'
							]);
				$validator->stopOnFirstFailure();
				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				} 
		$user_id = $request->user_id;
		$amount = $request->amount;
		$account_token = DB::table('users')->where('id',$user_id)->value('spribe_id');
		$apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/deduct-spribe-user-balance';
	    $manager_key = 'FEGIS935E6Xun';
		$authorizationtoken='1740198329635';
	    $headers = [
					'authorization' => 'Bearer ' .$manager_key,
					'validateuser' => 'Bearer '.$account_token,
			'authorizationtoken' => 'Bearer '.$authorizationtoken
				   ];
	
		       $pay_load = ['amount'=>$amount,'spribe_id'=>$account_token];
		       $pay_load = json_encode($pay_load);
		       $pay_load = base64_encode($pay_load);
		       $payloadpar = ['payload'=>$pay_load];
		
		try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
				$apiResponse = json_decode($response->body());
			dd($apiResponse);
				// Check if API call was successful
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					  return response()->json([
						  'status' => 200,
						  'message' => 'Transaction details..',
						  'newBalance'=>$apiResponse->newBalance,
						  'utr_no'=>$apiResponse->utr_no
					  ], 200); 
				}

				// Handle API errors
				return response()->json(['status' => 400,'message' => 'Failed to get transaction details.', 'api_response' => $response->body()], 400);
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return response()->json(['status' => 400, 'message' => 'Internal Server Error','error' => $e->getMessage()], 400);
			}
	}
	
	
	
	
	/// end api//
	
	
	
	
	public function get_reseller_info(?string $manager_key=null){
		$manager_key = $manager_key??'FEGIS935E6Xun';
		$authorizationtoken='1740198329635';
		$apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/get-spribe-game-list';
		//$manager_key = 'FEGISo8cR74cf';
	    $headers = ['authorization' => 'Bearer ' .$manager_key,'authorizationtoken' => 'Bearer '.$authorizationtoken];
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
        'user_id' => 'required|exists:users,id',
        'game_id' => 'required',
       
    ]);
    
    $validator->stopOnFirstFailure();
    
    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }
    
    // Assigning the request data to variables
    $user_id = $request->user_id;
    $game_id = $request->game_id;
		$account_token = DB::table('users')
    ->where('id', $user_id)
    ->select('spribe_id', 'wallet')
    ->first();
       $spribe_id = $account_token->spribe_id;
$wallet = $account_token->wallet;

    $home_url ="https://jupitergames.app/";

    // API URL and Manager Key
    $apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/get-spribe-game-urls';
    $manager_key = 'FEGIS935E6Xun';
		$authorizationtoken='1740198329635';
    
    // Set up headers for the API request
    $headers = [
        'authorization' => 'Bearer ' . $manager_key,
		'authorizationtoken' => 'Bearer '.$authorizationtoken,
        'Content-Type' => 'application/json'  // Ensure content type is JSON
    ];

    // Build the payload array with the necessary fields
    $pay_load = [
        'userId' => $spribe_id,
        'gameId' => $game_id,
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
		
		
		$error = $apiResponse['error'];
$msg = $apiResponse['msg'];

$data = $apiResponse['data'];
$code = $data['code'];
$dataMsg = $data['msg'];
$gameLaunchUrl = $data['payload']['game_launch_url'];
		$this->update_spribe_wallets();
		
		//$url=$apiResponse->payload;
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
            'game_launch_url' => $gameLaunchUrl ?? null
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
		 //$this->winning_wallet_transfers($request);
		
  // dd($request);

    // Validate incoming request data
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'game_id' => 'required',
    ]);
    
    $validator->stopOnFirstFailure();
    
    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }
    
    // Assigning the request data to variables
    $user_id = $request->user_id;
    $game_id = $request->game_id;
		
		/// add winning_wallet_transfer///
		$user = User::findOrFail($user_id);
    $status = $user->status;
    $main_wallet = $user->wallet;
    $thirdpartywallet = $user->winning_wallet;
    $add_main_wallet = $main_wallet + $thirdpartywallet;
    //dd($add_main_wallet);
    if ($status == 1) { 
        $user->winning_wallet = $add_main_wallet;
        $user->wallet = 0;
        $user->save();
    }
		////end add winning_wallet_transfer///
 // First, update the wallet
    $this->update_spribe_wallets($request);
		
    $account_token = DB::table('users')
        ->where('id', $user_id)
        ->select('spribe_id', 'wallet')
        ->first();

    $spribe_id = $account_token->spribe_id;
    $wallet = $account_token->wallet;

    $home_url = "https://root.masterpro.vip/";

    // API URL and Manager Key
    $apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/get-spribe-game-urls';
    $manager_key = 'FEGIS935E6Xun';
		$authorizationtoken='1740198329635';

    // Set up headers for the API request
    $headers = [
        'authorization' => 'Bearer ' . $manager_key,
		'authorizationtoken' => 'Bearer '.$authorizationtoken,
        'Content-Type' => 'application/json'  // Ensure content type is JSON
    ];

    // Build the payload array with the necessary fields
    $pay_load = [
        'userId' => $spribe_id,
        'gameId' => $game_id,
        'home_url' => $home_url
    ];
//dd($pay_load);
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

        // Decode the JSON response from the API
        $apiResponse = json_decode($response->body(), true);
//dd($apiResponse);
        // Check if the API call was successful
        if ($response->successful() && isset($apiResponse['error']) && $apiResponse['error'] == false) {
            $gameLaunchUrl = $apiResponse['data']['payload']['game_launch_url'] ?? null;
            
            return response()->json([
                'error' => false,
                'msg' => 'Data fetched successfully',
                'data' => [
                    'code' => 0,
                    'msg' => 'Success',
                    'payload' => [
                        'game_launch_url' => $gameLaunchUrl
                    ]
                ]
            ], 200);
        }

        // Handle failed API response or no game URL
        return response()->json([
            'error' => true,
            'msg' => 'Something went wrong in node API\'s.',
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
	
	
	
	
	private function winning_wallet_transfers(Request $request)
{
		 //$this->update_spribe_wallets($request);
     $validator = Validator::make($request->all(), [
        'id' => 'required|exists:users,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ],200);
    }
    
    $id = $request->id;
    
    $user = User::findOrFail($id);
    $status = $user->status;
    $main_wallet = $user->wallet;
    $thirdpartywallet = $user->winning_wallet;
    $add_main_wallet = $main_wallet + $thirdpartywallet;
    
    if ($status == 1) { 
        $user->winning_wallet = $add_main_wallet;
        $user->wallet = 0;
        $user->save();

        $response = [
            'status' => 200,
            'message' => "Winning Wallet transfer Successfully ....!"
        ];

        return response()->json($response, 200);
    } else {
        $response = [
            'status' => 401,
            'message' => "User blocked by admin..!"
        ];
        return response()->json($response, 401);
    }
}



	
private function update_spribe_wallets(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
    ]);
    
    $validator->stopOnFirstFailure();
    
    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }

    $user_id = $request->user_id;
    $account_token = DB::table('users')
        ->where('id', $user_id)
        ->select('spribe_id', 'winning_wallet')
        ->first();

    $userId = $account_token->spribe_id;
    $wallet = $account_token->winning_wallet;

    $apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/assign-ame-spribe-user-balance';
    $manager_key = 'FEGIS935E6Xun';
	$authorizationtoken='1740198329635';
    $headers = [
        'authorization' => 'Bearer ' . $manager_key,
        'validateuser' => 'Bearer ' . $userId,
		'authorizationtoken' => 'Bearer '.$authorizationtoken
    ];
    $pay_load = ['amount' => $wallet, 'userId' => $userId];
    $pay_load = json_encode($pay_load);
    $pay_load = base64_encode($pay_load);
    $payloadpar = ['payload' => $pay_load];
    
    try {
        $response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
        $apiResponse = json_decode($response->body(), true);
        
        // Handle API success or failure
        if ($response->successful() && isset($apiResponse['error']) && $apiResponse['error'] == false) {
            return true; // Successfully updated the wallet
        }

        return response()->json(['status' => 400, 'message' => $apiResponse['msg']], 200);
    } catch (\Exception $e) {
        // Log exception
        Log::error('Wallet Update API Error:', ['error' => $e->getMessage()]);
        
        // Return server error response
        return response()->json(['status' => 400, 'message' => 'Internal Server Error', 'error' => $e->getMessage()], 400);
    }
}

	
	
	
	public function spribe_betting_history(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'startDate' => 'required',
		'endDate' => 'required',
		'page' => 'required|integer',
		'limit' => 'required|integer',
       
    ]);
    
    $validator->stopOnFirstFailure();
    
    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }
      $user_id = $request->user_id;
    $startDate = $request->startDate;
		 $endDate = $request->endDate;
		 $page = $request->page;
		 $limit = $request->limit;
		$spribe_id = DB::table('users')->where('id',$user_id)->value('spribe_id');
	
    $apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/get-spribe-game-spec-betting-history';
    $manager_key = 'FEGIS935E6Xun';
	$authorizationtoken='1740198329635';
    
    $headers = [
        'authorization' => 'Bearer ' . $manager_key,'authorizationtoken' => 'Bearer '.$authorizationtoken
        
    ];

    $pay_load = [
        'userId' => $spribe_id,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'page' => $page,
		'limit' => $limit
    ];
	
    $pay_load_json = json_encode($pay_load);
     
    $encoded_payload = base64_encode($pay_load_json);
    //dd($encoded_payload);
    try {
        $response = Http::withHeaders($headers)->post($apiUrl, [
            'payload' => $encoded_payload  
        ]);
		//dd($response);
        $apiResponse = json_decode($response->body(), true);
		//dd($apiResponse);		
		

        // Handle successful response
        if ($response->successful() && isset($apiResponse['error']) && !$apiResponse['error']) {
            return response()->json([
                'error' => false,
                'msg' => 'Data fetched successfully',
                'data' => [
                    'success' => $apiResponse['data']['success'] ?? false,
                    'message' => $apiResponse['data']['message'] ?? 'No message',
                    'count' => $apiResponse['data']['count'] ?? 0,
                    'bets' => $apiResponse['data']['data'] ?? []
                ]
            ], 200);
        }

        // Handle failure response
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

	public function spribe_all_betting_history(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'startDate' => 'required',
		'endDate' => 'required',
		'page' => 'required|integer',
		'limit' => 'required|integer',
       
    ]);
    
    $validator->stopOnFirstFailure();
    
    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }
      $user_id = $request->user_id;
    $startDate = $request->startDate;
		 $endDate = $request->endDate;
		 $page = $request->page;
		 $limit = $request->limit;
		$spribe_id = DB::table('users')->where('id',$user_id)->value('spribe_id');
	
    $apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/get-spribe-game--all-betting-history';
    $manager_key = 'FEGIS935E6Xun';
	$authorizationtoken='1740198329635';
    
    $headers = [
        'authorization' => 'Bearer ' . $manager_key,'authorizationtoken' => 'Bearer '.$authorizationtoken
        
    ];

    $pay_load = [
        'userId' => $spribe_id,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'page' => $page,
		'limit' => $limit
    ];
		//dd($pay_load);
	
    $pay_load_json = json_encode($pay_load);
     
    $encoded_payload = base64_encode($pay_load_json);
    //dd($encoded_payload);
    try {
        $response = Http::withHeaders($headers)->post($apiUrl, [
            'payload' => $encoded_payload  
        ]);
		//dd($response);
        $apiResponse = json_decode($response->body(), true);
		//dd($apiResponse);		
		

        // Handle successful response
        if ($response->successful() && isset($apiResponse['error']) && !$apiResponse['error']) {
            return response()->json([
                'error' => false,
                'msg' => 'Data fetched successfully',
                'data' => [
                    'success' => $apiResponse['data']['success'] ?? false,
                    'message' => $apiResponse['data']['message'] ?? 'No message',
                    'count' => $apiResponse['data']['count'] ?? 0,
                    'bets' => $apiResponse['data']['data'] ?? []
                ]
            ], 200);
        }

        // Handle failure response
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
	
	public function handleCallback(Request $request)
    {
        // Callback se data retrieve karein
        $data = $request->all();

        // Response prepare karein
        return response()->json([
            'success' => true,
            'message' => 'Callback Received',
            'data'    => $data,
        ], 200);
    }

	
	public function add_in_spribe_wallet(Request $request){
		 $validator = Validator::make($request->all(), [
								'user_id' => 'required|exists:users,id',
		                    	'amount'=>'required|numeric|gt:0'
							]);
				$validator->stopOnFirstFailure();
				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				} 
		
		$user_id = $request->user_id;
		$amount = $request->amount;
		 $account_token = DB::table('users')->where('id',$user_id)->value('spribe_id');
		$apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/add-spribe-user-balance';
		$manager_key = 'FEGIS935E6Xun';
	$authorizationtoken='1740198329635';
	    $headers = [
				'authorization' => 'Bearer ' .$manager_key,
				'validateuser' => 'Bearer '.$account_token,
			'authorizationtoken' => 'Bearer '.$authorizationtoken
			];
		$pay_load = ['amount'=>$amount,'userId'=>$account_token];
		$pay_load = json_encode($pay_load);
		$pay_load = base64_encode($pay_load);
		$payloadpar = ['payload'=>$pay_load];
		
		try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
				$apiResponse = json_decode($response->body());
			   //dd($apiResponse);
				// Check if API call was successful
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					return response()->json(['status'=>200,'message'=>$apiResponse->msg]);
				}
				// Handle API errors
				return response()->json(['status'=>400,'message'=>$apiResponse->msg]);
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return response()->json(['status'=>400,'message'=>$e->getMessage()]);
			}
	}
	
	
	public function update_spribe_wallet_old(Request $request){
		 $validator = Validator::make($request->all(), [
								'user_id' => 'required|exists:users,id',
		                    	//'amount'=>'required|numeric|gt:0'
							]);
				$validator->stopOnFirstFailure();
				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				} 
		
		$user_id = $request->user_id;
		//$amount = $request->amount;
		$account_token = DB::table('users')
    ->where('id', $user_id)
    ->select('spribe_id', 'wallet')
    ->first();
		$userId=$account_token->spribe_id;
		$wallet=$account_token->wallet;

		//dd($account_token);
		$apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/assign-ame-spribe-user-balance';
		$manager_key = 'FEGIS935E6Xun';
	$authorizationtoken='1740198329635';
		
	    $headers = [
				'authorization' => 'Bearer ' .$manager_key,
				'validateuser' => 'Bearer '.$userId,
			'authorizationtoken' => 'Bearer '.$authorizationtoken
			];
		$pay_load = ['amount'=>$wallet,'userId'=>$userId];
		$pay_load = json_encode($pay_load);
		$pay_load = base64_encode($pay_load);
		$payloadpar = ['payload'=>$pay_load];
		
		try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
				$apiResponse = json_decode($response->body());
			   //dd($apiResponse);
				// Check if API call was successful
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					return response()->json(['status'=>200,'message'=>$apiResponse->msg]);
				}
				// Handle API errors
				return response()->json(['status'=>400,'message'=>$apiResponse->msg]);
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return response()->json(['status'=>400,'message'=>$e->getMessage()]);
			}
	}
	
	
	
	public function update_spribe_wallet(Request $request){
		 $validator = Validator::make($request->all(), [
								'user_id' => 'required|exists:users,id',
		                    	//'amount'=>'required|numeric|gt:0'
							]);
				$validator->stopOnFirstFailure();
				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				} 
		
		$user_id = $request->user_id;
		//$amount = $request->amount;
		$account_token = DB::table('users')
    ->where('id', $user_id)
    ->select('spribe_id', 'wallet')
    ->first();
		$userId=$account_token->spribe_id;
		$wallet=$account_token->wallet;

		//dd($account_token);
		$apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/assign-ame-spribe-user-balance';
		$manager_key = 'FEGIS935E6Xun';
	$authorizationtoken='1740198329635';
	    $headers = [
				'authorization' => 'Bearer ' .$manager_key,
				'validateuser' => 'Bearer '.$userId,
			'authorizationtoken' => 'Bearer '.$authorizationtoken
			];
		$pay_load = ['amount'=>$wallet,'userId'=>$userId];
		$pay_load = json_encode($pay_load);
		$pay_load = base64_encode($pay_load);
		$payloadpar = ['payload'=>$pay_load];
		
		try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
				$apiResponse = json_decode($response->body());
			   //dd($apiResponse);
				// Check if API call was successful
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					return response()->json(['status'=>200,'message'=>$apiResponse->msg]);
				}
				// Handle API errors
				return response()->json(['status'=>400,'message'=>$apiResponse->msg]);
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return response()->json(['status'=>400,'message'=>$e->getMessage()]);
			}
	}
	
	
	public function get_spribe_wallet(Request $request){
		 $validator = Validator::make($request->all(), [
								'user_id' => 'required|exists:users,id'
							]);
				$validator->stopOnFirstFailure();
				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				} 
		
		$user_id = $request->user_id;
		$amount = $request->amount;
		 $account_token = DB::table('users')->where('id',$user_id)->value('spribe_id');
		//dd($account_token);
		$apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/get-spribe-user-balance';
		$manager_key = 'FEGIS935E6Xun';
	$authorizationtoken='1740198329635';
	    $headers = [
				'authorization' => 'Bearer ' .$manager_key,
				'validateuser' => 'Bearer '.$account_token,
			'authorizationtoken' => 'Bearer '.$authorizationtoken
			];
		$pay_load = ['userId'=>$account_token];
		//dd($pay_load);
		$pay_load = json_encode($pay_load);
		$pay_load = base64_encode($pay_load);
		$payloadpar = ['payload'=>$pay_load];
		//dd($payloadpar);
		try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
				$apiResponse = json_decode($response->body());
			//dd($apiResponse);
			    $data = $apiResponse->data;

// Ensure $winning_wallet is defined properly
//$winning_wallet = $data; // Assuming $data contains the required values
//dd($winning_wallet);
// Assign the values to $wallet and $winning_wallet
$wallet = $data[0]->sprb_user_wallet;
			//dd($wallet);

				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					return response()->json(['status'=>200,'message'=>$apiResponse->msg,'data'=>$wallet]);
				}
				// Handle API errors
				return response()->json(['status'=>400,'message'=>$apiResponse->msg]);
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return response()->json(['status'=>400,'message'=>$e->getMessage()]);
			}
	}
	
	
	
	public function update_spribe_to_user_wallet(Request $request){
		 $validator = Validator::make($request->all(), [
								'user_id' => 'required|exists:users,id'
							]);
				$validator->stopOnFirstFailure();
				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				} 
		
		$user_id = $request->user_id;
		//dd($user_id);
		//$amount = $request->amount;
		 $account_token = DB::table('users')->where('id',$user_id)->value('spribe_id');
		//dd($account_token);
		$apiUrl = 'https://spribe.gamebridge.co.in/seller/v1/get-spribe-user-balance';
		$manager_key = 'FEGIS935E6Xun';
	$authorizationtoken='1740198329635';
	    $headers = [
				'authorization' => 'Bearer ' .$manager_key,
				'validateuser' => 'Bearer '.$account_token,
			'authorizationtoken' => 'Bearer '.$authorizationtoken
			];
		$pay_load = ['userId'=>$account_token];
		$pay_load = json_encode($pay_load);
		$pay_load = base64_encode($pay_load);
		$payloadpar = ['payload'=>$pay_load];
		
		try {
				$response = Http::withHeaders($headers)->post($apiUrl, $payloadpar);
				$apiResponse = json_decode($response->body());
			//dd($apiResponse);
			    $data = $apiResponse->data;

// Ensure $winning_wallet is defined properly
$winning_wallet = $data; // Assuming $data contains the required values

// Assign the values to $wallet and $winning_wallet
$wallet = $data[0]->sprb_user_wallet;
			DB::table('users')
    ->where('id', $user_id) // Find the user by ID
    ->update(['wallet' => $wallet]); // Update the wallet field

				// Check if API call was successful
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					return response()->json(['status'=>200,'message'=>$apiResponse->msg,'data'=>$combined]);
				}
				// Handle API errors
				return response()->json(['status'=>400,'message'=>$apiResponse->msg]);
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return response()->json(['status'=>400,'message'=>$e->getMessage()]);
			}
	}
	
	
	
	
	
}