<?php

use App\Models\Currency;
use App\Models\Wallet;
use Carbon\Carbon;

if (! function_exists('formatMoney')) {
    function formatMoney($amount, $currency) {
        return formatCurrency($currency) . number_format($amount, $currency?->precision ?? 2);
    }
}
if (! function_exists('formatCurrency')) {
    function formatCurrency($currency) {
        return strtoupper($currency?->symbol ?? 'NGN') . ' ';
    }
}
if (! function_exists('formatDate')) {
    function formatDate($date) {
        $formatDate = Carbon::parse($date);
        return $formatDate->format('Y-m-d H:i:s');
    }
}
if (! function_exists('getCurrencyBySymbol')) {
    function getCurrencyBySymbol($symbol) {
        return Currency::where('symbol', strtolower($symbol))->first();
    }
}
if (! function_exists('getWalletBySymbol')) {
    function getWalletBySymbol($symbol) {
        return Wallet::where('currency_id', getCurrencyBySymbol($symbol)->id)->first();
    }
}
if (! function_exists('getWalletByUserAndCurrency')) {
    function getWalletByUserAndCurrency($user, $currency) {
        return Wallet::where('user_id', $user->id)->where('currency_id', $currency->id)->first();
    }
}
if (! function_exists('getWalletByUserAndCurrencySymbol')) {
    function getWalletByUserAndCurrencySymbol($user, $symbol) {
        return getWalletByUserAndCurrency($user, getCurrencyBySymbol($symbol));
    }
}