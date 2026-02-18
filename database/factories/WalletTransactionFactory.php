<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 10, 10000);
        $prevBalance = fake()->randomFloat(2, 0, 100000);
        $type = fake()->randomElement([WalletTransaction::TYPE_CREDIT, WalletTransaction::TYPE_DEBIT]);
        $newBalance = $type === WalletTransaction::TYPE_CREDIT
            ? $prevBalance + $amount
            : $prevBalance - $amount;

        return [
            'wallet_id' => Wallet::factory(),
            'type' => $type,
            'reference' => fake()->unique()->uuid(),
            'description' => fake()->sentence(),
            'metadata' => [],
            'amount' => $amount,
            'status' => WalletTransaction::STATUS_COMPLETED,
            'idempotency_key' => fake()->unique()->uuid(),
            'prev_balance' => $prevBalance,
            'new_balance' => $newBalance,
        ];
    }
}
