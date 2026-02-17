<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Http;
use Illuminate\Console\Command;

class GetExchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-exchange-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the latest exchange rate from the API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseUrl = rtrim(config('services.coingecko.base_url'), '/');
        $apiKey = config('services.coingecko.api_key');
        $activeCurrencies = Currency::where('status', 1)->get()->keyBy('code');
        $currenciesCodes = $activeCurrencies->keys()->toArray();

        foreach ($currenciesCodes as $currencyCode) {
            $mainCurrency = $activeCurrencies->get($currencyCode);
            $otherCodes = Currency::where('status', 1)->whereNot('symbol', $mainCurrency->symbol)->pluck('code')->toArray();

            $url = $baseUrl . '/price?vs_currencies=' . strtolower($mainCurrency->symbol)
                    . '&x_cg_demo_api_key=' . $apiKey
                    . '&ids=' . implode(',', $otherCodes);

            $data = Http::get($url)->json();

            foreach ($data as $quoteCurrencyCode => $rate) {
                $quoteCurrency = $activeCurrencies->get($quoteCurrencyCode);
            
                if (!$quoteCurrency) {
                    $this->error('Currency not found: ' . $quoteCurrencyCode);
                    continue;
                }
            
                $rateValue = $rate[strtolower($mainCurrency->symbol)] ?? 0;
            
                // Save base -> quote (e.g. NGN -> BTC)
                ExchangeRate::updateOrCreate(
                    [
                        'base_currency_id' => $mainCurrency->id,
                        'quote_currency_id' => $quoteCurrency->id,
                    ],
                    [
                        'rate' => $rateValue,
                        'source' => 'coingecko',
                        'status' => 1,
                    ]
                );
            
                // Save quote -> base (e.g. BTC -> NGN) as the inverse
                $inverseRate = $rateValue > 0 ? 1 / $rateValue : 0;
            
                ExchangeRate::updateOrCreate(
                    [
                        'base_currency_id' => $quoteCurrency->id,
                        'quote_currency_id' => $mainCurrency->id,
                    ],
                    [
                        'rate' => $inverseRate,
                        'source' => 'coingecko',
                        'status' => 1,
                    ]
                );
            
                $this->info("Saved: {$mainCurrency->code} <-> {$quoteCurrency->code}");
            }
        }

        $this->info('Exchange rates updated successfully for all currencies');
    }
}
