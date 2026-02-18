<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Trade;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trade>
 */
class TradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        $baseCurrency = Currency::firstOrCreate(
            ['symbol' => 'ngn'],
            [
                'name' => 'Nigerian Naira',
                'code' => 'ngn',
                'precision' => 2,
                'type' => 'fiat',
                'min_trade_amount' => 100,
                'max_trade_amount' => 1000000,
                'logo' => 'https://example.com/logo.png',
                'status' => 1,
            ]
        );
        $quoteCurrency = Currency::firstOrCreate(
            ['symbol' => 'btc'],
            [
                'name' => 'Bitcoin',
                'code' => 'bitcoin',
                'precision' => 8,
                'type' => 'crypto',
                'min_trade_amount' => 0.00000001,
                'max_trade_amount' => 1000000,
                'logo' => 'https://example.com/logo.png',
                'status' => 1,
            ]
        );

        $baseAmount = fake()->randomFloat(2, 100, 10000);
        $rate = fake()->randomFloat(8, 0.00000001, 0.001);
        $quoteAmount = $baseAmount * $rate;
        $fee = $baseAmount * 0.01;

        $creditTransaction = WalletTransaction::factory()->create([
            'type' => WalletTransaction::TYPE_CREDIT,
        ]);

        $debitTransaction = WalletTransaction::factory()->create([
            'type' => WalletTransaction::TYPE_DEBIT,
        ]);

        return [
            'user_id' => $user->id,
            'reference' => fake()->unique()->uuid(),
            'base_currency_id' => $baseCurrency->id,
            'quote_currency_id' => $quoteCurrency->id,
            'base_amount' => $baseAmount,
            'quote_amount' => $quoteAmount,
            'price' => $baseAmount / $quoteAmount,
            'fee' => $fee,
            'rate' => $rate,
            'fee_currency_id' => $baseCurrency->id,
            'type' => fake()->randomElement(['buy', 'sell']),
            'status' => Trade::STATUS_COMPLETED,
            'executed_at' => now(),
            'credit_transaction_id' => $creditTransaction->id,
            'debit_transaction_id' => $debitTransaction->id,
        ];
    }
}
