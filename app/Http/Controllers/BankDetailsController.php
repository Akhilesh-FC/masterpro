<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankDetailsController extends Controller
{
   
public function index(Request $request)
{
    $query = DB::table('bank_details');

    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('userid', 'LIKE', "%$search%")
              ->orWhere('name', 'LIKE', "%$search%")
              ->orWhere('account_num', 'LIKE', "%$search%")
              ->orWhere('bank_name', 'LIKE', "%$search%")
              ->orWhere('ifsc_code', 'LIKE', "%$search%")
              ->orWhere('upi_id', 'LIKE', "%$search%")
              ->orWhere('branch_name', 'LIKE', "%$search%");
        });
    }

    $bank_details = $query->paginate(10); // 10 records per page

    return view('bank_details.index', compact('bank_details'));
}



    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
            'account_num' => 'required|string|max:50',
            'bank_name' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:50',
            'branch_name' => 'required|string|max:255',
        ]);

        DB::table('bank_details')
            ->where('id', $request->id)
            ->update([
                'name' => $request->name,
                'account_num' => $request->account_num,
                'bank_name' => $request->bank_name,
                'ifsc_code' => $request->ifsc_code,
                'branch_name' => $request->branch_name,
            ]);

        return response()->json(['success' => 'Bank details updated successfully!']);
    }
	
	public function delete(Request $request) {
    DB::table('bank_details')->where('id', $request->id)->delete();
    return response()->json(['success' => 'Bank details deleted successfully!']);
}

   public function usdt_index(Request $request)  
{  
    $query = DB::table('usdt_account_deatils'); // Ensure correct table name  
//dd($request);
    if ($request->has('search') && !empty($request->search)) {  
        $search = $request->search;  
        $query->where(function($q) use ($search) {  
            $q->where('user_id', 'LIKE', "%$search%")  
              ->orWhere('name', 'LIKE', "%$search%")  
              ->orWhere('usdt_wallet_address', 'LIKE', "%$search%");  
        });  
    }  
//dd($query->toSql(), $query->getBindings()); 
    $usdt_details = $query->paginate(10)->appends(['search' => $request->search]);
 // Ensure proper pagination  

    return view('bank_details.usdt', compact('usdt_details'));  
}


    public function usdtupdateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:usdt_account_deatils,id', // Ensure correct table name
            'name' => 'required|string',
            'usdt_wallet_address' => 'required|string'
        ]);

        DB::table('usdt_account_deatils')
            ->where('id', $request->id)
            ->update([
                'name' => $request->name,
                'usdt_wallet_address' => $request->usdt_wallet_address,
                'updated_at' => now()
            ]);

        return response()->json(['success' => 'USDT details updated successfully!']);
    }
	
	public function usdt_delete(Request $request) {
    DB::table('usdt_account_deatils')->where('id', $request->id)->delete();
    return response()->json(['success' => 'Usdt details deleted successfully!']);
	
	}
}


