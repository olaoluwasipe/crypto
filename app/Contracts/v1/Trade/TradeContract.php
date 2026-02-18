<?php

namespace App\Contracts\v1\Trade;

use App\Models\Trade;

interface TradeContract
{
    public function buy(array $data);
    public function sell(array $data);
    public function transactions(array $filters = []);
    public function show(Trade $trade);
}
