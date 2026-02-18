<?php

namespace App\Http\Controllers;

use App\ApiResponses;
use App\Contracts\v1\Wallet\WalletContract;
use App\Http\Requests\v1\Wallet\AddMoneyRequest;
use App\Http\Requests\v1\Wallet\WalletTransactionsFilterRequest;
use App\Http\Resources\v1\WalletTransactionResource;

class WalletController extends Controller
{
    use ApiResponses;

    protected $walletService;

    public function __construct()
    {
        $this->walletService = app(WalletContract::class);
    }

    public function addMoney(AddMoneyRequest $request)
    {
        try {
            $validated = $request->validated();
            $response = $this->walletService->addMoney($validated);

            return $this->successResponse(new WalletTransactionResource($response), 'Money added successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function transactions(WalletTransactionsFilterRequest $request)
    {
        try {
            $validated = $request->validated();
            $transactions = $this->walletService->transactions($validated);

            return $this->paginateResponse($transactions, 'Transactions retrieved successfully', 200, WalletTransactionResource::class);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
