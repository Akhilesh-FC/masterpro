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
use DateTime;
use DateTimeZone;
use App\Models\Payin;
use App\Models\WalletHistory;
use App\Models\withdraw;
use App\Models\GiftCard;
use App\Models\{GiftClaim,Version};
use App\Models\CustomerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Helper\jilli;

class PublicApiController extends Controller
{
	
	public function usdt_account_view(Request $request)
	{
		// Validate the request
		$validator = Validator::make($request->all(), [
			'user_id' => 'required|numeric',
		]);

		$validator->stopOnFirstFailure();

		if ($validator->fails()) {
			return response()->json([
				'status' => 400,
				'message' => $validator->errors()->first()
			], 400);
		}

		$userid = $request->user_id;

		// Fetch account details using query binding to avoid SQL injection
		$accountDetails = DB::select("SELECT * FROM `usdt_account_deatils` WHERE user_id = ?", [$userid]);

		if (!empty($accountDetails)) {
			return response()->json([
				'status' => 200,
				'message' => 'Successfully retrieved account details.',
				'data' => $accountDetails
			], 200);
		} else {
			return response()->json([
				'status' => 400,
				'message' => 'No record found.',
				'data' => []
			], 400);
		}
	}

	public function add_usdt_account(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'user_id' => 'required',
			'name' => 'required',
			'usdt_wallet_address' => 'required|unique:usdt_account_deatils,usdt_wallet_address',
		]);

		$validator->stopOnFirstFailure();

		if ($validator->fails()) {
			$response = [
				'status' => 400,
				'message' => $validator->errors()->first()
			]; 

			return response()->json($response, 400);
		}

		$datetime = now();
		$data = [
			'user_id' => $request->input('user_id'),
			'name' => $request->input('name'),
			'usdt_wallet_address' => $request->input('usdt_wallet_address'),
			'status' => 1,
			'created_at' => $datetime,
			'updated_at' => $datetime,
		];

		$inserted = DB::table('usdt_account_deatils')->insert($data);

		if ($inserted) {
			$response = [
				'status' => 200,
				'message' => 'Add USDT Account Successfully ..!',
			];

			return response()->json($response, 200);
		} else {
			$response = [
				'status' => 400,
				'message' => 'Internal error..!',
			];
			return response()->json($response, 400);
		}
	}

	
	public function country(Request $request)
	{
		$search = $request->input('search');

		// Fetch all columns from the country table with search on multiple columns
		$query = DB::table('country');

		if (!empty($search)) {
			$query->where('sortname', 'LIKE', "%{$search}%")
				->orWhere('name', 'LIKE', "%{$search}%")
				->orWhere('phone_code', 'LIKE', "%{$search}%");
		}

		$countries = $query->get();

		return response()->json([
			'status' => 'success',
			'data' => $countries,
		]);
	}

				public function payin_usdt(Request $request)
			{
				$validator = Validator::make($request->all(), [
					'user_id' => 'required|exists:users,id',
					'amount' => 'required|numeric|gt:0',
					'type' => 'required|in:2',
				]);

				$validator->stopOnFirstFailure();

				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				}

				$user_id = $request->user_id;
				$amount = $request->amount;
				$type = $request->type;
					$inr_amt=$amount * 94;
					
                $email = 'techjupiter3133@gmail.com'; 
				$token = '57682082025451629461939305377137'; // Replace with a secure token or config value
				$apiUrl = "https://cryptofit.biz/Payment/coinpayments_api_call";
				$coin = 'USDT.BEP20';
					
				do {
					$orderId = str_pad(mt_rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
				} while (DB::table('payins')->where('order_id', $orderId)->exists());
					
				// Check if user exists and email matches
				$user_exist = DB::table('users')->where('id', $user_id)->first();

				// Prepare data for PayIn API request
				$formData = [
					'txtamount' => $amount,
					'coin' => $coin,
					'UserID' => $email,
					'Token' => $token,
					'TransactionID' => $orderId,
				];

				try {
					// Make API request
					$response = Http::asForm()->post($apiUrl, $formData);

					// Log response
					Log::info('PayIn API Response:', ['response' => $response->body()]);
					Log::info('PayIn API Status Code:', ['status' => $response->status()]);

					// Parse API response
					$apiResponse = json_decode($response->body());

					// Check if API call was successful
					if ($response->successful() && isset($apiResponse->error) && $apiResponse->error === 'ok') {
						// Deduct amount from wallet
						
						// Insert payin record
						$inserted_id = DB::table('payins')->insertGetId([
							'user_id' => $user_id,
							'status' => 1,
							'order_id' => $orderId,
							'cash' => $inr_amt,
							'usdt_amount'=>$amount,
							'type' => $type,
						]);
						
						return response()->json([
							'status' => 200,
							'message' => 'Payment initiated successfully.',
							'data' => $apiResponse,
						], 200);
					}

					// Handle API errors
					return response()->json([
						'status' => 400,
						'message' => 'Failed to initiate payment.',
						'api_response' => $response->body(),
					], 400);
				} catch (\Exception $e) {
					// Log exception
					Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
					// Return server error response
					return response()->json(['status' => 400, 'message' => 'Internal Server Error'], 400);
				}
			}

	
	
	
	public function payin_call_back(Request $request){
		
		$validator = Validator::make($request->all(), [
					'invoice' => 'required',
					'status_text' => 'required',
					'amount' => 'required'
				]);

				$validator->stopOnFirstFailure();

				if ($validator->fails()) {
					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
				}
		
		$invoice = $request->invoice;
		$status_text = $request->status_text;
		$amount = $request->amount;
		if($status_text=='complete'){
			
          $a =  DB::table('payins')->where('order_id',$invoice)->update(['status'=>2]);
			
			if($a){
				$user_detail = Payin::where('order_id', $invoice)
                            ->where('status', 2)
                            ->first();
				$user_id=$user_detail->user_id;
				$amount1=$user_detail->cash;
				//$update_wallet = jilli::update_user_wallet($user_id);
				$update=User::where('id', $user_id)->update(['wallet' => $amount1]);
				$add_jili = jilli::add_in_jilli_wallet($user_id,$amount1);
				return response()->json(['status'=>200,'message'=>'Payment successful.'],200);
			}else{
			   return response()->json(['status'=>400,'message'=>'Failed to update!'],400);
			}
		}else{
           return response()->json(['status'=>400,'message'=>'Something went wrong!'],400);
		}
	}
	
	
	
	
	
	public function activity_rewards(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        $datetime = date('Y-m-d H:i:s');
        //  $date = date('Y-m-d');
        //  date_default_timezone_set('Asia/Kolkata');
        $date = now()->format('Y-m-d');
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $bet_amount = DB::table('bets')
    ->where('userid', $userid)
    ->whereDate('created_at', '=', $date)
    ->sum('amount');

$invite_bonus = DB::select("
        SELECT 
            a.id AS activity_id,
            a.amount,
            a.range_amount,
            a.name,
           
            COALESCE(c.status, '1') AS status,
            COALESCE(a.created_at, 'Not Found') AS created_at
        FROM 
            activity_rewards a
        LEFT JOIN 
            activity_rewards_claim c 
        ON 
            a.id = c.acyivity_reward_id 
        AND 
            c.userid = ?
        ORDER BY 
            a.id ASC
    ", [$userid]);
   //dd($invite_bonus);
    

    
        if (!empty($invite_bonus)) {
            $response = [
                'message' => 'activity rewards List',
                'status' => 200,
                'bet_amount'=>$bet_amount,
                'data' => $invite_bonus
                
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
	
	////Pay Modes ////


public function pay_modes(Request $request)
{
    if ($request->isMethod('get')) {
        $userid = $request->input('userid');
		$type = $request->input('type');
		if($type == ''){
        $check = DB::table('users')->where('first_recharge', '1')->where('id', $userid)->first();

        $pay_modes = DB::table('pay_modes')->where('status', '1')->get();

        if ($pay_modes->isNotEmpty()) {
            $response['msg'] = "Successfully";
            $response['data'] = $pay_modes->toArray();

            if ($check && $check->first_recharge == '1') {
                $response['minimum'] = 500;
                $response['status'] = 200;
            } else {
                $response['minimum'] = 100;
                $response['status'] = 200;
            }

            return response()->json($response);
        } else {
            // If no data is found, set an appropriate response
            $response['msg'] = "No record found";
            $response['status'] = "400";
            return response()->json($response);
        }
	 } else {
        $check = DB::table('users')->where('first_recharge', '1')->where('id', $userid)->first();

        $pay_modes = DB::table('pay_modes')->where('status', '1')->where('type', $type)->get();

        if ($pay_modes->isNotEmpty()) {
            $response['msg'] = "Successfully";
            $response['data'] = $pay_modes->toArray();

            if ($check && $check->first_recharge == '1') {
                $response['minimum'] = 500;
                $response['status'] = "200";
            } else {
                $response['minimum'] = 100;
                $response['status'] = "400";
            }

            return response()->json($response);
        } else {
            // If no data is found, set an appropriate response
            $response['msg'] = "No record found";
            $response['status'] = "400";
            return response()->json($response);
        }
    }
    } else {
        return response()->json(['error' => 'Unsupported request method'], 400);
    }
}

    
    
    ///// activity_rewards history ////
    
     public function activity_rewards_history(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'user_id' => 'required|numeric',
         'type_id'=>'required',
         
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->user_id;  
     $subtypeid = $request->type_id;  
       // $userid = $request->input('userid');

       $act_reward_hist=DB::select("SELECT wallet_histories.*,types.name as name FROM `wallet_histories` LEFT JOIN types ON wallet_histories.type_id=types.id WHERE wallet_histories.user_id=$userid && wallet_histories.type_id=$subtypeid");
       
  

        if (!empty($act_reward_hist)) {
            $response = [
                'message' => 'activity rewards List',
                'status' => 200,
                'data' => $act_reward_hist,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
	
		/// About us Api ///
	
      public function about_us(Request $request)
      {
		  $validator = Validator::make($request->all(), [
        'type' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $type = $request->type;
 
		  
		  
        $about_us = DB::select("SELECT `name`,`description` FROM `settings` WHERE `type`=$type;
");

        if ($about_us) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $about_us
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found', 'status' => 400,
                'data' => []], 400);
        }
    }
	
	
	// commission details //////
    
     public function commission_details(Request $request)
        {
             $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'type_id'=>'required|integer',
            'date'=>'nullable'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
             $userid = $request->user_id;
             $subtypeid= $request->type_id;
             $date=$request->created_at;
    
           $commission=DB::select("SELECT * FROM `wallet_histories` WHERE `user_id`=$userid && `type_id`=$subtypeid &&`created_at` LIKE '%$date%'");
           
          $data=[];
    foreach ($commission as $item){
        
           
           $amount=$item->amount;
           $description=$item->description;
           $description2=$item->description_2;
           $created_at=$item->created_at;
           $updated_at=$item->updated_at;
        }
        
        
         $data[] = [
             'number_of_bettors'=>$description2,
             'bet_amount'=>$description,
             'commission_payout'=>$amount,
             'date'=>$created_at,    
             'settlement_date'=>$updated_at       
             ];
              
    
            if (!empty($data)) {
                $response = [
                    'message' => 'commission_details',
                    'status' => 200,
                    'data' => $data,
                ];
                return response()->json($response);
            } else {
                return response()->json(['message' => 'Not found..!','status' => 400,
                    'data' => []], 400);
            }
        }
    
    public function activity_rewards_claim(Request $request)
{
     $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric',
        'amount' => 'required',
        'activity_id'=>'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->userid;
    $amount = $request->amount;
    $activity_id=$request->activity_id;
    $bonusClaim = DB::table('activity_rewards_claim')
                ->where('userid', $userid)
                ->where('acyivity_reward_id', $activity_id)
                ->get();
                // dd($bonusClaim);
                
if($bonusClaim->isEmpty()){
$user = DB::table('users')->where('id', $userid)->first();
if (!empty($user)) {
   $usser= DB::table('users')->where('id', $userid)->update([
        'wallet' => $user->wallet + $amount, // Add amount to wallet
    ]);
}else{
 return response()->json([
				'message' => 'user not found ..!',
				'status' => 400,
                ], 400);
 }
 if (!empty($usser)) {
    // Insert into wallet_histories
    $bonuss=DB::table('wallet_histories')->insert([
        'user_id'     => $userid,
        'amount'      => $amount,
        'description' => 'Activity_reward_daily',
        'type_id'     => 12, // Define type_id as 1 for bonus claim
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);
    
     $bonuss=DB::table('activity_rewards_claim')->insert([
        'userid'     => $userid,
        'acyivity_reward_id' => $activity_id,
        'status' => 0,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);
 }else{
 
 }
     if (!empty($bonuss)) {
            $response = [
                'message' => 'invitation bonus claimed successfully!',
                'status' => 200,
            ];
            return response()->json($response,200);
        } else {
            return response()->json([
				'message' => 'Bonus not claimed ..!',
				'status' => 400,
                ], 400);
        }
        
       } else{
         return response()->json([
				'message' => 'Already claimed ..!',
				'status' => 400,
                ], 400);  
       }
	}
	
	 	// profile Update Api //
    public function update_profile(Request $request)
    {
		$validator = Validator::make($request->all(), [
        'id' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

        
        $id = $request->id;
        
        $value = User::findOrFail($id);
        $status=$value->status;
        
        	if($status == 1)
        {
        if (!empty($request->name)) {
            $value->name = $request->name;
        }
        
        if (!empty($request->image) && $request->image != "null") {
            $value->image = $request->image;
        }
    
        // Save the changes
        $value->save();
    
        $response = [
            'status' => 200,
            'message' => "Successfully updated"
        ];
    
        return response()->json($response, 200);
        }else{
             $response['message'] = "User block by admin..!";
                    $response['status'] = "401";
                    return response()->json($response,401);
        }
    }
    
	public function image_all(){
      
         $user = DB::select("SELECT `image` FROM `all_images`
");
          if($user){
          $response =[ 'success'=>"200",'data'=>$user,'message'=>'Successfully'];return response ()->json ($response,200);
      }
      else{
       $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
      } 
    }
	
	
//     public function registers(Request $request)
// {
//     // Validate the incoming request
//     $validator = Validator::make($request->all(), [
//         'mobile' => 'required|string|size:10|unique:users',
//         'password' => 'required|string|min:8',
//         'referral_code' => 'nullable|string|exists:users,referral_code'
//     ]);

//     // Return validation error if it fails
//     if ($validator->fails()) {
//         return response()->json([
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ]);
//     }

//     // Generate random name and referral code
//     $randomName = 'User_' . Str::random(5);
//     $randomReferralCode = 'win' . Str::random(8);
    
//     // Get base URL
//     $baseUrl = URL::to('/');
    
//     // Prepare data for insertion using Eloquent model
//     $data = [
//         'name' => $randomName,
//         'mobile' => $request->mobile,
//         'password' => $request->password, // Hash the password
//         'image' => $baseUrl . "/image/download.png",
//         'status' => 1,
//         'referral_code' => $randomReferralCode,
//     ];
//       //dd($data);
//     // Check for referral code and set referrer ID if applicable
//     if ($request->has('referral_code')) {
//         $referrer = User::where('referral_code', $request->referral_code)->first();
//         if ($referrer) {
//             $data['referrer_id'] = $referrer->id;
//         }
//     }

//     // Create user using Eloquent model
//     $user = User::create($data);

//     // Return success response with user data
//     return response()->json([
//         'status' => 200,
//         'message' => 'User registered successfully',
//         'data' => $user
//     ]);
// }

/// web register uses only ////

	public function otp_register(Request $request){
    try {    
        // Validate input
        $validator = Validator::make($request->all(), [
            'mobile' => ['required', 'string', 'regex:/^\d{10}$/'], // Ensure 10 digits
            'otp' => 'required',
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ]; 

            return response()->json($response, 400);
        }

        $mobile = $request->mobile; // Define $mobile from the request
        $username = Str::random(6); // Generate random username
        $u_id = $this->generateRandomUID();
        $referral_code = Str::upper(Str::random(6)); // Generate referral code
        $rrand = rand(1, 20);
        $all_image = All_image::find($rrand);     
        $image = $all_image->image;
               
        $exist_user = User::where('mobile', $mobile)->where('type', 1)->first();
               
        if (!empty($exist_user)) {
            // Update existing user with new OTP
            $exist_user->otp = $request->otp;
            $exist_user->save();

            return response()->json([
                'status' => 200,
                'message' => 'OTP updated successfully for existing user.',
                'userid' => $exist_user->id,
                'mobile' => $exist_user->mobile,
            ]);   
        } else {
            // Insert new user into the database
            $userId = DB::table('users')->insertGetId([
                'mobile' => $mobile,
                'otp' => $request->otp,
                'name' => $username,
                'referral_code' => $referral_code,
                'u_id' => $u_id,
                'status' => 1,
                'type' => 1,
                'image' => $image,
                'created_at' => now()
            ]);

            if ($userId) {
                $user = DB::table('users')->where('id', $userId)->first();
                $response = [
                    'status' => 200,
                    'message' => 'User is created successfully.',
                    'userid' => $userId,
                    'mobile' => $user->mobile
                ];

                return response()->json($response);
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'Failed to register!'
                ];

                return response()->json($response, 400);
            }
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/// web register uses only ////
private function generateSecureRandomString($length = 8)
{
	//$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Only uppercase letters
    $characters = '0123456789'; // You can expand this to include more characters if needed.
    $randomString = '';

    // Loop to generate the random string
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $randomString;
}

  
	
      public function registers(Request $request){

    //dd($request);
    // Validate the incoming request
    $validator = Validator::make($request->all(), [
   // 'email' => 'required|email|unique:users,email',
	'country_code' => 'required',
    'mobile' => 'required|numeric|digits:10|unique:users,mobile',
    'password' => 'required|min:8',
    'password_confirmation' => 'required|min:8|same:password', // Add the 'same' rule
    'referral_code' => 'required|string|exists:users,referral_code',
]);
// Return validation error if it fails
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ],200);
    }
//dd($request);
    // Generate random name and referral code
    $randomName = 'User_' . strtoupper(Str::random(5));
    $randomReferralCode = 'ZUP' . strtoupper(Str::random(4));

		  // $email = $request->email;
		  $mobile = $request->mobile;
    
    // Get base URL
    $baseUrl = URL::to('/');
    $uid=$this->generateSecureRandomString(6);
    // Prepare data for insertion using Eloquent model
    $data = [
        'name' => $randomName,
        'u_id' => $uid,
        'mobile' => $request->mobile, // Ensure this is not null
        'password' =>$request->password, 
        'image' => $baseUrl . "/image/download.png",
        'status' => 1,
        'referral_code' => $uid,
        'wallet' => 28,
		'country_code' => $request->country_code,
		//'email'=>$email
    ];

    // Check for referral code and set referrer ID if applicable
    if ($request->has('referral_code')) {
        $referrer = User::where('referral_code', $request->referral_code)->first();
        if ($referrer) {
            $data['referrer_id'] = $referrer->id;
        }
    }
 
	 //$manager_key = 'FEGISo8cR74cf';
     //$apiUrl = 'https://api.gamebridge.co.in/seller/v1/end-user-registration';
	 //$headers = ['authorization' => 'Bearer ' . $manager_key];  
		  //	$requestData  = ['email'=>$email,'mobile'=>$mobile];
			//$requestData  = json_encode($requestData);
			//$requestData  = base64_encode($requestData);
		    //$payload = ['payload'=>$requestData];
		  
		  			try {
				// Make API request with headers and JSON body
				//$response = Http::withHeaders($headers)->post($apiUrl, $payload);
				//$apiResponse = json_decode($response->body());

				// Check if API call was successful
				//if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					 // $account_token = $apiResponse->account_token;
					 // $data['account_token'] = $account_token;
					  $user = User::create($data);
					if($user){
					 $success['userId'] = $user->id;
                     $success['token'] = $user->createToken('UserApp')->plainTextToken;
                     return response()->json([
						 'status' => 200,
						 'message' => 'Registation successfully',
						 'data' =>$success,
						 //'api_response'=>$apiResponse
					 ], 200);
				}

				// Handle API errors
				return response()->json([
					'status' => 400,
					'message' => 'Failed to register.',
					//'api_response' => $response->body()
				], 400);
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return response()->json(['status' => 400, 'message' => 'Internal Server Error','error' => $e->getMessage()], 400);
			}
}


public function check_existsnumber(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'mobile' => 'required|string|size:10',
    ]);

    // If validation fails, return a 400 response with the validation error
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ]);
    }

    // Check if a user with the provided mobile number exists using Eloquent
    $user = User::where('mobile', $request->mobile)->first();

    // If user exists, return a 400 response with a message
    if ($user) {
        return response()->json([
            'status' => 400,
            'message' => "This mobile number is already registered. Please login ..!"
        ]);
    }

    // If no user exists, return a 200 response
    return response()->json([
        'status' => 200,
        'message' => "This mobile number is not registered. Please register ..!"
    ]);
}


   public function login(Request $request)
{
    // Validate the input
    $validator = Validator::make($request->all(), [
		'country_code' => 'required',
        'mobile' => 'required|string|size:10',
        'password' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ],200);
    }

    // Check if the mobile number exists
   $user = User::where('mobile', $request->mobile)
                ->where('country_code', $request->country_code)
                ->first();

    if (!$user) {
        // Specific error for country code
        if (!User::where('country_code', $request->country_code)->exists()) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid country code'
            ], 200);
        }

        // Specific error for mobile number
        if (!User::where('mobile', $request->mobile)
                 ->where('country_code', $request->country_code)
                 ->exists()) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid mobile number'
            ], 200);
        }
    }

    // If both mobile and password are correct
    // $response = [
    //     'status' => 200,
    //     'message' => 'Login successful',
    //     'data' => $user
    // ];
            $success['userId'] = $user->id;
	        $success['token'] = $user->createToken('UserApp')->plainTextToken;
		    return response()->json(['status' => 200,'message' => 'Login successfully','data' =>$success], 200);

    return response()->json($response, 200);
}

public function Profile($id)
{
    // Create an instance of the jilli class
    //$jilliInstance = new jilli();
    
    // Call the method on the instance
    //$wallet_update = $jilliInstance->update_user_wallet($id);

    $ldate = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    // echo $ldate->format('Y-m-d H:i:s');

    try {
        $user = User::find($id);

        if ($user) {
            return response()->json([
                'success' => 200,
                'message' => 'User found..!',
                'data' => $user,
                'aviator_link' => "https://aviatorudaan.com/",
                'aviator_event_name' => "jupiter",
                'apk_link' => env('APP_URL') . "jupiter.apk",
                'usdt_payin_amount' => 94,
                'usdt_payout_amount' => 92,
                'telegram' => "https://t.me/Help_jupiter",
                'referral_code_url' => env('APP_URL') . "registerwithref/" . $user->referral_code,
                'last_login_time' => $ldate->format('Y-m-d H:i:s'),
				 // Static India Pay Data
                'india_pay' => [
                    'min_amount' => 110,
                    'max_amount' => 50000
                ],

                // Static USDT Data
                'usdt' => [
                    'min_amount' => 10,
                    'max_amount' => 5000
                ],
				
				
            ]);
        }
        return response()->json(['success' => 400, 'message' => 'User not found..!'], 200);
    } catch (Exception $e) {
        return response()->json(['error' => 'API request failed: ' . $e->getMessage()], 500);
    }
}
	
	
	public function main_wallet_transfer(Request $request)
{
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
    $thirdpartywallet = $user->third_party_wallet;
    $add_main_wallet = $main_wallet + $thirdpartywallet;
    
    if ($status == 1) {
        $user->wallet = $add_main_wallet;
        $user->third_party_wallet = 0;
        $user->save();

        $response = [
            'status' => 200,
            'message' => "Wallet transfer Successfully ....!"
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
	
	 public function attendance_List(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $list = DB::select("SELECT COALESCE(COUNT(at_claim.`userid`),0) AS attendances_consecutively , COALESCE(SUM(attendances.attendance_bonus),0) AS accumulated FROM `at_claim` LEFT JOIN attendances ON at_claim.attendance_id =attendances.id WHERE at_claim.userid=$userid");

    $day = $list[0]->attendances_consecutively;
    $bonus_amt = $list[0]->accumulated;


        $attendanceList = DB::select("
   SELECT a.`id` AS `id`, a.`accumulated_amount` as accumulated_amount ,a.`attendance_bonus` as attendance_bonus, COALESCE(c.`status`, '1') AS `status`, COALESCE(a.`created_at`, 'Not Found') AS `created_at` FROM `attendances` a LEFT JOIN `at_claim` c ON a.`id` = c.`attendance_id` AND c.`userid` =$userid  ORDER BY a.`id` ASC LIMIT 7
");
  

        if (!empty($attendanceList)) {
            $response = [
                'message' => 'Attendance List',
                'status' => 200,
                'attendances_consecutively' => $day,
                'accumulated' =>$bonus_amt,
                'data' => $attendanceList,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function attendance_history(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $list1 = DB::select("SELECT at_claim.id AS id,attendances.attendance_bonus AS attendance_bonus,at_claim.created_at FROM attendances LEFT JOIN at_claim ON at_claim.attendance_id=attendances.id WHERE at_claim.userid=$userid");

    
  

        if (!empty($list1)) {
            $response = [
                'message' => 'Attendance History',
                'status' => 200,
                'data' => $list1,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    //// Attendance Claim ////
    	public function attendance_claim(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric'
        ]);
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
    
        $results = DB::select("SELECT a.`id` AS `id`, a.`accumulated_amount` AS accumulated_amount, a.`attendance_bonus` AS attendance_bonus, COALESCE(c.`status`, '1') AS `status`, COALESCE(a.`created_at`, 'Not Found') AS `created_at`, u.`wallet` FROM `attendances` a LEFT JOIN `at_claim` c ON a.`id` = c.`attendance_id` AND c.`userid` = $userid JOIN `users` u ON u.id = $userid WHERE COALESCE(c.`status`, '1') = '1' ORDER BY a.`id` ASC LIMIT 7");
    //dd($results);
        if (count($results) > 0) {
            $bonus = $results[0]->attendance_bonus;
            $id = $results[0]->id;
            $accumulated_amount =$results[0]->accumulated_amount;
            $wallet = $results[0]->wallet;
    if($wallet >= $accumulated_amount){
            $count = DB::select("SELECT COALESCE(COUNT(userid), 0) AS userid FROM `at_claim` WHERE userid = $userid AND DATE(created_at) = CURDATE()");
		//dd($count);
        
       // dd($count);
            $datetime = now();
            if ($count[0]->userid == 0) {
				//dd("hii");
                DB::table('at_claim')->insert([      
                    'userid' => $userid,
                    'attendance_id' => $id,   
                    'status' => '0',
                    'created_at' => $datetime,
                    'updated_at' => $datetime    
                ]);
    
                // Assuming you have `$datetime` defined somewhere
                DB::table('users')->where('id', $userid)->increment('wallet', $bonus);
                DB::table('wallet_histories')->insert([
                    'user_id' => $userid,
                    'amount' => $bonus,
                    'type_id' => 14,
                    'created_at' => $datetime,
                    'updated_at' => $datetime
                ]);
    
                $response = [
                    'message' => 'Today Claimed Successfully ...!',
                    'status' => 200,
                ];
                return response()->json($response, 200);
            } else {
                return response()->json(['message' => 'Today You Have Already Claimed', 'status' => 400], 400); 
            }
    }else{
      return response()->json(['message' => 'You can not claim due to insufficient Balance...!', 'status' => 400], 400);  
    }
            
        } else {
            return response()->json(['message' => 'User Not Found!', 'status' => 400], 400);
        }
    }
public function total_bet_details(Request $request)
{
    // Validate incoming data
    $validator = Validator::make($request->all(), [
        'userid' => 'required|exists:users,id',
        'type' => 'required|in:1,2,3,4'
		
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $userid = $request->userid;
    $type = $request->type;
	
	

    // Prepare the SQL query based on the 'type' parameter
    switch ($type) {
        case 1:
            // For today
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) = CURDATE()", [$userid]);
            break;

        case 2:
            // For yesterday
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) = CURDATE() - INTERVAL 1 DAY", [$userid]);
            break;

        case 3:
            // For the past week
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) >= CURDATE() - INTERVAL 1 WEEK", [$userid]);
            break;

        case 4:
            // For the past month
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) >= CURDATE() - INTERVAL 1 MONTH", [$userid]);
            break;

        default:
            return response()->json([
                'status' => 400,
                'message' => 'Invalid type provided'
            ], 200);
    }
	
	
	$grand_total=$betDetails[0]->total_bet_amount;

    // If no bets found, send response with 0 values
    if (empty($betDetails)) {
        return response()->json([
            'status' => 200,
            'message' => 'No bets found',
            'lottery_data' => [
                'total_bet_amount' => 0,
                'total_bet_count' => 0,
                'total_win_amount' => 0
            ]
        ], 200);
    }

    // Return the bet details
    return response()->json([
        'status' => 200,
        'message' => 'Bet details fetched successfully',
		'grand_total' => $grand_total,
        'lottery_data' => $betDetails[0] // Assuming only one record is returned
		
    ], 200);
}



public function slider_image_view()
{
    $slider = Slider::all();

    if ($slider->isNotEmpty()) {
        return response()->json(['success' => 200, 'message' => 'Sliders found..!', 'data' => $slider]);
    }

    return response()->json(['success' => 400, 'message' => 'Sliders not found..!'],200);
}

	
	
    
    //// Change Password Api ////
    public function changepassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required|exists:users,id',
        'password' => 'required|string|min:8',
        'newpassword' => 'required|string|min:8',
        'confirm_newpassword' => 'required|string|same:newpassword',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => "400",
            'message' => $validator->errors()->first()
        ], 200);
    }

    $user = User::find($request->userid);
    //dd($user);
    // Verify the current password
    if ($user->password !== $request->password) {
        return response()->json([
            'status' => "400",
            'message' => 'Current password is incorrect'
        ], 200);
    }

    // Update the password
    $user->password = $request->newpassword;
    $user->save();

    return response()->json([
        'status' => "200",
        'message' => 'Password Changed successfully'
    ], 200);
}


public function resetPassword(Request $request)
{
    // Validate the request inputs
    $validator = Validator::make($request->all(), [
        'mobile' => 'required|string|size:10',
        'password' => 'required|string|min:8',
        'confirm_password' => 'required|string|same:password',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'status' => "400",
            'message' => $validator->errors()->first()
        ], 200);
    }

    // Find the user by mobile number
    $user = User::where('mobile', $request->mobile)->first();

    // If user is not found
    if (!$user) {
        return response()->json([
            'status' => "400",
            'message' => 'Invalid mobile number'
        ], 200);
    }

    // Update the user's password (plain text, no hashing)
    $user->password = $request->password;
    $user->save();

    // Return success response
    return response()->json([
        'status' => "200",
        'message' => 'Password updated successfully'
    ], 200);
}
public function addAccount(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'name' => 'required',
        'account_number' => 'required',
        'bank_name' => 'required',
        'ifsc_code' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => "400",
            'message' => $validator->errors()->first(),
        ], 200);
    }

    $userid = $request->input('userid');
    $name = $request->input('name');
    $account_number = $request->input('account_number');
    $bank_name = $request->input('bank_name');
    $ifsc_code = $request->input('ifsc_code');

    $datetime = Carbon::now();

    // Check if the account with the same account number already exists
    $existingAccount = BankDetail::where('account_num', $account_number)->first();

    if ($existingAccount) {
        return response()->json([
            'status' => "400",
            'message' => 'This account number is already registered.',
        ], 200);
    }

    // Create a new account
    $account = BankDetail::create([
        'userid' => $userid,
        'name' => $name,
        'account_num' => $account_number,
        'bank_name' => $bank_name,
        'ifsc_code' => $ifsc_code,
        'status' => 1,
        'created_at' => $datetime,
        'updated_at' => $datetime,
    ]);

    if ($account) {
        return response()->json([
            'id' => $account->id,
            'status' => "200",
            'message' => 'Account Added Successfully.',
        ]);
    } else {
        return response()->json([
            'status' => "400",
            'message' => 'Account Not Added',
        ], 200);
    }
}


public function addAccount_old(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'name' => 'required',
        'account_number' => 'required',
        'bank_name' => 'required',
        'ifsc_code' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => "400",
            'message' => $validator->errors()->first(),
        ],200);
    }

    $userid = $request->input('userid');
    $name = $request->input('name');
    $account_number = $request->input('account_number');
    $bank_name = $request->input('bank_name');
    $ifsc_code = $request->input('ifsc_code');

    $datetime = Carbon::now();

    // Check if the account exists
    // $existingAccount = BankDetail::where('userid', $userid)->first();
    // if ($existingAccount) {
    //     $existingAccount->update([
    //         'name' => $name,
    //         'account_num' => $account_number,
    //         'bank_name' => $bank_name,
    //         'ifsc_code' => $ifsc_code,
    //     ]);

    //     return response()->json([
    //         'status' => "200",
    //         'message' => 'Account Updated Successfully.',
    //     ]);
    // }

    // Create a new account
    $account = BankDetail::create([
        'userid' => $userid,
        'name' => $name,
        'account_num' => $account_number,
        'bank_name' => $bank_name,
        'ifsc_code' => $ifsc_code,
        'status' => 1,
        'created_at' => $datetime,
        'updated_at' => $datetime,
    ]);

    if ($account) {
        return response()->json([
            'id' => $account->id,
            'status' => "200",
            'message' => 'Account Added Successfully.',
        ]);
    } else {
        return response()->json([
            'status' => "400",
            'message' => 'Account Not Added',
        ],200);
    }
}

public function accountView(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ],200);
    }
     $userid=$request->userid;
   
    $result = BankDetail::where('userid', $userid)->get();

    if ($result->isNotEmpty()) {  
        return response()->json([
            'status' => "200",
            'message' => 'Success',
            'data' => $result,
        ], 200);
    } else {
        return response()->json([
            'status' => "400",
            'message' => 'No data found.',
        ], 200);
    }
}

// public function accountDelete($id)
// {
//     try {
//         // Find the BankDetail entry by ID
//         $bankDetail = BankDetail::findOrFail($id);


//       if(!$id){
//         // Delete the entry
//         $bankDetail->delete();

//         // Return success response
//         return response()->json(['status' => "200", 'message' => 'Account deleted successfully'], 200);
//       }else{
//           return response()->json(['status' => "400", 'message' => 'Id no found'], 200);
//       }
//     } catch (ModelNotFoundException $e) {
//         // Return not found response
//         return response()->json(['status' => "400", 'message' => 'Account not found'], 200);
//     } catch (Exception $e) {
//         // Return error response
//         return response()->json(['error' => 'API request failed: ' . $e->getMessage()], 500);
//     }
// }
	public function wingo_rules(Request $request)
{
    $validator = Validator::make($request->all(), [
        'type' => 'required|numeric',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    $type = $request->type;

    $records = DB::select("SELECT name, list FROM rules WHERE type = ?", [$type]);

    if (!empty($records)) {
        // Clean up list formatting: add line breaks after periods
        $cleanedRecords = array_map(function ($record) {
            $record->list = preg_replace('/\.\s*/', ".\n", $record->list);
            return [
                'name' => $record->name,
                'list' => $record->list
            ];
        }, $records);

        return response()->json([
            'message' => 'rules list',
            'status' => 200,
            'data' => $cleanedRecords,
        ], 200);
    } else {
        return response()->json([
            'message' => 'Not found..!',
            'status' => 400,
            'data' => []
        ], 400);
    }
}

	public function wingo_rules_olddd(Request $request)
{
    $validator = Validator::make($request->all(), [
        'type' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return view('wingo_rules', ['error' => $validator->errors()->first()]);
    }

    $type = $request->type;
    $records = DB::select("SELECT name, list FROM `rules` WHERE `type` = ?", [$type]);

    if (!empty($records)) {
        return view('wingo_rules', ['records' => $records]);
    } else {
        return view('wingo_rules', ['error' => 'Not found!']);
    }
}


public function accountDelete($id)
{
    // Find the BankDetail entry by ID
    $bankDetail = BankDetail::find($id);

    // Check if the BankDetail entry was found
    if (!$bankDetail) {
        // Return not found response
        return response()->json(['status' => "404", 'message' => 'Account not found'], 200);
    }

    // Delete the entry
    $bankDetail->delete();

    // Return success response
    return response()->json(['status' => "200", 'message' => 'Account deleted successfully'], 200);
}
	
	public function accountUpdate(Request $request, $id)
{
    // Validate incoming request data (you can customize the validation rules)
    $validatedData = $request->validate([
        'account_num' => 'nullable|string|max:255',
        'name' => 'nullable|string|max:255',
        'bank_name' => 'nullable|string|max:255',
        'ifsc_code' => 'nullable|string|max:255',
    ]);

    // Find the BankDetail entry by ID
    $bankDetail = BankDetail::find($id);

    // Check if the BankDetail entry was found
    if (!$bankDetail) {
        // Return not found response
        return response()->json(['status' => "404", 'message' => 'Account not found'], 404);
    }

    // Update only the fields that are provided in the request
    if ($request->has('account_num')) {
        $bankDetail->account_num = $validatedData['account_num'];
    }
    if ($request->has('name')) {
        $bankDetail->name = $validatedData['name'];
    }
    if ($request->has('bank_name')) {
        $bankDetail->bank_name = $validatedData['bank_name'];
    }
    if ($request->has('ifsc_code')) {
        $bankDetail->ifsc_code = $validatedData['ifsc_code'];
    }

    // Save the updated BankDetail entry
    $bankDetail->save();

    // Return success response
    return response()->json(['status' => "200", 'message' => 'Account updated successfully'], 200);
}

public function kuber_payin(Request $request)
{
    // Validate request data
    $validator = Validator::make($request->all(), [
        'userid' => 'required|exists:users,id',
        'amount' => 'required|numeric',
        'type' => 'required|in:1' // Assuming 'type' can only be '0' or '1'
    ]);

    $validator->stopOnFirstFailure();

    // Handle validation failure
    if ($validator->fails()) {
        return response()->json([
            'status' => "400",
            'message' => $validator->errors()->first()
        ], 200);
    }

    // Assign request data to variables
    $cash = $request->amount;
    $userid = $request->userid;
    $type = $request->type;
    
    // Generate order id
    $dateTime = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));
	//dd($dateTime);
    $formattedDateTime = $dateTime->format('YmdHis');
    $rand = rand(11111, 99999);
    $orderid = $formattedDateTime . $rand;
    $datetime = now();

    // Check if the user exists
    $user = User::find($userid);

    if ($user) {
        if ($cash >= 100) {
        if ($type == '0') {
            $baseUrl = URL::to('/');
            $redirect_url = $baseUrl . "/api/checkPayment?order_id=$orderid";

            // Insert payin record using Eloquent
            $payin = new Payin();
            $payin->user_id = $user->id;
            $payin->cash = $cash;
            $payin->type = $type;
            $payin->order_id = $orderid;
            $payin->redirect_url = $redirect_url;
            $payin->status = 1;
            $payin->created_at = $dateTime;
            $payin->updated_at = $dateTime;
            
            if (!$payin->save()) {
                return response()->json(['status' => "400", 'message' => 'Failed to store record in payin history!'], 200);
            }

            // Prepare data for the external API call
            $postParameter = [
                "merchantid" => "PAYIN1001",
                "orderid" => $orderid,
                "amount" => $cash,
                "name" => $user->name,
                "email" => "test@gmail.com", // Update with the real email if needed
                "mobile" => $user->mobile,
                "remark" => "Kuber payIn",
                "type" => "2",
                "redirect_url" => $redirect_url
            ];

            // External API call using cURL
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://indianpay.co.in/admin/paynow',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postParameter),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Cookie: ci_session=oo35jvjuvh3ukuk9t7biecukphiiu8vl'
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);
            echo $response;
        } else {
            // Handle case where type is not '0'
            return response()->json([
                'status' => "400",
                'message' => 'Invalid type value!'
            ], 200);
        }
        } else {
            // Handle case where type is not '0'
            return response()->json([
                'status' => "400",
                'message' => 'Minimum Deposit amount is 100 rs!'
            ], 200);
        }
    } else {
        return response()->json([
            'status' => "400",
            'message' => 'Internal error! User not found.'
        ], 400);
    }
}
	
	public function kuber_checkPayment(Request $request)
{
    // Validate request data
    $validator = Validator::make($request->all(), [
        'order_id' => 'required|exists:payins,order_id', // Ensure order_id exists in Payin table
    ]);

    $validator->stopOnFirstFailure();

    // Handle validation failure
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first(),
        ], 200);
    }

    $orderid = $request->order_id;
    $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');

    // Find the payment with order_id and status 1
    $payment = Payin::where('order_id', $orderid)
                    ->where('status', 1)
                    ->first();

    if (!$payment) {
        return response()->json([
            'status' => 404,
            'message' => 'Payment not found or already processed.',
        ], 200);
    }

    // Get the user ID and payment amount from the payment record
    $userid = $payment->user_id; 
    $amount = $payment->cash;

    // Update payment status to 2 (processed)
    $payment->status = 2;
    $payment->save();

    // Fetch the user data
    $user = User::where('id', $userid)
                ->where('status', 1) // Ensure user is active
                ->first();

    if (!$user) {
        return response()->json([
            'status' => 404,
            'message' => 'User not found or inactive.',
        ], 200);
    }

    // Fetch referral user ID (if any)
    $referral_user_id = $user->referrer_id;
	//dd($referral_user_id);

    // First recharge percentage (10% for the user)
    $userPercentage = ($amount * 10) / 100;

    // Second recharge percentage (5% for the referral user)
    $secondPercentage = ($amount * 5) / 100;
	$final_amt=$amount + $userPercentage;
	//dd($final_amt);

    // Check if it's the user's first recharge
    if ($user->first_recharge == '1') {
        // Update user wallet and recharge amounts
        DB::table('users')
            ->where('id', $userid)
            ->update([
                'wallet' => DB::raw("wallet + $amount + $userPercentage"),
                'recharge' => DB::raw("recharge + $amount + $userPercentage"),
                'first_recharge' => 0, // Mark first recharge as done
            ]);
		
		

        // If referral user exists, update their wallet and recharge
        if ($referral_user_id) {
            DB::table('users')
                ->where('id', $referral_user_id)
                ->update([
                    'wallet' => DB::raw("wallet + $userPercentage"),
                    'recharge' => DB::raw("recharge + $userPercentage"),
                ]);
            
            // Add wallet history for the referral user
            DB::table('wallet_histories')->insert([
                'user_id' => $referral_user_id,
                'amount' => $userPercentage,
                'type_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add wallet history for the user
        DB::table('wallet_histories')->insert([
            'user_id' => $userid,
            'amount' => $userPercentage,
            'type_id' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    } else {
        // Update user wallet and recharge amounts for non-first recharge
        DB::table('users')
            ->where('id', $userid)
            ->update([
                'wallet' => DB::raw("wallet + $amount + $userPercentage"),
                'recharge' => DB::raw("recharge + $amount + $userPercentage"),
                'first_recharge' => 0, // Mark first recharge as done
            ]);

        // If referral user exists, update their wallet and recharge
        if ($referral_user_id) {
            DB::table('users')
                ->where('id', $referral_user_id)
                ->update([
                    'wallet' => DB::raw("wallet + $secondPercentage"),
                    'recharge' => DB::raw("recharge + $secondPercentage"),
                ]);

            // Add wallet history for the referral user
            DB::table('wallet_histories')->insert([
                'user_id' => $referral_user_id,
                'amount' => $secondPercentage,
                'type_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add wallet history for the user
        DB::table('wallet_histories')->insert([
            'user_id' => $userid,
            'amount' => $userPercentage,
            'type_id' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

		
	// akash ///
	  $account_token = $user->account_token;
	  $zili_wallet_resp = $this->add_to_ziliwallet($userid,$final_amt,$account_token);
	  if($zili_wallet_resp['status']){
       //  return redirect()->view('success')->with('error',$zili_wallet_resp['msg']);
		  return redirect()->route('payin.successfully');
	  }
	  $zili_utr_number = $zili_wallet_resp['utr_no'];
	  DB::table('payins')->where('order_id',$request->order_id)->update(['zili_utr_num'=>$zili_utr_number]);
	
	//akash end///
	
    // Redirect to success route
    return redirect()->route('payin.successfully');
}
	



public function payin(Request $request)
{
    // Validate request data
    $validator = Validator::make($request->all(), [
        'userid' => 'required|exists:users,id',
        'amount' => 'required|numeric',
        'type' => 'required|in:0' // Assuming 'type' can only be '0' or '1'
    ]);

    $validator->stopOnFirstFailure();

    // Handle validation failure
    if ($validator->fails()) {
        return response()->json([
            'status' => "400",
            'message' => $validator->errors()->first()
        ], 200);
    }

    // Assign request data to variables
    $cash = $request->amount;
    $userid = $request->userid;
    $type = $request->type;
    
    // Generate order id
    $dateTime = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));
	//dd($dateTime);
    $formattedDateTime = $dateTime->format('YmdHis');
    $rand = rand(11111, 99999);
    $orderid = $formattedDateTime . $rand;
    $datetime = now();

    // Check if the user exists
    $user = User::find($userid);
	
	if ($user) {
        if ($cash > 10000) {
            return response()->json([
                'status' => "400",
                'message' => 'Please use KuberPay for amounts greater than 10,000.'
            ], 200);
        }

    if ($user) {
        if ($cash >= 100) {
        if ($type == '0') {
            $baseUrl = URL::to('/');
            $redirect_url = $baseUrl . "/api/checkPayment?order_id=$orderid";

            // Insert payin record using Eloquent
            $payin = new Payin();
            $payin->user_id = $user->id;
            $payin->cash = $cash;
            $payin->type = $type;
            $payin->order_id = $orderid;
            $payin->redirect_url = $redirect_url;
            $payin->status = 1;
            $payin->created_at = $dateTime;
            $payin->updated_at = $dateTime;
            
            if (!$payin->save()) {
                return response()->json(['status' => "400", 'message' => 'Failed to store record in payin history!'], 200);
            }

            // Prepare data for the external API call
            $postParameter = [
                "merchantid" => "INDIANPAY00INDIANPAY0096",
                "orderid" => $orderid,
                "amount" => $cash,
                "name" => $user->name,
                "email" => "test@gmail.com", // Update with the real email if needed
                "mobile" => $user->mobile,
                "remark" => "payIn",
                "type" => "2",
                "redirect_url" => $redirect_url
            ];

            // External API call using cURL
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://indianpay.co.in/admin/paynow',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postParameter),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Cookie: ci_session=oo35jvjuvh3ukuk9t7biecukphiiu8vl'
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);
            echo $response;
        } else {
            // Handle case where type is not '0'
            return response()->json([
                'status' => "400",
                'message' => 'Invalid type value!'
            ], 200);
        }
        } else {
            // Handle case where type is not '0'
            return response()->json([
                'status' => "400",
                'message' => 'Minimum Deposit amount is 100 rs!'
            ], 200);
        }
    } else {
        return response()->json([
            'status' => "400",
            'message' => 'Internal error! User not found.'
        ], 400);
    }
}
}


public function checkPayment(Request $request)
{
    // Validate request data
    $validator = Validator::make($request->all(), [
        'order_id' => 'required|exists:payins,order_id', // Ensure order_id exists in Payin table
    ]);

    $validator->stopOnFirstFailure();

    // Handle validation failure
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first(),
        ], 200);
    }

    $orderid = $request->order_id;
    $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');

    // Find the payment with order_id and status 1
    $payment = Payin::where('order_id', $orderid)
                    ->where('status', 1)
                    ->first();

    if (!$payment) {
        return response()->json([
            'status' => 404,
            'message' => 'Payment not found or already processed.',
        ], 200);
    }

    // Get the user ID and payment amount from the payment record
    $userid = $payment->user_id; 
    $amount = $payment->cash;

    // Update payment status to 2 (processed)
    $payment->status = 2;
    $payment->save();

    // Fetch the user data
    $user = User::where('id', $userid)
                ->where('status', 1) // Ensure user is active
                ->first();

    if (!$user) {
        return response()->json([
            'status' => 404,
            'message' => 'User not found or inactive.',
        ], 200);
    }

    // Fetch referral user ID (if any)
    $referral_user_id = $user->referrer_id;
	//dd($referral_user_id);

    // First recharge percentage (10% for the user)
    $userPercentage = ($amount * 10) / 100;

    // Second recharge percentage (5% for the referral user)
    $secondPercentage = ($amount * 5) / 100;
	$final_amt=$amount + $userPercentage;
	//dd($final_amt);

    // Check if it's the user's first recharge
    if ($user->first_recharge == '1') {
        // Update user wallet and recharge amounts
        DB::table('users')
            ->where('id', $userid)
            ->update([
                'wallet' => DB::raw("wallet + $amount + $userPercentage"),
                'recharge' => DB::raw("recharge + $amount + $userPercentage"),
                'first_recharge' => 0, // Mark first recharge as done
            ]);
		
		

        // If referral user exists, update their wallet and recharge
        if ($referral_user_id) {
            DB::table('users')
                ->where('id', $referral_user_id)
                ->update([
                    'wallet' => DB::raw("wallet + $userPercentage"),
                    'recharge' => DB::raw("recharge + $userPercentage"),
                ]);
            
            // Add wallet history for the referral user
            DB::table('wallet_histories')->insert([
                'user_id' => $referral_user_id,
                'amount' => $userPercentage,
                'type_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add wallet history for the user
        DB::table('wallet_histories')->insert([
            'user_id' => $userid,
            'amount' => $userPercentage,
            'type_id' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    } else {
        // Update user wallet and recharge amounts for non-first recharge
        DB::table('users')
            ->where('id', $userid)
            ->update([
                'wallet' => DB::raw("wallet + $amount + $userPercentage"),
                'recharge' => DB::raw("recharge + $amount + $userPercentage"),
                'first_recharge' => 0, // Mark first recharge as done
            ]);

        // If referral user exists, update their wallet and recharge
        if ($referral_user_id) {
            DB::table('users')
                ->where('id', $referral_user_id)
                ->update([
                    'wallet' => DB::raw("wallet + $secondPercentage"),
                    'recharge' => DB::raw("recharge + $secondPercentage"),
                ]);

            // Add wallet history for the referral user
            DB::table('wallet_histories')->insert([
                'user_id' => $referral_user_id,
                'amount' => $secondPercentage,
                'type_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add wallet history for the user
        DB::table('wallet_histories')->insert([
            'user_id' => $userid,
            'amount' => $userPercentage,
            'type_id' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

		
	// akash ///
	  $account_token = $user->account_token;
	  $zili_wallet_resp = $this->add_to_ziliwallet($userid,$final_amt,$account_token);
	  if($zili_wallet_resp['status']){
       //  return redirect()->view('success')->with('error',$zili_wallet_resp['msg']);
		  return redirect()->route('payin.successfully');
	  }
	  $zili_utr_number = $zili_wallet_resp['utr_no'];
	  DB::table('payins')->where('order_id',$request->order_id)->update(['zili_utr_num'=>$zili_utr_number]);
	
	//akash end///
	
    // Redirect to success route
    return redirect()->route('payin.successfully');
}
	
	public function redirect_success(){
		 return view ('success');	
	 }
	    
	protected function add_to_ziliwallet($userid,$amount,$account_token){
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
				// Check if API call was successful
				if ($response->successful() && isset($apiResponse->error) && $apiResponse->error == false) {
					$utr_no = $apiResponse->utr_no;
					  return ['status'=>false,'utr_no'=>$utr_no,'msg'=>null];
				}

				// Handle API errors
				return ['status'=>true,'utr_no'=>null,'msg'=>$apiResponse->msg];
			} catch (\Exception $e) {
				// Log exception
				Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
				// Return server error response
				return ['status'=>true,'utr_no'=>null,'msg'=>$e->getMessage()];
			}
		
	}
	
	

public function wallet_transfer(Request $request)
{
	 $validator = Validator::make($request->all(), [
        'id' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => "400",
            'message' => $validator->errors()->first(),
        ],200);
    }
   $id=$request->id;
    // Find the user by ID
    $user = User::findOrFail($id);
    
    // Check if user status is active (status == 1)
    if ($user->status != 1) {
        return response()->json([
            'status' => 401,
            'message' => "User blocked by admin."
        ], 401);
    }

    // Calculate the new wallet balance by adding third-party wallet to the main wallet
    $add_main_wallet = $user->wallet + $user->third_party_wallet;

   
        // Update the main wallet and reset the third-party wallet
        $user->update([
            'wallet' => $add_main_wallet,
            'third_party_wallet' => 0,
        ]);
        
        // Return success response
        return response()->json([
            'status' => 200,
            'message' => "Wallet transfer successfully completed!"
        ], 200);
   
}

	
 public function withdraw_old(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'account_id' => 'required',
        'type' => 'required',
        'amount' => 'required|numeric',
    ]);

    $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                      'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }

    $userid = $request->input('user_id');
    $accountid = $request->input('account_id');
    $amount = $request->input('amount');
    $type = $request->input('type');
    
     $date = date('YmdHis');
        $rand = rand(11111, 99999);
        $orderid = $date . $rand;
    if ($amount >= 200 && $amount<=25000) {
      //($amount >= 550) 
		dd("hii");
        $wallet=DB::select("SELECT ⁠ wallet ⁠,⁠ first_recharge ⁠,⁠ winning_wallet ⁠ FROM ⁠ users ⁠ WHERE id=$userid");
      $user_wallet=$wallet[0]->wallet;
      $first_recharge=$wallet[0]->first_recharge;
      if($user_recharge == 0){
          if($first_recharge == 1){
        if($user_wallet >= $amount){
      $data= DB::table('withdraws')->insert([
    'user_id' => $userid,
    'amount' => $amount,
    'account_id' => $accountid,
    'type' => $type,
    'order_id' => $orderid,
    'status' => 1,
	'typeimage'=>"https://root.globalbet24.live/uploads/fastpay_image.png",
    'created_at' => now(),
    'updated_at' => now(),
]);
      DB::select("UPDATE ⁠ users ⁠ SET ⁠ wallet ⁠=⁠ wallet ⁠-$amount WHERE id=$userid;");
 if ($data) {
             $response = [
        'status' =>200,
        'message' => 'Withdraw Request Successfully ..!',
    ];

    return response()->json($response,200);

        } else {
             $response = [
        'status' =>400,
        'message' => 'Internal error..!',
    ];

    return response()->json($response,400);
            
        }
        }else{
      $response = [
        'status' =>400,
        'message' => 'insufficient Balance..!',
    ];

    return response()->json($response,400);
 }  
          }else{
      $response = [
        'status' =>400,
        'message' => 'first rechage is mandatory..!',
    ];

    return response()->json($response,400);
 }     
      }else {
         $response = [
        'status' =>400,
        'message' => 'need to bet amount 0 to be able to Withdraw',
    ];

    return response()->json($response,400);   
      }
        
    }else{
        $response['message'] = "minimum Withdraw 200 And Maximum Withdraw 25000";
            $response['status'] = "400";
            return response()->json($response,200); 
	}    
}
	
	public function withdraw(Request $request)
{
    $now = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');

    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'account_id' => 'required',
        'type' => 'required',
        'amount' => 'required|numeric',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ]; 

        return response()->json($response, 200);
    }

    $userid = $request->input('user_id');
    $accountid = $request->input('account_id');
    $amount = $request->input('amount');
    $type = $request->input('type');

    // Define the minimum and maximum amounts based on the type
    if ($type == 0) {
    $minAmount = 110;
    $maxAmount = 10000;

    // If amount exceeds 10000 for type = 0, show message to use type = 1
    if ($amount > $maxAmount) {
        $response = [
            'status' => 400,
            'message' => 'For withdrawal amounts greater than 10000, please use type = 1 (Kuberpay).'
        ];
        return response()->json($response, 200);
    }
} elseif ($type == 1) {
        $minAmount = 10000;
        $maxAmount = 100000;
    } elseif ($type == 2) {
        $minAmount = 10;
        $maxAmount = 5000;
    } else {
        // If type is invalid, return an error
        $response = [
            'status' => 400,
            'message' => 'Invalid withdrawal type!'
        ];
        return response()->json($response, 200);
    }

    // Check if the amount is within the valid range based on the type
    if ($amount < $minAmount || $amount > $maxAmount) {
        $response = [
            'status' => 400,
            'message' => "The minimum withdraw for this type is $minAmount and maximum withdraw is $maxAmount."
        ];
        return response()->json($response, 200);
    }

    // Check if there's a pending withdrawal
    $lastWithdrawal = DB::table('withdraw_histories')
        ->where('user_id', $userid)
        ->orderBy('created_at', 'desc')
        ->first();

    if ($lastWithdrawal && $lastWithdrawal->status == 1) { // Assuming 1 is for pending
        return response()->json([
            'status' => 400,
            'message' => 'You cannot withdraw again until your previous request is approved or rejected.'
        ], 400);
    }

    // Limit to three withdrawals per day
    $withdrawCount = DB::table('withdraw_histories')
        ->where('user_id', $userid)
        ->whereDate('created_at', now())
        ->where('status', 2) // Assuming 2 is for successful withdrawal
        ->count();

    if ($withdrawCount >= 3) {
        $response = [
            'status' => 400,
            'message' => 'You can only withdraw 3 times in a day.'
        ];
        return response()->json($response, 400);
    }

    $date = date('YmdHis');
    $rand = rand(11111, 99999);
    $orderid = $date . $rand;

    // Proceed with the logic if the amount is within the correct range
    if ($amount >= $minAmount && $amount <= $maxAmount) {
        // Here you can insert your logic to check the user's balance, first_recharge, etc.
        $wallet = DB::select("SELECT wallet, first_recharge FROM users WHERE id=$userid");
        $user_wallet = $wallet[0]->wallet;
        $first_recharge = $wallet[0]->first_recharge;

        if ($type == 2) {
            $usdtAmount = $amount * 93;
        } else {
            $usdtAmount = $amount;
        }

        if ($user_wallet >= $amount) {
            // Check if the user has done the first recharge
            if ($first_recharge == 1) {
                // Proceed with withdrawal
                $data = DB::table('withdraws')->insert([
                    'user_id' => $userid,
                    'amount' => $usdtAmount,
                    'actual_amount' => $amount,
                    'account_id' => $accountid,
                    'type' => $type,
                    'order_id' => $orderid,
                    'status' => 1,
                    'typeimage' => "https://root.globalbet24.live/uploads/fastpay_image.png",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Update user's wallet balance
                DB::select("UPDATE users SET wallet = wallet - $amount WHERE id = $userid");

                $deduct_jili = jilli::deduct_from_wallet($userid, $amount);

                if ($data) {
                    $response = [
                        'status' => 200,
                        'message' => 'Withdraw request successful!'
                    ];
                    return response()->json($response, 200);
                } else {
                    $response = [
                        'status' => 400,
                        'message' => 'Internal error!'
                    ];
                    return response()->json($response, 400);
                }
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'First recharge is mandatory!'
                ];
                return response()->json($response, 400);
            }
        } else {
            $response = [
                'status' => 400,
                'message' => 'Insufficient balance!'
            ];
            return response()->json($response, 200);
        }
    } else {
        $response = [
            'status' => 400,
            'message' => 'Invalid amount! Please check the minimum and maximum withdrawal limits.'
        ];
        return response()->json($response, 200);
    }
}

	
    public function withdrawHistory(Request $request)
{
    // Validation rules
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer',
        'type' => 'nullable',
        'status' => 'sometimes|string',
        'created_at' => 'sometimes|date',
    ]);

    // Check for validation failures
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first(),
        ], 200);
    }
		$type=$request->type;
			
     if ($type == null) {
		 //dd("hello");
        // Fetching request parameters
    $query = Withdraw::query();

    // Adding conditions based on provided parameters
    $query->where('user_id', $request->user_id);

    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    if ($request->has('created_at')) {
        $query->whereDate('created_at', $request->created_at);
    }

    // Fetching the results
    $withdrawHistories = $query->orderBy('id', 'desc')->get();

		 
	 }
    // Fetching request parameters
    $query = Withdraw::query();

    // Adding conditions based on provided parameters
    $query->where('user_id', $request->user_id);
    
    if ($request->has('type')) {
        // Ensure type is an integer
        $query->where('type', $type);
    }

    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    if ($request->has('created_at')) {
        $query->whereDate('created_at', $request->created_at);
    }

    // Fetching the results
    $withdrawHistories = $query->orderBy('id', 'desc')->get();

    // Returning the response
    if ($withdrawHistories->isNotEmpty()) {
        return response()->json([
            'message' => 'Successfully retrieved',
            'status' => 200,
            'data' => $withdrawHistories,
        ], 200);
    } else {
        return response()->json([
            'message' => 'No record found',
            'status' => 400,
            'data' => [],
        ], 200);
    }
}

public function deposit_history(Request $request)
{
    // Validation rules
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer', 
        'type' => 'nullable',    
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // Fetch request parameters
    $user_id = $request->user_id;
    $type = $request->type;

    // Check if 'type' is null
    if ($type == null) {
       //dd($type);
    // Query using Eloquent
    $payinQuery = Payin::query();

    // Apply 'user_id' filter
    $payinQuery->where('user_id', $user_id);

    // Fetch the results, ordering by id descending
    $payin = $payinQuery->orderBy('id', 'desc')->get(['cash','usdt_amount', 'type', 'status', 'order_id', 'created_at']);

    }
	//dd($type);
    // Query using Eloquent
    $payinQuery = Payin::query();

    // Apply 'user_id' filter
    $payinQuery->where('user_id', $user_id);

    // Apply 'type' filter
    if (isset($type)) {
        $payinQuery->where('type', $type);  // Make sure 'type' is passed correctly as an integer
    }

    // Fetch the results, ordering by id descending
    $payin = $payinQuery->orderBy('id', 'desc')->get(['cash','usdt_amount', 'type', 'status', 'order_id', 'created_at']);

    // Return the response
    if ($payin->isNotEmpty()) {
        return response()->json([
            'message' => 'Successfully retrieved',
            'status' => 200,
            'data' => $payin
        ], 200);
    } else {
        return response()->json([
            'message' => 'No record found',
            'status' => 200,
            'data' => []
        ], 200);
    }
}

//// Gift Cart Apply /////

public function giftCartApply(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'code' => 'required',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first(),
        ], 400);
    }

    $userid = $request->input('userid');
    $code = $request->input('code');

    // Find the gift card with the provided code and active status
    $giftCart = GiftCard::where('code', $code)->where('status', 1)->first();

    if ($giftCart) {
        if ($giftCart->availed_num < $giftCart->number_people) {
            // Check if the user has already claimed this gift card
            $claimUser = GiftClaim::where('gift_code', $code)->where('userid', $userid)->first();

            if (!$claimUser) {
                date_default_timezone_set('Asia/Kolkata');
                $datetime = now();  // Using Laravel's now() helper for current timestamp

                $giftCartAmount = $giftCart->amount;

                if (!empty($giftCartAmount)) {
                    // Insert into gift_claim table
                    GiftClaim::create([
                        'userid' => $userid,
                        'gift_code' => $code,
                        'amount' => $giftCartAmount,
                    ]);

                    // Update user's wallet, bonus, and recharge amounts
                    User::where('id', $userid)->increment('third_party_wallet', $giftCartAmount);
                    User::where('id', $userid)->increment('bonus', $giftCartAmount);
                    User::where('id', $userid)->increment('recharge', $giftCartAmount);

                    // Update availed_num in gift_cart table
                    $giftCart->increment('availed_num');

                    // Insert into wallet_history table
                    WalletHistory::create([
                        'user_id' => $userid,
                        'amount' => $giftCartAmount,
                        'type_id' => 5,
                        'created_at' => $datetime,
                        'updated_at' => $datetime,
                    ]);

                    return response()->json([
                        'status' => 200,
                        'message' => "Added $giftCartAmount Rs. Successfully",
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 400,
                        'message' => "No record found",
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => "You have already availed this offer!",
                ]);
            }
        } else {
            return response()->json([
                'status' => 400,
                'message' => "No longer available for this offer.",
            ]);
        }
    } else {
        return response()->json([
            'status' => 400,
            'message' => "Invalid Gift Code!",
        ] );
    }
}

//// Gift Cart Apply /////


/// GiftClaim /////
public function claim_list(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
    ]);

    $validator->stopOnFirstFailure();

    // Handle validation failure
    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];

        return response()->json($response, 400);
    }

    // Get the validated user ID
    $userid = $request->userid;

    // Fetch the account details using Eloquent Model
    $accountDetails = GiftClaim::where('userid', $userid)
                                ->orderBy('id', 'DESC')
                                ->get();
//dd($accountDetails);
    // Check if account details were found
    if ($accountDetails->isNotEmpty()) {
        $response = [
            'message' => 'Successfully',
            'status' => 200,
            'data' => $accountDetails
        ];

        return response()->json($response, 200);
    } else {
        return response()->json([
            'message' => 'No record found',
            'status' => 200,
            'data' => []
        ], 200);
    }
}
//// GiftClaim ///

public function customer_service()
{
    // Using the CustomerService model to fetch the data
    $customerService = CustomerService::where('status', 1)
        ->select('name', 'Image', 'link')
        ->get();

    if ($customerService->isNotEmpty()) {
        $response = [
            'message' => 'Successfully',
            'status' => 200,
            'data' => $customerService
        ];
        
        return response()->json($response);
    } else {
        return response()->json([
            'message' => 'No record found',
            'status' => 400,
            'data' => []
        ], 400);
    }
}
	
	public function versionApkLink(Request $request)
{
    // Retrieve the version data using raw query
    $data = DB::select("SELECT * FROM `versions` WHERE `id`=1");

    if (count($data) > 0) {
        // Accessing the first row
        $row = $data[0];

        $response = [
			'data'=>$row,
            'msg' => 'Success',
            'status' => 200
            
        ];
        return response()->json($response, 200);
    } else {
        // If no data is found, return a 400 response
        return response()->json([
            'msg' => 'No record found',
            'status' => 400
        ], 400);
    }
}
	
	public function salary_list(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
		'salary_type'=> 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ],200);
    }
     $userid=$request->userid;
		$salary_type=$request->salary_type;
		
   
   $salary = DB::table('salary')
            ->where('user_id', $userid)
            ->where('salary_type', $salary_type)
            ->get();


    if ($salary->isNotEmpty()) {  
        return response()->json([
            'status' => "200",
            'message' => 'Success',
            'data' => $salary,
        ], 200);
    } else {
        return response()->json([
            'status' => "400",
            'message' => 'No data found.',
        ], 200);
    }
}
	
	
	public function betting_rebate(){
    
    $currentDate = date('Y-m-d');
		 
		 $a = DB::select("SELECT sum(amount) as betamount, userid FROM bets WHERE created_at like '$currentDate %' AND status= '2' GROUP BY userid;");

	   //dd($a);
		//$a = DB::select("SELECT `today_turnover` FROM `users` WHERE `id`=$userid ");
		
		foreach($a as $item){
		
		   $betamount = $item->betamount;
		   $userid = $item->userid;
			
			DB::select("UPDATE users SET wallet = wallet + $betamount * 0.01 WHERE id = $userid");
		$rebate_rate=0.01;
		  $insert= DB::table('wallet_histories')->insert([
        'user_id' => $userid,
        'amount' => $betamount*$rebate_rate,
        'description'=>$betamount,
        'description_2'=>$rebate_rate,
        'type_id' => 7,
		'created_at'=> now(),
        'updated_at' => now()
		
        ]);
		
	   }
		
	}		
	
	public function betting_rebate_history(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric',
        'type_id' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->userid;
    $subtypeid = $request->type_id;
    
    $value=DB::select("SELECT 
    COALESCE(SUM(amount), 0) as total_rebet,
    COALESCE(SUM(description), 0) as total_amount,
    COALESCE(SUM(CASE WHEN DATE(CURDATE()) = CURDATE() THEN amount ELSE 0 END), 0) as today_rebet 
FROM 
    wallet_histories 
WHERE 
    user_id = $userid && type_id =$subtypeid");
    
    $records=DB::select("SELECT 
    `amount` as rebate_amount,description_2 as rebate_rate,created_at as datetime,
    COALESCE((SELECT SUM(description) FROM wallet_histories WHERE `user_id` = $userid AND type_id = $subtypeid), 0) as betting_rebate 
FROM 
    `wallet_histories` 
WHERE 
    `user_id` = $userid AND type_id = $subtypeid;");


       
 
        if (!empty($records)) {
            $response = [
                'message' => 'Betting Rebet List',
                'status' => 200,
                'data1' =>$records,
                'data' =>$value,
            ];
            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
 

    }	
	
	
	  public function invitation_bonus_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
       $total_refer = DB::table('users')->where('referrer_id', $userid)->count();
      
    // Fetch all users referred by the user with the given $userid
    $refer_users = DB::table('users')->where('referrer_id', $userid)->get();
    
    $count_users = 0; // Initialize the count of users whose total deposit > 500
    
    // Loop through each referred user to calculate the total deposit sum
    foreach ($refer_users as $refer_user) {
        $user_id = $refer_user->id;
        
        // Calculate the total deposit amount for this user from the 'payins' table
        $deposit_count = DB::select("SELECT SUM(`cash`) as total_amount FROM `payins` WHERE `user_id` = :user_id", ['user_id' => $user_id]);
        
        // Get the total deposit amount for the user (or 0 if null or no rows)
        $total_deposit = $deposit_count[0]->total_amount ?? 0;
    
        // Only count users whose total deposit is greater than 500
        if ($total_deposit >= 500) {
            $count_users++; // Increment the count of users whose total deposit > 500
        }
    }
    
    
    $invite_bonus = DB::select("
        SELECT 
            a.id AS bonus_id,
            a.amount,
            a.claim_amount,
            a.no_of_user,
            CASE 
                WHEN c.userid = ? AND c.invite_id = a.id AND a.no_of_user <= ? THEN 0
                WHEN a.no_of_user <= ? THEN 2 
                ELSE COALESCE(c.status, '1') 
            END AS status,
            COALESCE(a.created_at, 'Not Found') AS created_at
        FROM 
            invite_bonus a
        LEFT JOIN 
            invite_bonus_claim c 
        ON 
            a.id = c.invite_id 
        AND 
            c.userid = ?
        ORDER BY 
            a.id ASC
    ", [$userid, $count_users, $count_users, $userid]);
    
    
    
        if (!empty($invite_bonus)) {
            $response = [
                'message' => 'invitation_bonus_list',
                'status' => 200,
                'data' => collect($invite_bonus)->map(function ($bonus) use ($total_refer, $count_users) {
                    return [
                        'bonus_id' => $bonus->bonus_id,
                        'amount' => $bonus->amount,
                        'claim_amount' => $bonus->claim_amount,
                        'no_of_user' => $bonus->no_of_user,
                        'status' => $bonus->status,
                        'created_at' => $bonus->created_at,
                        'no_of_invitees' => $total_refer,
                        'refer_invitees' => $count_users
                    ];
                })
            ];
            return response()->json($response);
        } else {
            return response()->json([
                'message' => 'Not found..!',
                'status' => 400,
                'data' => []
            ], 400);
        }
    }
    
    ///// Invitation_reward_rule ////
    
     public function Invitation_reward_rule(Request $request)
    {
          

       $rule=DB::select("SELECT * FROM `invite_bonus`");
       
  

        if (!empty($rule)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $rule,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
	
    //// Invitation record ////
    
    public function Invitation_records(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->userid;
 

       $records=DB::select("SELECT `name`,`u_id`,`first_recharge_amount`,`created_at` FROM `users` WHERE `referrer_id`=$userid");
       
  

        if (!empty($records)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $records,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
	
		public function invitation_bonus_claim(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric',
            'amount' => 'required',
            'invite_id'=>'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
        $amount = $request->amount;
        $invite_id=$request->invite_id;
        $bonusClaim = DB::table('invite_bonus_claim')
                    ->where('userid', $userid)
                    ->where('invite_id', $invite_id)
                    ->get();
                    // dd($bonusClaim);
                    
    if($bonusClaim->isEmpty()){
    $user = DB::table('users')->where('id', $userid)->first();
    if (!empty($user)) {
       $usser= DB::table('users')->where('id', $userid)->update([
            'wallet' => $user->wallet + $amount, // Add amount to wallet
        ]);
    }else{
     return response()->json([
    				'message' => 'user not found ..!',
    				'status' => 400,
                    ], 400);
     }
     if (!empty($usser)) {
        // Insert into wallet_histories
        $bonuss=DB::table('wallet_histories')->insert([
            'user_id'     => $userid,
            'amount'      => $amount,
            'description' => 'Invitation Bonus',
            'type_id'     => 8, // Define type_id as 1 for bonus claim
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        
         $bonuss=DB::table('invite_bonus_claim')->insert([
            'userid'     => $userid,
            'invite_id' => $invite_id,
            'status' => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
     }else{
     
     }
         if (!empty($bonuss)) {
                $response = [
                    'message' => 'invitation bonus claimed successfully!',
                    'status' => 200,
                ];
                return response()->json($response,200);
            } else {
                return response()->json([
    				'message' => 'Bonus not claimed ..!',
    				'status' => 400,
                    ], 400);
            }
            
           } else{
             return response()->json([
    				'message' => 'Already claimed ..!',
    				'status' => 400,
                    ], 400);  
           }
    	}
	
	 public function transaction_history_list()
      {
      $subtype=DB::select("SELECT `id`,`name` FROM `types` WHERE status=1");

        if ($subtype) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $subtype
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
    
////// Result Api ////
public function transaction_history(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }
    
    $userid = $request->userid;
    $subtype = $request->type_id;
     //$offset = $request->offset ?? 0;
    $from_date = $request->created_at;
    //$to_date = $request->created_at;
    //$status = $request->status;

// $status=DB::SELECT("SELECT `status` FROM `users` WHERE id=$userid"); 
// //dd($status);
// 	$ddd=$status[0]->status;
// 	//dd($ddd);
// if($ddd == 1){hea
    $where = [];

    if (!empty($userid)) {
        $where[] = "wallet_histories.`user_id` = '$userid'";
    }

    if (!empty($from_date)) {
		$newDateString = date("Y-m-d", strtotime($from_date));
		
        $where[] = "DATE(`wallet_histories`.`created_at`) = '$newDateString'";
		
    }
    if (!empty($subtype)) {
        $where[] = "`wallet_histories`.`type_id` = '$subtype'";
    }
    //
    //
    
    $query = "
       SELECT types.name as type , wallet_histories.amount as amount, wallet_histories.created_at as datetime FROM `wallet_histories` LEFT JOIN `types` on wallet_histories.type_id = types.id
    ";

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY wallet_histories.id DESC";

    $results = DB::select($query);
    //dd($results);
if(!empty($results)){
    return response()->json([
        'status' => 200,
        'message' => 'Data found',
        'data' => $results
    ]);
}else{
     return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
}
// }else{
    
//  $response['message'] = "User block by admin..!";
//                 $response['status'] = "401";
//                 return response()->json($response,401);
    
// }
    
}

public function getPaymentLimits()
{
    $details = DB::select("SELECT `name`, `amount` FROM `payment_limits` WHERE 1");

    if ($details) {
        $formattedData = [];
        foreach ($details as $detail) {
            $formattedData[$detail->name] = $detail->amount;
        }

        return response()->json([
            'status' => 200,
            'message' => 'Data found',
            'data' => $formattedData
        ]);
    } else {
        return response()->json([
            'message' => 'No record found',
            'status' => 400,
            'data' => []
        ], 400);
    }
}


// public function usdt_payin(Request $request)
// {
//     // Validate only the required fields for type 2
//     $validator = Validator::make($request->all(), [
//         'user_id' => 'required|exists:users,id',
//         'cash' => 'required|numeric',
//         'type' => 'required|integer',
//         'screenshot' => 'required|string',  // Assuming screenshot is required
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ]);
//     }

//     // Assign variables
//     $usdt = $request->cash;
//     $image = $request->screenshot;
//     $userid = $request->user_id;
//     $inr = $usdt;  // Assuming conversion is not needed
//     $datetime = now();
//     $orderid = date('YmdHis') . rand(11111, 99999);

//     // Check if screenshot is empty or invalid
//     if (empty($image) || $image === '0' || $image === 'null' || $image === null || $image === '' || $image === 0) {
//         return response()->json([
//             'status' => 400,
//             'message' => 'Please Select Image'
//         ]);
//     }

//     $path = '';

//     // Save image if provided
//     if (!empty($image)) {
//         $imageData = base64_decode($image);
//         if ($imageData === false) {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'Invalid base64 encoded image'
//             ]);
//         }

//         $newName = Str::random(6) . '.png';
//         $path = 'usdt_images/' . $newName;

//         if (!file_put_contents(public_path($path), $imageData)) {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'Failed to save image'
//             ]);
//         }
//     }

//     // Only handle type 2 payment
//     if ($request->has('type') && $request->type == 2) {
//         $insert_usdt = DB::table('payins')->insert([
//             'user_id' => $userid,
//             // 'cash' => $inr,
//             // 'usdt_amount' => $usdt,
//               'cash' => $usdt*93,
//             'usdt_amount' => $inr,
//             'type' => 2, // Always 2 as per your requirement
//             'screenshot' => $path,
//             'order_id' => $orderid,
//             'status' => 1,
//             'created_at' => $datetime,
//             'updated_at' => $datetime
//         ]);

//         if ($insert_usdt) {
//             return response()->json([
//                 'status' => 200,
//                 'message' => 'USDT Payment Request sent successfully. Please wait for admin approval.'
//             ]);
//         } else {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'Failed to process payment'
//             ]);
//         }
//     } else {
//         return response()->json([
//             'status' => 400,
//             'message' => 'Invalid payment type or type not supported.'
//         ]);
//     }
// }

public function usdt_payin(Request $request)
{
    // Validate only the required fields for type 2
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'cash' => 'required|numeric',
        'type' => 'required|integer',
        'screenshot' => 'required|string',  // Assuming screenshot is required
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ]);
    }

    // Assign variables
    $usdt = $request->cash;
    $image = $request->screenshot;
    $userid = $request->user_id;
    $inr = $usdt;  // Assuming conversion is not needed
    $datetime = now();
    $orderid = date('YmdHis') . rand(11111, 99999);

    // Check if screenshot is empty or invalid
    if (empty($image) || $image === '0' || $image === 'null' || $image === null || $image === '' || $image === 0) {
        return response()->json([
            'status' => 400,
            'message' => 'Please Select Image'
        ]);
    }

    $path = '';

    // Save image if provided
    if (!empty($image)) {
        $imageData = base64_decode($image);
        if ($imageData === false) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid base64 encoded image'
            ]);
        }

        $newName = Str::random(6) . '.png';
        $path = 'usdt_images/' . $newName;

        if (!file_put_contents(public_path($path), $imageData)) {
            return response()->json([
                'status' => 400,
                'message' => 'Failed to save image'
            ]);
        }
    }

    // Fetch the conversion rate from the payment_limits table where id = 13
    $conversionRate = DB::table('payment_limits')->where('id', 13)->value('amount');

    // If the conversion rate is not available, return an error
    if (!$conversionRate || $conversionRate <= 0) {
        return response()->json([
            'status' => 400,
            'message' => 'Invalid or missing deposit conversion rate'
        ]);
    }

    // Cast to float to avoid string multiplication error
    $usdt = floatval($usdt);
    $conversionRate = floatval($conversionRate);

    // Only handle type 2 payment
    if ($request->has('type') && $request->type == 2) {
        $insert_usdt = DB::table('payins')->insert([
            'user_id' => $userid,
            'cash' => $usdt * $conversionRate,  // Use dynamic conversion rate
            'usdt_amount' => $inr,
            'type' => 2, // Always 2 as per your requirement
            'screenshot' => $path,
            'order_id' => $orderid,
            'status' => 1,
            'created_at' => $datetime,
            'updated_at' => $datetime
        ]);

        if ($insert_usdt) {
            return response()->json([
                'status' => 200,
                'message' => 'USDT Payment Request sent successfully. Please wait for admin approval.'
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Failed to process payment'
            ]);
        }
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'Invalid payment type or type not supported.'
        ]);
    }
}





public function qr_view(Request $request)
{
    // Fetch all records from the usdt_qr table
    $show_qr = DB::select("SELECT * FROM `usdt_qr`");

    if ($show_qr) {
        $response = [
            'message' => 'Successfully retrieved records.',
            'status' => 200,
            'data' => $show_qr
        ];

        return response()->json($response, 200);
    } else {
        return response()->json([
            'message' => 'No record found',
            'status' => 400,
            'data' => []
        ], 400);
    }
}

public function withdraw_history(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'type' => 'required',
    ]);

    $date = date('Y-m-d h:i:s');
    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    $user_id = $request->user_id;
    $status = $request->status;
    $type = $request->type;  
    $created_at = $request->created_at; 
    $where = [];

    if (!empty($user_id)) {
        $where[] = "withdraws.`user_id` = '$user_id'";
    }

    if (!empty($status)) {
        $where[] = "`withdraws`.`status` = '$status'";
    }

    // Modified the condition for type: check if it's 0 or 1, then adjust the query to check for both
    if (!empty($type)) {
        if ($type == '0' || $type == '1') {
            $where[] = "`withdraws`.`type` IN (0, 1)";
        } else {
            $where[] = "`withdraws`.`type` = '$type'";
        }
    }

    if (!empty($created_at)) {
        $newDateString = date("Y-m-d", strtotime($created_at));
        $where[] = "DATE(`withdraws`.`created_at`) = '$newDateString'";
    }

    $query = "SELECT `id`, `user_id`, `amount`, `type`, `status`, `typeimage`, `order_id`, `created_at` FROM withdraws ";

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY withdraws.id DESC";

    $payin = DB::select($query);

    if ($payin) {
        $response = [
            'message' => 'Successfully',
            'status' => 200,
            'data' => $payin
        ];

        return response()->json($response, 200);
    } else {
        return response()->json(['message' => 'No record found', 'status' => 200, 'data' => []], 400);
    }
}


//  public function withdraw_history(Request $request)
//       {
//          $validator = Validator::make($request->all(), [
//         'user_id' => 'required',
// 		'type'=>'required',
//     ]);

//     $date = date('Y-m-d h:i:s');
//      $validator->stopOnFirstFailure();
	   
//     if($validator->fails()){
		
//         return response()->json([
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ],400);
//     }
   
//      $user_id = $request->user_id;
//      $status = $request->status;
//       $type = $request->type;  
// 		$created_at = $request->created_at; 
//          $where = [];

//     if (!empty($user_id)) {
//         $where[] = "withdraw_histories.`user_id` = '$user_id'";
//     }

//     if (!empty($status)) {
//         $where[] = "`withdraw_histories`.`status` = '$status'";
//     }
// 	if (!empty($type)) {
//         $where[] = "`withdraw_histories`.`type` = '$type'";
//     }
// 	if (!empty($created_at)) {
// 		$newDateString = date("Y-m-d", strtotime($created_at));
//         $where[] = "DATE(`withdraw_histories`.`created_at`) = '$newDateString'";

//     }
    
//     $query = "SELECT `id`,`user_id`,`amount`,`type`,`status`,`typeimage`,`order_id`,`created_at` FROM withdraw_histories ";

//     if (!empty($where)) {
//         $query .= " WHERE " . implode(" AND ", $where);
//     }

//     $query .= " ORDER BY withdraw_histories.id DESC";

//     $payin = DB::select($query);

//         if ($payin) {
//             $response = [
//                 'message' => 'Successfully',
//                 'status' => 200,
//                 'data' => $payin
//             ];

//             return response()->json($response,200);
//         } else {
//             return response()->json(['message' => 'No record found','status' => 200,'data' => []], 400);
//         }
//     }


	
	

}
