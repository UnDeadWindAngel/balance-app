<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BalanceService
{
    /**
     * Пополнение баланса
     */
    public function deposit(int $userId, float $amount, string $comment = null): Transaction
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::find($userId);
            if (!$user) {
                throw new InvalidArgumentException('User not found', 404);
            }

            if ($amount <= 0) {
                throw new InvalidArgumentException('Amount must be positive', 422);
            }

            $transaction = Transaction::create([
                'user_id' => $userId,
                'type' => Transaction::TYPE_DEPOSIT,
                'amount' => $amount,
                'comment' => $comment,
            ]);

            return $transaction;
        });
    }

    /**
     * Списание средств
     */
    public function withdraw(int $userId, float $amount, string $comment = null): Transaction
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::find($userId);
            if (!$user) {
                throw new InvalidArgumentException('User not found', 404);
            }

            if ($amount <= 0) {
                throw new InvalidArgumentException('Amount must be positive', 422);
            }

            $balance = $user->balance;
            if ($balance < $amount) {
                throw new InvalidArgumentException('Insufficient funds', 409);
            }

            $transaction = Transaction::create([
                'user_id' => $userId,
                'type' => Transaction::TYPE_WITHDRAW,
                'amount' => -$amount,
                'comment' => $comment,
            ]);

            return $transaction;
        });
    }

    /**
     * Перевод между пользователями
     */
    public function transfer(int $fromUserId, int $toUserId, float $amount, string $comment = null): array
    {
        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $comment) {
            if ($fromUserId === $toUserId) {
                throw new InvalidArgumentException('Cannot transfer to same user', 422);
            }

            $fromUser = User::find($fromUserId);
            $toUser = User::find($toUserId);

            if (!$fromUser || !$toUser) {
                throw new InvalidArgumentException('User not found', 404);
            }

            if ($amount <= 0) {
                throw new InvalidArgumentException('Amount must be positive', 422);
            }

            $fromUserBalance = $fromUser->balance;
            if ($fromUserBalance < $amount) {
                throw new InvalidArgumentException('Insufficient funds', 409);
            }

            $outTransaction = Transaction::create([
                'user_id' => $fromUserId,
                'type' => Transaction::TYPE_TRANSFER_OUT,
                'amount' => -$amount,
                'comment' => $comment,
                'related_user_id' => $toUserId,
            ]);

            $inTransaction = Transaction::create([
                'user_id' => $toUserId,
                'type' => Transaction::TYPE_TRANSFER_IN,
                'amount' => $amount,
                'comment' => $comment,
                'related_user_id' => $fromUserId,
            ]);

            return [
                'out_transaction' => $outTransaction,
                'in_transaction' => $inTransaction,
            ];
        });
    }

    /**
     * Получение баланса пользователя
     */
    public function getBalance(int $userId): float
    {
        $user = User::find($userId);
        if (!$user) {
            throw new InvalidArgumentException('User not found', 404);
        }

        return $user->balance;
    }
}
