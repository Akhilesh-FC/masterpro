<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RebateSystemController extends Controller
{
// 	public function rebateDetails(Request $request)
// {
//     $query = DB::table('users')->where('illegal_count', '>', 0);

//     if ($request->has('u_id') && !empty($request->u_id)) {
//         $query->where('u_id', $request->u_id);
//     }


//     $usersWithIllegalCount = $query->get();

//     $userDetails = [];
//     foreach ($usersWithIllegalCount as $user) {
//         $userDetails[] = [
//             'userid' => $user->id,
//             'u_id' => $user->u_id,
//             'illegalCount' => $user->illegal_count,
//             'recharge' => $user->recharge,
//             'userStatus' => $user->status,
//         ];
//     }

//     return view('rebatesystem.index', [
//         'users' => $userDetails,
//         'search_id' => $request->u_id // Search value ko wapas bhejna taake input box mein dikhe
//     ]);
// }
public function rebateDetails(Request $request)
{
    $query = DB::table('users')->where('illegal_count', '>', 0);

    if ($request->has('u_id') && !empty($request->u_id)) {
        $query->where('u_id', $request->u_id);
    }

    $usersWithIllegalCount = $query->paginate(10); // Yahan pagination add kiya, har page me 10 records dikhane ke liye

    $userDetails = [];
    foreach ($usersWithIllegalCount as $user) {
        $userDetails[] = [
            'userid' => $user->id,
            'u_id' => $user->u_id,
            'illegalCount' => $user->illegal_count,
            'recharge' => $user->recharge,
            'userStatus' => $user->status,
        ];
    }

    return view('rebatesystem.index', [
        'users' => $usersWithIllegalCount, // Paginated data pass kiya
        'search_id' => $request->u_id
    ]);
}

// 	public function rebateDetails(Request $request)
// {
//     $query = DB::table('users')->where('illegal_count', '>', 0);

//     if ($request->has('u_id') && !empty($request->u_id)) {
//         $query->where('u_id', $request->u_id);
//     }

//     // Pagination add kiya (10 records per page)
//     $usersWithIllegalCount = $query->paginate(10);

//     return view('rebatesystem.index', [
//         'users' => $usersWithIllegalCount, // Paginated data bhejna
//         'search_id' => $request->u_id
//     ]);
// }



public function updateRebate(Request $request)
{
    $userId = $request->input('user_id');
    $amount = $request->input('amount'); 
    $action = $request->input('action'); 

    if (!is_numeric($amount) || $amount <= 0) {
        return response()->json(['success' => false, 'error' => 'Invalid amount entered.']);
    }

    $user = DB::table('users')->where('id', $userId)->first();
    if (!$user) {
        return response()->json(['success' => false, 'error' => 'User not found.']);
    }

    $currentRecharge = $user->recharge;

    if ($action === 'increase') {
        DB::table('users')->where('id', $userId)->increment('recharge', $amount);
    } elseif ($action === 'decrease') {
        if ($currentRecharge < $amount) {
            return response()->json(['success' => false, 'error' => 'Insufficient balance!']);
        }
        DB::table('users')->where('id', $userId)->decrement('recharge', $amount);
    }

    $newBalance = DB::table('users')->where('id', $userId)->value('recharge');

    return response()->json(['success' => true, 'new_balance' => $newBalance]);
}
	
	public function unblockUser(Request $request)
{
    $userId = $request->input('user_id');

    // Check if user exists and is blocked
    $user = DB::table('users')->where('id', $userId)->where('status', 0)->first();
    if (!$user) {
        return response()->json(['success' => false, 'error' => 'User not found or already active.']);
    }

    // Update status to 1 (unblock)
    DB::table('users')->where('id', $userId)->update(['status' => 1]);

    return response()->json(['success' => true, 'message' => 'User unblocked successfully!']);
}


}
