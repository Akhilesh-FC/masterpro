<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{PublicApiController,GameApiController,AviatorApiController,AgencyPromotionController,SalaryApiController,VipController,ZiliApiController,TestJilliController,SpribeApiController,IfscApiController};

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('get-ifsc-details', [IfscApiController::class, 'getIfscDetails']);

//// VIP Routes////
Route::get('/vip_level',[VipController::class,'vip_level']);
Route::get('/vip_level_history',[VipController::class,'vip_level_history']);
Route::post('/add_money',[VipController::class,'receive_money']);

Route::get('/gameSerialNo',[GameApiController::class,'gameSerialNo']);


Route::controller(PublicApiController::class)->group(function () {
   
	
    //// uses only web ///
    Route::post('/otp-register',[PublicApiController::class,'otp_register']);
     //// uses only web ///
    Route::post('/register', 'registers');
    Route::post('/check_number', 'check_existsnumber');
    Route::post('/login', 'login');
    Route::get('/profile/{id}', 'Profile');
	Route::post('/update_profile','update_profile');
	Route::get('/image_all','image_all');
    Route::get('/slider','slider_image_view');
    Route::post('/changepassword','changepassword');
    Route::post('/forget_Password','resetPassword');
	Route::get('/about_us','about_us');
    Route::post('/addAccount','addAccount');
    Route::get('/accountView','accountView');
	Route::post('/kuber_payin','kuber_payin');
	Route::get('/kuber_checkPayment','kuber_checkPayment');
    Route::post('/payin','payin');
	Route::get('/pay_modes','pay_modes');
    Route::get('/checkPayment','checkPayment');
    Route::get('/payin-successfully','redirect_success')->name('payin.successfully');
    Route::post('/withdraw','withdraw');
    Route::get('/withdraw_history','withdraw_history');
	Route::get('/commission_details','commission_details');
    Route::get('/deposit-history','deposit_history');
    Route::get('/account-delete/{id}','accountDelete');
	Route::post('/account_update/{id}','accountUpdate');
    Route::post('/gift_cart_apply','giftCartApply');
    Route::get('/gift_redeem_list','claim_list'); 
	 Route::get('/total_bet_details','total_bet_details');
    Route::get('/customer_service','customer_service');
	Route::post('/wallet_transfers','wallet_transfer');
	Route::post('/main_wallet_transfers','main_wallet_transfer');
	Route::get('/version_apk_link','versionApkLink');
	Route::get('/salary_list','salary_list');
	Route::get('/betting_rebate','betting_rebate');
    Route::get('/betting_rebate_history','betting_rebate_history');
	Route::get('/invitation_bonus_list','invitation_bonus_list');
	Route::get('/Invitation_reward_rule','Invitation_reward_rule');
	Route::get('/Invitation_records','Invitation_records');
	Route::post('/invitation_bonus_claim','invitation_bonus_claim');
	Route::get('/activity_rewards','activity_rewards');
	Route::Post('/activity_rewards_claimold','activity_rewards_claim');

	Route::get('/activity_rewards_history','activity_rewards_history');
	Route::get('/attendance_List','attendance_List');
	Route::get('/attendance_history','attendance_history');
	Route::post('/attendance_claim','attendance_claim');
	
	Route::get('/transaction_history_list','transaction_history_list');
	Route::get('/transaction_history','transaction_history');
	Route::post('/country','country');
	Route::post('/add_usdt_account','add_usdt_account');
	Route::get('/usdt_account_view','usdt_account_view');
	Route::get('/wingo_rules','wingo_rules');
	Route::get('/getPaymentLimits','getPaymentLimits');
	
});

    Route::post('/usdt_payin',[PublicApiController::class,'usdt_payin']);
    Route::post('/payin_call_back',[PublicApiController::class,'payin_call_back']);
    
    Route::get('/qr_view',[PublicApiController::class,'qr_view']);

Route::controller(AgencyPromotionController::class)->group(function () {
    Route::get('/agency-promotion-data-{id}', 'promotion_data');
	Route::get('/new-subordinate', 'new_subordinate');
	Route::get('/tier', 'tier');
	Route::post('/subordinate-data','subordinate_data');
	Route::get('/turnovers','turnover_new');
	//Route::get('/turnover','turnover');
});
Route::controller(GameApiController::class)->group(function () {
     Route::post('/bets', 'bet');
     Route::post('/dragon_bet', 'dragon_bet');
     Route::get('/win_amount', 'win_amount');
     Route::get('/results','results');
     Route::get('/last_five_result','lastFiveResults');
     Route::get('/last_result','lastResults');
     Route::post('/bet_history','bet_history');
     Route::get('/cron/{game_id}/','cron');
     /// mine game route //
    Route::post('/mine_bet','mine_bet'); 
    Route::post('/mine_cashout','mine_cashout');
    Route::get('/mine_result','mine_result');
    Route::get('/mine_multiplier','mine_multiplier');
    
    //// Plinko Game Route /////
    
     Route::post('/plinko_bet','plinkoBet');
    Route::get('/plinko_index_list','plinko_index_list');
    Route::get('/plinko_result','plinko_result');
    Route::get('/plinko_cron','plinko_cron');
    Route::post('/plinko_multiplier','plinko_multiplier'); 
});

// Route::controller(AviatorApiController::class)->group(function () {
// Route::post('/aviator_bet','aviatorBet');
// Route::post('/aviator_cashout','aviator_cashout');
// Route::post('/aviator_history','aviator_history');
// Route::get('/aviator_last_result','aviator_last_result');
// Route::post('/aviator_bet_cancel','bet_cancel');
// Route::get('/result_half_new','result_half_new');
// Route::post('/result_insert_new','result_insert_new');
// });

Route::controller(AviatorApiController::class)->group(function () {
Route::get('/aviator_bet','aviatorBet');
Route::post('/aviator_cashout','aviator_cashout');
Route::post('/aviator_history','aviator_history');
Route::get('/aviator_last_five_result','last_five_result');
Route::get('/aviator_bet_cancel','bet_cancel');
Route::get('/result_half_new','result_half_new');
Route::post('/result_insert_new','result_insert_new');
});

Route::controller(SalaryApiController::class)->group(function () {
    Route::get('/aviator_salary', 'aviator_salary');
    Route::get('/daily_bonus','dailyBonus');
	Route::get('/monthly_bonus','monthlyBonus');

	//Route::get('/turnover','turnover');
});

   ///   akash /////

// Route::post('/usdt_payin',[PublicApiController::class,'payin_usdt']);
// Route::post('/payin_call_back',[PublicApiController::class,'payin_call_back']);

    //// Zili Api ///
Route::post('/user_register',[ZiliApiController::class,'user_register']);  //not in use for registration
Route::post('/all_game_list',[ZiliApiController::class,'all_game_list']);
Route::post('/all_game_list_test',[ZiliApiController::class,'all_game_list_test']);
Route::post('/get_game_url',[ZiliApiController::class,'get_game_url']);
Route::post('/get_jilli_transactons_details',[ZiliApiController::class,'get_jilli_transactons_details']);
Route::post('/jilli_deduct_from_wallet',[ZiliApiController::class,'jilli_deduct_from_wallet']);
Route::post('/jilli_get_bet_history',[ZiliApiController::class,'jilli_get_bet_history']);
Route::post('/add_in_jilli_wallet ',[ZiliApiController::class,'add_in_jilli_wallet']);
Route::post('/update_main_wallet ',[ZiliApiController::class,'update_main_wallet']);
Route::post('/update_jilli_to_user_wallet ',[ZiliApiController::class,'update_jilli_to_user_wallet']);

///////akhilesh
Route::post('/deduct-bet-amount', [ZiliApiController::class, 'deduct_bet_amount']);

//////end akhilesh

Route::post('/get_jilli_wallet ',[ZiliApiController::class,'get_jilli_wallet']);
Route::post('/update_jilli_wallet ',[ZiliApiController::class,'update_jilli_wallet']);


Route::get('/test_get_user_info ',[ZiliApiController::class,'test_get_user_info']);
Route::get('/get-reseller-info/{manager_key?}',[ZiliApiController::class,'get_reseller_info']);


/// test Jilli Controller ////

Route::get('/end_user_register',[TestJilliController::class,'end_user_register']);
Route::get('/get_all_game_list',[TestJilliController::class,'get_all_game_list']);
Route::get('/get_game_url_gameid',[TestJilliController::class,'get_game_url_gameid']);
Route::get('/add_amount_to_user',[TestJilliController::class,'transfer_amount_to_user']);
Route::get('/get_jilli_transaction_details',[TestJilliController::class,'get_jilli_transaction_details']);
Route::get('/wallet_deduct_from_user',[TestJilliController::class,'wallet_deduct_from_user']);
Route::get('/get_bet_history',[TestJilliController::class,'get_bet_history']);
Route::get('/get_reseller_info',[TestJilliController::class,'get_reseller_info']);


/* Route::controller(SpribeApiController::class)->group(function () {
    Route::get('/get_reseller_info', 'get_reseller_info');
    Route::post('/get_spribe_game_urls','get_spribe_game_urls');
	//Route::get('/monthly_bonus','monthlyBonus');
}); */


//// Zili Api ///
    Route::post('/all_game_list',[ZiliApiController::class,'all_game_list']);


Route::controller(SpribeApiController::class)->group(function () {
    Route::get('/get_reseller_info', 'get_reseller_info');
    Route::post('/get_spribe_game_urls','get_spribe_game_urls');
	Route::post('/spribe_betting_history','spribe_betting_history');
	Route::post('/spribe_all_betting_history','spribe_all_betting_history');
	Route::post('/sprb/spribe/callback','handleCallback');
	Route::post('/spribe_user_register','spribe_user_register'); 
	Route::post('/spribe_transactons_details','spribe_transactons_details'); 
	Route::post('/scribe_deduct_from_wallet','scribe_deduct_from_wallet');
	Route::post('/get_spribe_wallet ','get_spribe_wallet');
	Route::post('/add_in_spribe_wallet ','add_in_spribe_wallet');
	Route::post('/update_spribe_wallet ','update_spribe_wallet');
	Route::post('/update_spribe_to_user_wallet ','update_spribe_to_user_wallet');

	
	//Route::get('/monthly_bonus','monthlyBonus');
});



