<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\{User,BusinessSetting};

// use App\Models\Project_maintenance;
use App\Helper\jilli;

class WidthdrawlController extends Controller
{
    public function widthdrawl_index($id)
    {
		
        // Fetch all records from the Project_maintenance model
        $widthdrawls = DB::select("SELECT withdraws.*, users.id AS user_id, users.u_id AS userid, users.name AS uname, users.mobile AS mobile, users.illegal_count AS illegal_count, bank_details.account_num AS acno, bank_details.bank_name AS bname, bank_details.ifsc_code AS ifsc,bank_details.upi_id AS upi_id FROM withdraws LEFT JOIN users ON withdraws.user_id = users.id LEFT JOIN bank_details ON bank_details.id = withdraws.account_id WHERE withdraws.`status`=$id && withdraws.type=0 order by withdraws.id desc ;");

        // Pass the data to the view and load the 'project_maintenance.index' Blade file
        return view('widthdrawl.index', compact('widthdrawls'))->with($id,'id');
	 
			
    }

 public function success_by_upi(Request $request, $id)
{
    //dd($id);
    // Check if session has 'id'
    if (!$request->session()->has('id')) {
        return redirect()->route('login');
    }

    // Fetch required data using parameter binding to prevent SQL injection
    $data=DB::select("SELECT bank_details.*, users.email AS email, users.mobile AS mobile, withdraws.amount AS amount, business_settings.longtext AS mid, (SELECT business_settings.longtext FROM business_settings WHERE id = 13) AS token, (SELECT business_settings.longtext FROM business_settings WHERE id = 14 ) AS orderid FROM bank_details LEFT JOIN users ON bank_details.userid = users.id LEFT JOIN withdraws ON withdraws.user_id = users.id && withdraws.account_id=bank_details.id LEFT JOIN business_settings ON business_settings.id = 12 WHERE withdraws.id=$id;");

//dd($data);
    // Check if withdrawal data is found
    if (empty($data)) {
        return redirect()->route('widthdrawl', '1')->with('error', 'No withdrawal data found for the specified ID.');
    }

    // Extract required values
    $object = $data[0];
    $upiid = $object->upi_id;
    $amount = $object->amount;
    $mid = $object->mid;
    $token = $object->token;

    $randid = rand(11111111111111, 99999999999999);

    // Initialize cURL request
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://indianpay.co.in/admin/PayViaUpi?upiid=$upiid&amount=$amount&merchantId=$mid&token=$token&orderid=$randid",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
    ]);

    $response = curl_exec($curl);

    // Check for cURL errors
    if (curl_errno($curl)) {
        curl_close($curl);
        return redirect()->back()->with('error', 'CURL Error: ' . curl_error($curl));
    }

    curl_close($curl);

    // Check if response is empty
    if (empty($response)) {
        return redirect()->back()->with('error', 'Empty response from the server');
    }

    // Decode JSON response
    $datta = json_decode($response);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        return redirect()->back()->with('error', 'Invalid JSON response');
    }

    // Check if response has expected structure
    if (!is_object($datta) || !isset($datta->status)) {
        return redirect()->back()->with('error', 'Unexpected response structure');
    }

    $status = $datta->status;
    $error = $datta->error ?? 'Unknown error';

    // Handle response status
    if ($status == 400) {
        return redirect()->back()->with('error', $error);
    }

    // Update withdrawal history
    DB::update("UPDATE `withdraw_histories` SET `status` = ?, `response` = ?, `remark` = 'by upi' WHERE id = ?", [2, $response, $id]);

    return redirect()->route('widthdrawl', '1')->with('success', 'Transaction processed successfully.');
}


    public function success(Request $request,$id)
    {
		$value = $request->session()->has('id');
		
     if(!empty($value))
        {
        
         $data=DB::select("SELECT bank_details.*, users.email AS email, users.mobile AS mobile, withdraws.amount AS amount, business_settings.longtext AS mid, (SELECT business_settings.longtext FROM business_settings WHERE id = 13) AS token, (SELECT business_settings.longtext FROM business_settings WHERE id = 14 ) AS orderid FROM bank_details LEFT JOIN users ON bank_details.userid = users.id LEFT JOIN withdraws ON withdraws.user_id = users.id && withdraws.account_id=bank_details.id LEFT JOIN business_settings ON business_settings.id = 12 WHERE withdraws.id=$id;");
      
       //dd($data);
         foreach ($data as $object) {
            
            // $object->amount
            $name= $object->name;
            $ac_no= $object->account_num;
            $ifsc=$object->ifsc_code;
            $bankname= $object->bank_name;
            $email= $object->email;
            $mobile=$object->mobile;
            $amount=$object->amount;
            $mid=$object->mid;
            $token=$object->token;
            $orderid=$object->orderid;
        }
		//echo $mid;
        $rand=rand(11111111,99999999);
      $randid="$rand";
      //$amount
       $payoutdata=  json_encode(array(    
         "merchant_id"=>$mid,
         "merchant_token"=>$token,
         "account_no"=>$ac_no,
         "ifsccode"=>$ifsc,
         "amount"=>$amount,
         "bankname"=>$bankname,
         "remark"=>"payout",
         "orderid"=>$randid,
         "name"=>$name,
         "contact"=>$mobile,
         "email"=>$email
      ));
       //dd($payoutdata);
    // Encode the payout data using base64
    $salt = base64_encode($payoutdata);
    
    // Prepare the JSON data
    $json = [
        "salt" => $salt
    ];
    
    // Initialize cURL session
    $curl = curl_init();
    
    // Set cURL options
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://indianpay.co.in/admin/single_transaction',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($json), // Encode JSON data
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json' // Set Content-Type header
        ),
    ));
    
    // Execute cURL request and get the response
    $response = curl_exec($curl);
    //dd($response);
    // Check for errors
    if (curl_errno($curl)) {
        echo 'Error: ' . curl_error($curl);
    } else {
        // Print the response
        echo $response;
    }
    
    // Close cURL session
    curl_close($curl);
	
    DB::select("UPDATE `withdraws` SET `status`='2',`response`='$response' WHERE id=$id;");
		 return redirect()->route('widthdrawl', '1')->with('key', 'value');

    }
		else
        {
           return redirect()->route('login');  
        }
			
			
    }
	
	
		
	
	public function reject(Request $request,$id)
  	{	
  	$rejectionReason = $request->input('msg');
		
		$data=DB::select("SELECT * FROM `withdraws` WHERE id=$id;");
		$amt=$data[0]->amount;
		$useid=$data[0]->user_id;
         $value = $request->session()->has('id');
			
     if(!empty($value))
        {
            // dd("UPDATE `withdraws` SET `status`='3' WHERE id=$id;");
     $ss= DB::select("UPDATE `withdraws` SET `status`='3',`rejectmsg`='$rejectionReason' WHERE id=$id;");
    //dd("UPDATE `users` SET `wallet`=`wallet`+'$amt' WHERE id=$useid;");
	DB::select("UPDATE `users` SET `wallet`=`wallet`+'$amt' WHERE id=$useid;");
		 DB::update("UPDATE `users` SET `illegal_count` = 0 WHERE id = ?", [$useid]);
		  DB::update("UPDATE `bets` SET `illegal_status` = 0 WHERE userid = ?", [$useid]);
		 
		  
		 $deduct_jili = jilli::add_in_jilli_wallet($useid,$amt);
	
		return redirect()->route('widthdrawl', '1')->with('key', 'value');
		  }
		 else
        {
           return redirect()->route('login');  
        }
		
  }

		
		
    
    public function all_success()    
    {           
		$value = $request->session()->has('id');
		
     if(!empty($value))
        {
      DB::select("UPDATE `withdraws` SET `status`='2' WHERE `status`='1';");
		         return view('widthdrawl.index', compact('widthdrawls'))->with($id,'1');
	 }
else
        {
           return redirect()->route('login');  
        }
			
      //return redirect()->route('widthdrawl/0');
    }
	
	public function indiaonlin_payout(Request $request,$id)
    {
		$value = $request->session()->has('id');
		
     if(!empty($value))
        {
        
         $data=DB::select("SELECT bank_details.*, users.email AS email, users.mobile AS mobile, withdraws.amount AS amount, business_settings.longtext AS mid, (SELECT business_settings.longtext FROM business_settings WHERE id = 13) AS token, (SELECT business_settings.longtext FROM business_settings WHERE id = 14 ) AS orderid FROM bank_details LEFT JOIN users ON bank_details.userid = users.id LEFT JOIN withdraws ON withdraws.user_id = users.id && withdraws.account_id=bank_details.id LEFT JOIN business_settings ON business_settings.id = 12 WHERE withdraws.id=$id;");
       
         foreach ($data as $object) {
            
            $name= $object->name;
            $ac_no= $object->account_num;
            $ifsc=$object->ifsc_code;
            $bankname= $object->bank_name;
            $email= $object->email;
            $mobile=$object->mobile;
            $amount=$object->amount;
           
            $token=$object->token;
            $orderid=$object->orderid;
        }
$rand = rand(11111111, 99999999);
$date = date('YmdHis');
$invoiceNumber = $date . $rand;
		 
		$data = [
    "merchantId" => "",
    "secretKey" => "",
    "apiKey" => "5692d831-decd-450c-8ff5-d1d11943dc82",
    "invoiceNumber" => $invoiceNumber,
    "customerName" => $name,
    "phoneNumber" => $mobile,
    "payoutMode" => "IMPS",
    "payoutAmount" => 1,
    "accountNo" => $ac_no,
    "ifscBankCode" => $ifsc,
    "ipAddress" => "35.154.155.190"
];

		 
         $encodeddata=json_encode($data);
		
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://indiaonlinepay.com/api/iop/payout',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>$encodeddata,
			  CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Cookie: Path=/'
			  ),
			));

			$response = curl_exec($curl);

			curl_close($curl);
		 
			echo  $response; 
		 $dataArray = json_decode($response, true);

         $referenceId=$dataArray['Data']['ReferenceId'];
		 $Status=$dataArray['Data']['Status'];
		 if($Status == "Received"){
		 
   
         DB::select("UPDATE `withdraws` SET `referenceId`='$referenceId',`response`='$response',status='2' WHERE id=$id;");
		 return redirect()->route('widthdrawl', '1')->with('key', 'value');
		 }
       return redirect()->route('widthdrawl', '1')->with('key', 'value');
    }
		else
        {
           return redirect()->route('login');  
        }
			
			
    }
	
	
	



}
