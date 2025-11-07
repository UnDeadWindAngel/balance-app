<?php

use App\Http\Controllers\BalanceController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WithdrawController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/deposit', [DepositController::class, 'store']);
Route::post('/withdraw', [WithdrawController::class, 'store']);
Route::post('/transfer', [TransferController::class, 'store']);
Route::get('/balance/{user_id}', [BalanceController::class, 'show']);

Route::post('/debug-deposit', function (Request $request) {
    Log::info('Debug Deposit Request', [
        'headers' => $request->headers->all(),
        'content_type' => $request->header('Content-Type'),
        'all_data' => $request->all(),
        'json_data' => $request->json()->all(),
        'raw_content' => $request->getContent(),
        'user_id' => $request->user_id,
        'amount' => $request->amount,
        'isJson' => $request->isJson()
    ]);

    return response()->json([
        'content_type' => $request->header('Content-Type'),
        'all_data' => $request->all(),
        'raw_content' => $request->getContent(),
        'user_id_received' => $request->user_id,
        'amount_received' => $request->amount,
        'is_json' => $request->isJson()
    ]);
});
