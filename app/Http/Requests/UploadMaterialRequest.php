<?php

namespace App\Http\Requests;

use App\Rules\SafeDocument;
use Illuminate\Foundation\Http\FormRequest;

class UploadMaterialRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'material' => ['required', 'file', new SafeDocument],
            'note' => ['nullable', 'string', 'max:3000'],
            'confirm_guidance' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'material.required' => 'Choose the file to upload.',
            'confirm_guidance.accepted' => 'Confirm that this file is guidance or feedback for the student to learn from, not a finished assignment.',
        ];
    }
}
