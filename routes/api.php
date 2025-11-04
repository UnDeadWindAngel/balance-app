<?php

use App\Http\Controllers\BalanceController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WithdrawController;
use Illuminate\Support\Facades\Route;

Route::post('/deposit', [DepositController::class, 'store']);
Route::post('/withdraw', [WithdrawController::class, 'store']);
Route::post('/transfer', [TransferController::class, 'store']);
Route::get('/balance/{user_id}', [BalanceController::class, 'show']);
