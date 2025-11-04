<?php

namespace App\Http\Controllers;

use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TransferController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'from_user_id' => 'required|integer|exists:users,id',
            'to_user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|gt:0',
            'comment' => 'sometimes|string|max:255',
        ]);

        try {
            $transactions = $this->balanceService->transfer(
                $request->from_user_id,
                $request->to_user_id,
                $request->amount,
                $request->comment
            );

            $fromBalance = $this->balanceService->getBalance($request->from_user_id);
            $toBalance = $this->balanceService->getBalance($request->to_user_id);

            return response()->json([
                'message' => 'Transfer successful',
                'transaction_id' => $transactions['out_transaction']->id,
                'from_user_balance' => $fromBalance,
                'to_user_balance' => $toBalance,
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
