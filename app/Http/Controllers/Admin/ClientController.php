<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\WhatsAppNumber;
use App\Services\AuditLogger;
use App\Support\SiteContent;
use App\Support\WhatsAppNumber as WhatsAppNumberSupport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Client profiles for people who order by WhatsApp, phone or email. They
 * share the student table, so one person's portal and off-site orders sit
 * together. With an email address, a client can later claim portal access
 * through "Forgot password" and email verification.
 */
class ClientController extends Controller
{
    public function create(): View
    {
        return view('admin.clients.form', ['client' => null, 'countries' => SiteContent::countriesOfStudy()]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $this->validated($request);

        $client = new User;
        $client->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'whatsapp_number' => $data['whatsapp_number'],
            'country' => $data['country'],
            // Unusable until the client resets it themselves.
            'password' => Str::random(64),
        ]);
        $client->role = UserRole::Student;
        $client->status = AccountStatus::Active;
        $client->created_by = $request->user()->id;
        $client->save();

        $audit->record('client.created', $client);

        return redirect()->route('admin.students.show', $client)->with('status', 'Client added. You can now add orders for them.');
    }

    public function edit(User $student): View
    {
        abort_unless($student->isStudent(), 404);

        return view('admin.clients.form', ['client' => $student, 'countries' => SiteContent::countriesOfStudy()]);
    }

    public function update(Request $request, User $student, AuditLogger $audit): RedirectResponse
    {
        abort_unless($student->isStudent(), 404);

        $data = $this->validated($request, $student);

        $student->name = $data['name'];
        $student->whatsapp_number = $data['whatsapp_number'];
        $student->country = $data['country'];

        // A verified email belongs to the student; only unverified or missing addresses can be set here.
        if ($student->email_verified_at === null) {
            $student->email = $data['email'];
        }

        $student->save();
        $audit->record('client.updated', $student);

        return redirect()->route('admin.students.show', $student)->with('status', 'Client details saved.');
    }

    /**
     * @return array{name: string, email: ?string, whatsapp_number: ?string, country: ?string}
     */
    private function validated(Request $request, ?User $existing = null): array
    {
        $request->merge(['email' => filled($request->input('email')) ? mb_strtolower(trim((string) $request->input('email'))) : null]);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:254', Rule::unique('users', 'email')->ignore($existing?->id)],
            'whatsapp_number' => ['nullable', 'string', 'max:25', new WhatsAppNumber],
            'country' => ['nullable', 'string', Rule::in(SiteContent::countriesOfStudy())],
        ], [
            'email.unique' => 'Another client or student already uses this email. Search for them on the Students page instead.',
        ]);

        return [
            'name' => trim($data['name']),
            'email' => $data['email'] ?? null,
            'whatsapp_number' => WhatsAppNumberSupport::normalize($data['whatsapp_number'] ?? null),
            'country' => $data['country'] ?? null,
        ];
    }
}
