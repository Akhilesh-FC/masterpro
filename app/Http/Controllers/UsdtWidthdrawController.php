<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class UsdtWidthdrawController extends Controller
{
    public function usdt_widthdrawl_index($id)
    {
		//dd($id);
        // Fetch all records from the Project_maintenance model
//         $widthdrawls = DB::select("SELECT withdraws.*, users.name AS uname, users.mobile AS mobile,users.illegal_count AS illegal_count, usdt_account_deatils.name as  beneficiary_name FROM withdraws JOIN users ON withdraws.user_id = users.id JOIN usdt_account_deatils ON withdraws.`account_id` = usdt_account_deatils.id where withdraws.type=2 && withdraws.status=$id
// ");
$widthdrawls = DB::select("
    SELECT withdraws.*, 
           users.name AS uname, 
            users.u_id AS user_id, 
           users.mobile AS mobile, 
           users.illegal_count AS illegal_count, 
           usdt_account_deatils.name AS beneficiary_name, 
           usdt_account_deatils.usdt_wallet_address 
    FROM withdraws 
    JOIN users ON withdraws.user_id = users.id 
    JOIN usdt_account_deatils ON withdraws.account_id = usdt_account_deatils.id 
    WHERE withdraws.type = 2 
    AND withdraws.status = $id
");

//dd($widthdrawls);
        // Pass the data to the view and load the 'usdt_withdraw.index' Blade file
        return view('usdt_withdraw.index', compact('widthdrawls'))->with('id', $id);
		//dd($widthdrawls);
    }

    public function usdt_success(Request $request, $id)
    {
		//dd($request);
        // Check if the session has an 'id' key
        if ($request->session()->has('id')) {
            // Use parameter binding to prevent SQL injection
            DB::table('withdraws')
                ->where('id', $id)
                ->update(['status' => 2]);

            // Redirect with route and parameters
            return redirect()->route('usdt_widthdrawl',1)->with('success', 'approved successfully');
        } else {
            // Redirect to login if session does not have 'id'
            return redirect()->route('login');
        }
    }

    public function usdt_reject(Request $request, $id)
    {
		 $rejectionReason = $request->input('msg');
        // Retrieve the withdrawal history for the given id
        $data = DB::table('withdraws')->where('id', $id)->first();
        
        // If no data is found, handle it appropriately
        if (!$data) {
            // Handle the case where no withdrawal history is found
            return redirect()->route('usdt_widthdrawl', 1)->with('error', 'Withdrawal history not found.');
        }

        $amt = $data->actual_amount;
        $useid = $data->user_id;

        // Check if the session has an 'id' key
        if ($request->session()->has('id')) {
            // Use Query Builder to perform updates safely
			
			DB::table('withdraws')
    ->where('id', $id)
    ->update([
        'status' => 3,
        'rejectmsg' => $rejectionReason
    ]);
			 DB::update("UPDATE `users` SET `illegal_count` = 0 WHERE id = ?", [$useid]);

            DB::table('users')->where('id', $useid)->increment('wallet', $amt);
            
            // Redirect with route and parameters
            return redirect()->route('usdt_widthdrawl', 1)->with('success', 'reject successfully');
        } else {
            // Redirect to login if session does not have 'id'
            return redirect()->route('login');
        }
    }

    public function all_success(Request $request)
    {
        // Check if the session has an 'id' key
        if ($request->session()->has('id')) {
            // Use Query Builder to perform the update safely
            DB::table('withdraw_histories')
                ->where('status', 1)
                ->update(['status' => 2]);

            // Retrieve updated withdrawal histories
            $widthdrawls = DB::table('withdraw_histories')->get();

            // Return the view with the updated data
            return view('widthdrawl.index', compact('widthdrawls'))->with('id', '1');
        } else {
            // Redirect to login if session does not have 'id'
            return redirect()->route('login');
        }
    }
}
