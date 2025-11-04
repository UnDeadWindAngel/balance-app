<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DepositTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_deposit()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 500.00,
            'comment' => 'Пополнение через карту'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'transaction_id',
                'balance'
            ])
            ->assertJson([
                'balance' => 500.00
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => 500.00,
            'comment' => 'Пополнение через карту'
        ]);
    }

    public function test_deposit_validation_errors()
    {
        // Несуществующий пользователь
        $response = $this->postJson('/api/deposit', [
            'user_id' => 999,
            'amount' => 100.00
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);

        // Отрицательная сумма
        $user = User::factory()->create();
        $response = $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => -100.00
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Нулевая сумма
        $response = $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 0.00
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Отсутствует сумма
        $response = $this->postJson('/api/deposit', [
            'user_id' => $user->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_multiple_deposits_correctly_calculate_balance()
    {
        $user = User::factory()->create();

        $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 300.00
        ]);

        $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 200.00
        ]);

        $response = $this->getJson("/api/balance/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'balance' => 500.00
            ]);
    }
}
