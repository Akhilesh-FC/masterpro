<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentLimitController extends Controller
{
    
    public function index()
    {
        $payment_limits = DB::table('payment_limits')
                            ->whereIn('id', [13, 14])
                            ->get();

        return view('payment_limits.index', compact('payment_limits'));
    }

    // Update Payment Limit
    public function update(Request $request)
{
    $request->validate([
        'id' => 'required|in:13,14',
        'amount' => 'required|numeric|min:1'
    ]);

    DB::table('payment_limits')
        ->where('id', $request->id)
        ->update([
            'amount' => $request->amount, // Only Amount is Updated
            'updated_at' => now()
        ]);

    return response()->json(['success' => 'Payment limit updated successfully!']);
}

}
