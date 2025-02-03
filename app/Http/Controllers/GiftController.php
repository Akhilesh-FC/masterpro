<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\Invoice;
Use App\Models\{GiftCard,GiftClaim};
use DB;

class GiftController extends Controller
{

    //  public function index()
    //  {
    //       $gifts = DB::select("SELECT * FROM `gift_cards` ORDER BY `gift_cards`.`id` DESC");
    //      return view('gift.index')->with('gifts',$gifts);

    //  }

public function index()
{
    $gifts = GiftCard::orderBy('id', 'DESC')->get();
    return view('gift.index')->with('gifts', $gifts);
}

//     public function gift_store(Request $request)
//     {
// 		$datetime=now();
//      $amount=$request->amount;
//      $number_people=$request->number_people;
//      $rand=rand(000000000000000,999999999999999);
//         $data = DB::insert("INSERT INTO `gift_cards`(`amount`, `number_people`,`code`,`status`,`created_at`,`updated_at`) VALUES ('$amount','$number_people','$rand','1','$datetime','$datetime')");
//             return redirect()->route('gift')->with('data',$data)->with('success','Gift Added Successfully ..!');    
//     }

public function gift_store(Request $request)
{
    $giftCard = new GiftCard();
    $giftCard->amount = $request->amount;
    $giftCard->number_people = $request->number_people;
    $giftCard->code = rand(000000000000000, 999999999999999);
    $giftCard->status = 1;
    $giftCard->created_at = now();
    $giftCard->updated_at = now();
    $giftCard->save();

    return redirect()->route('gift')->with('data', $giftCard)->with('success', 'Gift Added Successfully ..!');
}
	
// 	 public function giftredeemed()
// {
//     $gifts = DB::select("
//         SELECT gift_claims.*, users.name 
//         FROM gift_claims 
//         LEFT JOIN users ON gift_claims.userid = users.id 
//         ORDER BY gift_claims.id DESC
//     ");

//     return view('gift.giftredeemed')->with('gifts', $gifts);
// }

public function giftredeemed()
{
    $gifts = GiftClaim::with('user')
        ->orderBy('id', 'DESC')
        ->get();

    return view('gift.giftredeemed')->with('gifts', $gifts);
}



//   public function GiftDelete($id){
//       $gift = GiftCard::findOrFail($id);
//       $gift->delete();
//       return redirect()->back()->with('success', 'Gift Delete Successfully..!');
//   }

public function GiftDelete($id) {
    // Use the GiftCard model to find and delete the gift card by ID
    $gift = GiftCard::findOrFail($id);
    $gift->delete();
    
    // Redirect back with a success message
    return redirect()->back()->with('success', 'Gift Deleted Successfully..!');
}


}
