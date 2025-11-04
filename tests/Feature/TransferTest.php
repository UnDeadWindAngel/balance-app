<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_transfer()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Пополняем баланс первого пользователя
        $user1->transactions()->create([
            'type' => 'deposit',
            'amount' => 500.00
        ]);

        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $user1->id,
            'to_user_id' => $user2->id,
            'amount' => 150.00,
            'comment' => 'Перевод другу'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'from_user_balance' => 350.00,
                'to_user_balance' => 150.00
            ]);

        // Проверяем транзакции списания
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user1->id,
            'type' => 'transfer_out',
            'amount' => -150.00,
            'related_user_id' => $user2->id,
            'comment' => 'Перевод другу'
        ]);

        // Проверяем транзакции зачисления
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user2->id,
            'type' => 'transfer_in',
            'amount' => 150.00,
            'related_user_id' => $user1->id,
            'comment' => 'Перевод другу'
        ]);
    }

    public function test_transfer_insufficient_funds()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->transactions()->create([
            'type' => 'deposit',
            'amount' => 100.00
        ]);

        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $user1->id,
            'to_user_id' => $user2->id,
            'amount' => 200.00
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'Insufficient funds'
            ]);
    }

    public function test_transfer_to_same_user()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $user->id,
            'to_user_id' => $user->id,
            'amount' => 100.00
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Cannot transfer to same user'
            ]);
    }

    public function test_transfer_validation_errors()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Отрицательная сумма
        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $user1->id,
            'to_user_id' => $user2->id,
            'amount' => -100.00
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Нулевая сумма
        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $user1->id,
            'to_user_id' => $user2->id,
            'amount' => 0.00
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Несуществующий пользователь
        $response = $this->postJson('/api/transfer', [
            'from_user_id' => 999,
            'to_user_id' => $user2->id,
            'amount' => 100.00
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['from_user_id']);
    }

    public function test_transfer_complete_balance_flow()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Начальные пополнения
        $user1->transactions()->create(['type' => 'deposit', 'amount' => 1000.00]);
        $user2->transactions()->create(['type' => 'deposit', 'amount' => 500.00]);

        // Перевод от user1 к user2
        $this->postJson('/api/transfer', [
            'from_user_id' => $user1->id,
            'to_user_id' => $user2->id,
            'amount' => 300.00
        ]);

        // Перевод от user2 к user3
        $this->postJson('/api/transfer', [
            'from_user_id' => $user2->id,
            'to_user_id' => $user3->id,
            'amount' => 200.00
        ]);

        // Проверяем финальные балансы
        $this->getJson("/api/balance/{$user1->id}")
            ->assertJson(['balance' => 700.00]);

        $this->getJson("/api/balance/{$user2->id}")
            ->assertJson(['balance' => 600.00]); // 500 + 300 - 200

        $this->getJson("/api/balance/{$user3->id}")
            ->assertJson(['balance' => 200.00]);
    }
}
