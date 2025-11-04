<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WithdrawTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_withdrawal()
    {
        $user = User::factory()->create();

        // Сначала пополняем баланс
        $user->transactions()->create([
            'type' => 'deposit',
            'amount' => 500.00
        ]);

        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => 200.00,
            'comment' => 'Покупка подписки'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'balance' => 300.00
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'withdraw',
            'amount' => -200.00,
            'comment' => 'Покупка подписки'
        ]);
    }

    public function test_insufficient_funds()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => 200.00
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'Insufficient funds'
            ]);
    }

    public function test_withdrawal_validation_errors()
    {
        $user = User::factory()->create();

        // Отрицательная сумма
        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => -100.00
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Нулевая сумма
        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => 0.00
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_withdrawal_exact_balance()
    {
        $user = User::factory()->create();

        $user->transactions()->create([
            'type' => 'deposit',
            'amount' => 300.00
        ]);

        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => 300.00
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'balance' => 0.00
            ]);
    }
}
