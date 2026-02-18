<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'reference' => $this->reference,
            'type' => $this->type,
            'status' => $this->status_text,
            'amount_paid' => formatMoney($this->price, $this->baseCurrency),
            'rate' => formatMoney($this->rate, $this->baseCurrency) . ' per ' . formatMoney(1, $this->quoteCurrency),
            'base_currency' => formatCurrency($this->baseCurrency),
            'quote_currency' => formatCurrency($this->quoteCurrency),
            'debit_transaction' => new WalletTransactionResource($this->debitTransaction),
            'credit_transaction' => new WalletTransactionResource($this->creditTransaction),
            'executed_at' => formatDate($this->executed_at),
            'created_at' => formatDate($this->created_at),
        ];
    }
}
