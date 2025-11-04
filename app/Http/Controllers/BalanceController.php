<?php

namespace App\Http\Controllers;

use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BalanceController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    public function show(int $userId): JsonResponse
    {
        try {
            $balance = $this->balanceService->getBalance($userId);

            return response()->json([
                'user_id' => $userId,
                'balance' => $balance,
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
