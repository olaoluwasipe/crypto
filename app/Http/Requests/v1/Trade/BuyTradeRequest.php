<?php

namespace App\Http\Requests\v1\Trade;

use App\Http\Requests\v1\BaseRequest;

class BuyTradeRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'wallet' => 'required|string|exists:currencies,symbol',
            'currency' => 'required|string|exists:currencies,symbol',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be greater than 0',
            'wallet.required' => 'Wallet is required',
            'wallet.string' => 'Wallet must be a string',
            'wallet.exists' => 'Wallet does not exist on this platform',
            'currency.required' => 'Currency is required',
            'currency.string' => 'Currency must be a string',
            'currency.exists' => 'Currency does not exist on this platform',
        ];
    }
}
