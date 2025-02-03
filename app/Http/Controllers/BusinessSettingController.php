<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User,BusinessSetting,Game};
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class BusinessSettingController extends Controller
{
    public function businessSetting_index(){
        $businessSetting = BusinessSetting::find(1);

        return view('BusinessSetting.index')->with('businessSetting', $businessSetting);
      

    }

    public function businessSettingUpdate(Request $request,$id){

        $validated = $request->validate([
            'project_name' => 'required',
            'project_title' => 'required',
            // 'logo' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'payment_key' => 'required',
            'merchant_token' => 'required',
            'betting_commission' => 'required',
            'first_deposit_amount' => 'required',
            'min_deposit' => 'required',
            'max_deposit' => 'required',
            'min_withdraw' => 'required',
            'max_withdraw' => 'required',
            // 'game_id' => 'required|array',
        ]);
        $businessSetting=BusinessSetting::findOrFail($id);

       


        $data = array(
            'project_name' => $request->project_name,
            'project_title' => $request->project_title,
            // 'logo' => $request->file('logo')->store('logo_image'),
            'payment_key' => $request->payment_key,
            'merchant_token' => $request->merchant_token,
            'betting_commission' => $request->betting_commission,
            'first_deposit_amount' => $request->first_deposit_amount,
            'min_deposit' => $request->min_deposit,
            'max_deposit' => $request->max_deposit,
            'min_withdraw' => $request->min_withdraw,
            'max_withdraw' => $request->max_withdraw,
            // 'game_id' => json_encode($request->game_id),
            'status' => 1 ,
        );
        $businessSetting->update($data);
        return redirect()->back()->with('success', 'Business setting successfully!');
    }


}
