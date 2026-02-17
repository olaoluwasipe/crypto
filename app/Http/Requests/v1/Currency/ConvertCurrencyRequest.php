<?php

namespace App\Http\Requests\v1\Currency;

use App\Http\Requests\v1\BaseRequest;

class ConvertCurrencyRequest extends BaseRequest
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
            'base_currency' => 'required|string',
            'quote_currency' => 'required|string',
            'amount' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'base_currency.required' => 'Base currency is required',
            'base_currency.string' => 'Base currency must be a string',
            'quote_currency.required' => 'Quote currency is required',
            'quote_currency.string' => 'Quote currency must be a string',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
        ];
    }
}
