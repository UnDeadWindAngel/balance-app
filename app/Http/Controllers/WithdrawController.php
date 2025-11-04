<?php

namespace App\Http\Controllers;

use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class WithdrawController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|gt:0',
            'comment' => 'sometimes|string|max:255',
        ]);

        try {
            $transaction = $this->balanceService->withdraw(
                $request->user_id,
                $request->amount,
                $request->comment
            );

            $balance = $this->balanceService->getBalance($request->user_id);

            return response()->json([
                'message' => 'Withdrawal successful',
                'transaction_id' => $transaction->id,
                'balance' => $balance,
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
