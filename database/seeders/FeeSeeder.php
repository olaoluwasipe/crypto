<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Fee;

class FeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fees = [
            [
                'type' => 'buy',
                'currency_id' => 3,
                'amount' => 0.001,
                'percentage' => 0.1,
                'fixed_amount' => 0,
                'min_amount' => 1,
                'max_amount' => 1000000,
                'status' => 1,
            ],
            [
                'type' => 'sell',
                'currency_id' => 3,
                'amount' => 0.001,
                'percentage' => 0.1,
                'fixed_amount' => 0,
                'min_amount' => 1,
                'max_amount' => 1000000,
                'status' => 1,
            ],
            [
                'type' => 'buy',
                'currency_id' => 4,
                'amount' => 0.001,
                'percentage' => 0.1,
                'fixed_amount' => 0,
                'min_amount' => 1,
                'max_amount' => 1000000,
                'status' => 1,
            ],
            [
                'type' => 'sell',
                'currency_id' => 4,
                'amount' => 0.001,
                'percentage' => 0.1,
                'fixed_amount' => 0,
                'min_amount' => 1,
                'max_amount' => null,
                'status' => 1,
            ],
        ];
        foreach ($fees as $fee) {
            Fee::updateOrCreate(['currency_id' => $fee['currency_id'], 'type' => $fee['type']], $fee);
        }
    }
}
