<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
if (config('app.debug')) {
    Route::get('/heartbeat', 'App\Http\Controllers\visms@Heartbeat');
    Route::get('/product_inquiry', 'App\Http\Controllers\visms@ProductInquiry');
    Route::get('/checkbalance', 'App\Http\Controllers\visms@CheckBalance');
    Route::get('/purchase_item_rule_id', 'App\Http\Controllers\visms@PurchaseItemRuleID');
    Route::get('/purchase_gift', 'App\Http\Controllers\visms@PurchaseGift');

}
Route::post('/heartbeat', 'App\Http\Controllers\visms@Heartbeat');
Route::post('/product_inquiry', 'App\Http\Controllers\visms@ProductInquiry');
Route::post('/checkbalance', 'App\Http\Controllers\visms@CheckBalance');
Route::post('/purchase_item_rule_id', 'App\Http\Controllers\visms@PurchaseItemRuleID');
Route::post('/purchase_gift', 'App\Http\Controllers\visms@PurchaseGift');




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::any('{path}', function(Request $request) {
    return response([
        "service_code" => $request->service_code,
        "Result" => -96,
        "msg" => 'ERD_VISMS_NOT_FOUND',
    ], 404);
})->where('path', '.*');
