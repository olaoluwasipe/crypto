<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'currency' => formatCurrency($this->currency),
            'balance' => formatMoney($this->balance, $this->currency),
            'created_at' => formatDate($this->created_at),
            'last_transaction' => new WalletTransactionResource($this->transactions()->latest()->first()),
            'number_of_transactions' => $this->transactions()->count(),
            'total_debit' => formatMoney($this->transactions()->where('type', 'debit')->sum('amount'), $this->currency),
            'total_credit' => formatMoney($this->transactions()->where('type', 'credit')->sum('amount'), $this->currency),
        ];
    }
}
