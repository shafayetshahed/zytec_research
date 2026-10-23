<?php

namespace App\Http\Requests\Auth;

use App\Rules\WhatsAppNumber;
use App\Support\WhatsAppNumber as WhatsAppNumberSupport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'email' => ['required', 'string', 'email:rfc', 'max:254', 'unique:users,email'],
            'whatsapp_number' => ['required', 'string', 'max:25', new WhatsAppNumber],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'An account already exists for this email address. Try signing in or resetting your password.',
            'terms.accepted' => 'Please confirm that you accept the Terms of Service and Privacy Policy.',
        ];
    }

    public function normalizedWhatsApp(): string
    {
        return (string) WhatsAppNumberSupport::normalize($this->string('whatsapp_number')->toString());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }
}
