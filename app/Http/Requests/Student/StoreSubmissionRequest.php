<?php

namespace App\Http\Requests\Student;

use App\Rules\SafeDocument;
use App\Rules\WhatsAppNumber;
use App\Support\SiteContent;
use App\Support\WhatsAppNumber as WhatsAppNumberSupport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'assignment_name' => ['required', 'string', 'min:2', 'max:200'],
            'module_code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9][A-Za-z0-9 .\/_-]*$/'],
            'brief' => ['required', 'file', new SafeDocument],
            'supplementary' => ['nullable', 'array', 'max:'.config('zytecs.uploads.max_supplementary_files')],
            'supplementary.*' => ['file', new SafeDocument],
            'whatsapp_number' => ['required', 'string', 'max:25', new WhatsAppNumber],
            'confirm_whatsapp' => ['accepted'],
            'terms' => ['accepted'],
            'institution' => ['nullable', 'string', 'max:150'],
            'country' => ['nullable', 'string', Rule::in(SiteContent::countriesOfStudy())],
            'subject' => ['nullable', 'string', Rule::in(array_values(SiteContent::portfolioAreas()))],
            'assessment_type' => ['nullable', 'string', Rule::in(SiteContent::assessmentTypes())],
            'deadline' => ['nullable', 'date', 'after_or_equal:today', 'before:+3 years'],
            'description' => ['nullable', 'string', 'max:5000'],
            'client_token' => ['required', 'uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'assignment_name.required' => 'Enter the assignment or project name.',
            'module_code.required' => 'Enter the module code, for example MGMT402.',
            'module_code.regex' => 'The module code may only contain letters, numbers, spaces, dots, slashes, hyphens and underscores.',
            'brief.required' => 'Attach your assignment brief.',
            'supplementary.max' => 'You can attach up to :max supporting files.',
            'confirm_whatsapp.accepted' => 'Please confirm that this WhatsApp number is correct.',
            'terms.accepted' => 'Please confirm that you have read the document-handling notice.',
            'deadline.after_or_equal' => 'The deadline cannot be in the past.',
        ];
    }

    /**
     * @return array{assignment_name: string, module_code: string, institution: ?string, country: ?string, subject: ?string, assessment_type: ?string, deadline: ?string, description: ?string, client_token: string}
     */
    public function submissionDetails(): array
    {
        /** @var array{assignment_name: string, module_code: string, institution: ?string, country: ?string, subject: ?string, assessment_type: ?string, deadline: ?string, description: ?string, client_token: string} */
        return $this->safe()->only([
            'assignment_name', 'module_code', 'institution', 'country', 'subject',
            'assessment_type', 'deadline', 'description', 'client_token',
        ]) + array_fill_keys(['institution', 'country', 'subject', 'assessment_type', 'deadline', 'description'], null);
    }

    public function normalizedWhatsApp(): string
    {
        return (string) WhatsAppNumberSupport::normalize($this->string('whatsapp_number')->toString());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'assignment_name' => trim((string) $this->input('assignment_name')),
            'module_code' => mb_strtoupper(trim((string) $this->input('module_code'))),
        ]);
    }
}
