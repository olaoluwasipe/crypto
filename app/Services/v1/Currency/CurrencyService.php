<?php

namespace App\Services\v1\Currency;

use App\Contracts\v1\Currency\CurrencyContract;
use App\Models\Currency;
use App\Models\ExchangeRate;

class CurrencyService implements CurrencyContract
{
    public function getAllRates() {
        $rates = ExchangeRate::with('baseCurrency', 'quoteCurrency')->where('status', 1)->get();
        return $rates;
    }
    
    public function getRate(Currency $currency, Currency $quoteCurrency)
    {
        $rate = ExchangeRate::where('base_currency_id', $currency->id)->where('quote_currency_id', $quoteCurrency->id)->firstOrFail();
        return $rate;
    }

    public function convert(Currency $currency, Currency $quoteCurrency, $amount)
    {
        $rate = $this->getRate($currency, $quoteCurrency);
        return $amount * $rate->rate;
    }

    public function convertToCurrency(Currency $currency, Currency $quoteCurrency, $amount)
    {
        $rate = $this->getRate($currency, $quoteCurrency);
        return $amount / $rate->rate;
    }
}
