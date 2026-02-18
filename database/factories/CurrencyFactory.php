<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->currencyCode(),
            'symbol' => strtolower(fake()->unique()->currencyCode()),
            'code' => strtolower(fake()->unique()->currencyCode()),
            'precision' => 2,
            'type' => fake()->randomElement(['fiat', 'crypto']),
            'min_trade_amount' => 1,
            'max_trade_amount' => 1000000,
            'logo' => 'https://example.com/logo.png',
            'status' => 1,
        ];
    }
}
