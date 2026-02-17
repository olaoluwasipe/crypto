<?php

namespace App\Contracts\v1\Currency;

use App\Models\Currency;

interface CurrencyContract
{
    public function getAllRates();
    public function getRate(Currency $currency, Currency $quoteCurrency);
    public function convert(Currency $currency, Currency $quoteCurrency, $amount);
    public function convertToCurrency(Currency $currency, Currency $quoteCurrency, $amount);
}
