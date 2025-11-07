<?php

namespace App\Http\Controllers;

use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WithdrawController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        Log::info('=== WITHDRAW CONTROLLER START ===');

        // Принудительно разбираем raw content
        $rawContent = $request->getContent();
        Log::info('Raw content: ' . $rawContent);

        $data = [];
        if (!empty($rawContent)) {
            $data = $this->parseWeirdJson($rawContent);

            if (!empty($data)) {
                $request->merge($data);
                Log::info('Merged data: ', $data);
            } else {
                Log::error('Failed to parse content', ['content' => $rawContent]);
            }
        }

        Log::info('Request data after processing: ', $request->all());
        Log::info('=== WITHDRAW CONTROLLER END ===');

        // Временная жесткая валидация
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|gt:0',
            'comment' => 'sometimes|string|max:255',
        ]);

        try {
            $transaction = $this->balanceService->withdraw(
                $validatedData['user_id'],
                $validatedData['amount'],
                $validatedData['comment'] ?? ''
            );

            $balance = $this->balanceService->getBalance($validatedData['user_id']);

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
    private function parseWeirdJson(string $content): array
    {
        // Сначала пробуем стандартный JSON
        $data = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }

        // Если это наш "странный" формат {key:value,key:value}
        if (preg_match('/^{[^}]+}$/', $content)) {
            // Убираем фигурные скобки
            $content = trim($content, '{}');

            // Разбиваем на пары ключ-значение
            $pairs = explode(',', $content);
            $result = [];

            foreach ($pairs as $pair) {
                $parts = explode(':', $pair, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);

                    // Преобразуем числовые значения
                    if (is_numeric($value)) {
                        $value = (float)$value;
                    }

                    $result[$key] = $value;
                }
            }

            if (!empty($result)) {
                return $result;
            }
        }

        return [];
    }
}
