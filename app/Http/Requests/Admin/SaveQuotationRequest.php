<?php

namespace App\Http\Requests\Admin;

use App\Enums\Currency;
use App\Support\SiteContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveQuotationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'currency' => ['required', Rule::enum(Currency::class)],
            'total_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99', 'decimal:0,2'],
            'deposit_amount' => ['required', 'numeric', 'min:0', 'lte:total_amount', 'decimal:0,2'],
            'work_due_date' => ['nullable', 'date'],
            'country' => ['nullable', 'string', Rule::in(SiteContent::countriesOfStudy())],
            'message' => ['nullable', 'string', 'max:3000'],
            'action' => ['required', 'in:save,send'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'deposit_amount.lte' => 'The deposit cannot be more than the total price.',
        ];
    }
}
