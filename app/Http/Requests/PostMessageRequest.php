<?php

namespace App\Http\Requests;

use App\Rules\SafeDocument;
use Illuminate\Foundation\Http\FormRequest;

class PostMessageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['nullable', 'required_without:attachment', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', new SafeDocument],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required_without' => 'Write a message or attach a file.',
        ];
    }
}
