<?php

namespace App\Services\v1\Wallet;

use App\Contracts\v1\Wallet\FeeContract;
use App\Models\Currency;
use App\Models\Fee;

class FeeService implements FeeContract
{
    public function calculateBuyFee(Currency $currency, $amount)
    {
        return $this->calculateFee($currency, $amount, 'buy');
    }

    public function calculateSellFee(Currency $currency, $amount)
    {
        return $this->calculateFee($currency, $amount, 'sell');
    }

    public function calculateFee(Currency $currency, $amount, $type)
    {
        $fee = Fee::where('currency_id', $currency->id)->where('type', $type)->first();
        
        if (!$fee) {
            return 0;
        }

        // Start with percentage or fixed
        if ($fee->percentage) {
            $calculatedFee = $amount * $fee->percentage / 100;
        } elseif ($fee->fixed_amount) {
            $calculatedFee = $fee->fixed_amount;
        } else {
            $calculatedFee = 0;
        }

        // Then apply min/max caps on top
        if ($fee->min_amount && $calculatedFee < $fee->min_amount) {
            $calculatedFee = $fee->min_amount;
        }

        if ($fee->max_amount && $calculatedFee > $fee->max_amount) {
            $calculatedFee = $fee->max_amount;
        }

        return $calculatedFee;
    }
}
