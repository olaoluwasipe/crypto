<?php

namespace App\Contracts\v1\Trade;

interface TradeContract
{
    public function buy(array $data);
    public function sell(array $data);
}
