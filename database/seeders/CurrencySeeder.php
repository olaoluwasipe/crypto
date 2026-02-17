<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'Nigerian Naira',
                'symbol' => 'ngn',
                'code' => 'ngn',
                'precision' => 2,
                'type' => 'fiat',
                'min_trade_amount' => 100,
                'max_trade_amount' => 1000000,
                'logo' => 'https://example.com/logo.png',
                'status' => 1,
            ],
            [
                'name' => 'US Dollar',
                'symbol' => 'usd',
                'code' => 'usd',
                'precision' => 2,
                'type' => 'fiat',
                'min_trade_amount' => 1,
                'max_trade_amount' => 1000000,
                'logo' => 'https://example.com/logo.png',
                'status' => 1,
            ],
            [
                'name' => 'Bitcoin',
                'symbol' => 'btc',
                'code' => 'bitcoin',
                'precision' => 8,
                'type' => 'crypto',
                'min_trade_amount' => 0.00000001,
                'max_trade_amount' => 1000000,
                'logo' => 'https://example.com/logo.png',
                'status' => 1,
            ],
            [
                'name' => 'Ethereum',
                'symbol' => 'eth',
                'code' => 'ethereum',
                'precision' => 18,
                'type' => 'crypto',
                'min_trade_amount' => 0.00000001,
                'max_trade_amount' => 1000000,
                'logo' => 'https://example.com/logo.png',
                'status' => 1,
            ],
            [
                'name' => 'USDT Tether',
                'symbol' => 'usdt',
                'code' => 'tether',
                'precision' => 6,
                'type' => 'crypto',
                'min_trade_amount' => 0.000001,
                'max_trade_amount' => 1000000,
                'logo' => 'https://example.com/logo.png',
                'status' => 1,
            ],
            // [
            //     'name' => 'Tether',
            //     'symbol' => 'trx',
            //     'code' => 'tether',
            //     'precision' => 6,
            //     'type' => 'crypto',
            //     'min_trade_amount' => 0.000001,
            //     'logo' => 'https://example.com/logo.png',
            //     'status' => 1,
            // ],
        ];
        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
