<?php

namespace App\Http\Requests\v1\Trade;

use App\Http\Requests\v1\BaseRequest;

class TradesFilterRequest extends BaseRequest
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
            'status' => 'nullable|in:pending,completed,cancelled',
            'type' => 'nullable|in:buy,sell',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be either pending, completed or cancelled',
            'type.in' => 'Type must be either buy or sell',
            'start_date.date' => 'Start date must be a valid date',
            'end_date.date' => 'End date must be a valid date',
            'per_page.integer' => 'Per page must be an integer',
            'per_page.min' => 'Per page must be at least 1',
            'per_page.max' => 'Per page must be at most 100',
        ];
    }
}
