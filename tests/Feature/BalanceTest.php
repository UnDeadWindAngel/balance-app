<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_user_balance()
    {
        $user = User::factory()->create();

        // Создаем транзакции для пользователя
        $user->transactions()->create([
            'type' => 'deposit',
            'amount' => 500.00,
            'comment' => 'Initial deposit'
        ]);

        $response = $this->getJson("/api/balance/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $user->id,
                'balance' => 500.00
            ]);
    }

    public function test_returns_404_for_nonexistent_user()
    {
        $response = $this->getJson('/api/balance/999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'User not found'
            ]);
    }

    public function test_balance_is_zero_for_new_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/balance/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $user->id,
                'balance' => 0.00
            ]);
    }
}
