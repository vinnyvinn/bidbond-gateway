<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::resource('core-banking','SoapController');

Route::post('safaricom/c2b/validation/callback/{secret}', 'MPESAController@c2bValidation');

Route::post('safaricom/c2b/confirmation/callback/{secret}', 'MPESAController@c2bConfirmation');

Route::post('safaricom/trx-status/timeout/callback/{secret}', 'MPESAController@trxStatusTimeout');

Route::post('safaricom/trx-status/confirmation/callback/{secret}', 'MPESAController@trxStatusConfirmation');

Route::post('safaricom/stk/confirmation/callback/{secret}', 'MPESAController@stkConfirmation');

Route::get('user_details/{phone}/{id_number}', 'UserController@usersearch')->middleware('services.access');
Route::post('agent/restore-limit', 'AgentController@restoreLimit')->middleware('services.access');
Route::post('agent/usage', 'AgentController@increaseBidUsage')->middleware('services.access');

Route::get('create-account/{company}', 'CompanyController@createAccount');
//Route::post('conversion-rate')->middleware('services.access');

if(config('app.env') !== 'production') {
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
}
