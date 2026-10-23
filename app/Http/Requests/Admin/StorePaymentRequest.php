<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PaymentType::class)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99', 'decimal:0,2'],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:120'],
            'rate_to_bdt' => ['nullable', 'numeric', 'gt:0', 'max:100000'],
            'note' => ['nullable', 'string', 'max:2000'],
            'notify_student' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'paid_on.before_or_equal' => 'The payment date cannot be in the future.',
        ];
    }
}
