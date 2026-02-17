<?php

namespace App\Contracts\v1\Wallet;

use App\Models\Currency;

interface FeeContract
{
    public function calculateBuyFee(Currency $currency, $amount);
    public function calculateSellFee(Currency $currency, $amount);
    public function calculateFee(Currency $currency, $amount, $type);
}
