<?php

namespace App\Http\Requests;

use App\Enums\EnquiryType;
use App\Rules\WhatsAppNumber;
use App\Support\SiteContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['nullable', 'required_without:whatsapp_number', 'email:rfc', 'max:254'],
            'whatsapp_number' => ['nullable', 'required_without:email', 'string', 'max:25', new WhatsAppNumber],
            'country' => ['nullable', 'string', Rule::in(SiteContent::countriesOfStudy())],
            'subject' => ['nullable', 'string', 'max:100'],
            'enquiry_type' => ['required', Rule::enum(EnquiryType::class)],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required_without' => 'Give us an email address or a WhatsApp number so we can reply.',
            'whatsapp_number.required_without' => 'Give us a WhatsApp number or an email address so we can reply.',
            'website.prohibited' => 'Your message could not be sent.',
        ];
    }
}
