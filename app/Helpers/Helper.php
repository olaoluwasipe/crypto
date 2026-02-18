<?php

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
        return $date->format('Y-m-d H:i:s');
    }
}
