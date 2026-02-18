<?php

namespace App\Http\Controllers;

use App\ApiResponses;
use App\Contracts\v1\Trade\TradeContract;
use App\Http\Requests\v1\Trade\BuyTradeRequest;
use App\Http\Requests\v1\Trade\SellTradeRequest;
use App\Http\Requests\v1\Trade\TradesFilterRequest;
use App\Http\Resources\v1\TradeResource;
use App\Models\Trade;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    use ApiResponses;

    protected $tradeService;

    public function __construct()
    {
        $this->tradeService = app(TradeContract::class);
    }

    public function buy(BuyTradeRequest $request)
    {
        try {
            $validated = $request->validated();
            $response = $this->tradeService->buy($validated);
            
            return $this->successResponse(new TradeResource($response), 'Trade successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function sell(SellTradeRequest $request)
    {
        try {
            $validated = $request->validated();
            $validated['currency'] = "ngn";
            $response = $this->tradeService->sell($validated);

            return $this->successResponse(new TradeResource($response), 'Trade successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function transactions(TradesFilterRequest $request)
    {
        try {
            $validated = $request->validated();
            $transactions = $this->tradeService->transactions($validated);
            return $this->paginateResponse($transactions, 
            'Transactions retrieved successfully', 200, TradeResource::class);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(Trade $trade)
    {
        try {
            $transaction = $this->tradeService->show($trade);
            return $this->successResponse(new TradeResource($transaction), 'Transaction retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
