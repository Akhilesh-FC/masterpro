<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use  Illuminate\Support\Facades\DB;

class UsdtController extends Controller
{
    public function usdt_view()
    {
        $usdt = DB::select("SELECT * FROM `usdt_qr`");

        return view('usdt.usdt_qr', compact('usdt'));
    }


        public function update_usdtqr(Request $request, $id){
                // Validate the request to ensure an image file is provided
             

                // Handle the uploaded image
                $image = $request->file('image');
                $wallet_address = $request->wallet_address;
			    $originalName = $image->getClientOriginalName();
                $path = 'uploads/' . $originalName;

                // Save the image to the public disk
                if (!file_put_contents(public_path($path), file_get_contents($image->getRealPath()))) {
                        return redirect()->back()->with('message', 'Failed to update image!');
                }

                // Update the database record
               DB::table('usdt_qr')->where('id', $id)->update([
                        'qr_code' => env('APP_URL') . $path,
                        'wallet_address' => $wallet_address
                    ]);

                return redirect()->back()->with('message', 'updated successfully!');
        }





 
}