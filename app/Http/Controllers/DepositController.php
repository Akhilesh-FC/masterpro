<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class DepositController extends Controller
{
    public function deposit_index($id)
    {
         $deposits= DB::select("SELECT payins.*,users.name AS uname,users.u_id As userid, users.mobile As mobile FROM `payins` LEFT JOIN users ON payins.user_id=users.id WHERE payins.type = 0 && payins.status = $id");
        return view('work_order_assign.deposit')->with('deposits',$deposits)->with('id',$id);
    }
  
  
  
  
  

}
