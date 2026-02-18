<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
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
            'wallet' => formatCurrency($this->resource->wallet->currency),
            'amount' => formatMoney($this->amount, $this->resource->wallet->currency),
            'status' => $this->status,
            'created_at' => formatDate($this->created_at),
        ];
    }
}
