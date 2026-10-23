<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Enums\DeliverableStatus;
use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Mail\TeamInvitation;
use App\Models\Deliverable;
use App\Models\User;
use App\Rules\WhatsAppNumber;
use App\Services\AuditLogger;
use App\Support\WhatsAppNumber as WhatsAppNumberSupport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * Team members (your writers/experts) who prepare guidance and feedback
 * materials. They sign in to the Team portal and only see orders assigned
 * to them, without any student contact details.
 */
class TeamController extends Controller
{
    public function index(): View
    {
        $finalStatuses = array_filter(SubmissionStatus::cases(), fn (SubmissionStatus $s) => $s->isFinal());

        return view('admin.team.index', [
            'members' => User::where('role', UserRole::Expert)
                ->withCount([
                    'assignedSubmissions as open_orders_count' => fn (Builder $q) => $q->whereNotIn('status', $finalStatuses),
                    'assignedSubmissions as total_orders_count',
                ])
                ->orderBy('name')
                ->get(),
            'awaitingReview' => Deliverable::where('status', DeliverableStatus::PendingReview)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.team.create');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254', 'unique:users,email'],
            'whatsapp_number' => ['nullable', 'string', 'max:25', new WhatsAppNumber],
        ], ['email.unique' => 'This email is already used by another account.']);

        $member = new User;
        $member->fill([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'whatsapp_number' => WhatsAppNumberSupport::normalize($data['whatsapp_number'] ?? null),
            'password' => Str::random(64),
        ]);
        $member->role = UserRole::Expert;
        $member->status = AccountStatus::Active;
        $member->email_verified_at = now();
        $member->created_by = $request->user()->id;
        $member->save();

        $audit->record('team.member_added', $member);

        return redirect()->route('admin.team.index')->with(...$this->invite($member));
    }

    public function resendInvite(User $member): RedirectResponse
    {
        abort_unless($member->isExpert(), 404);

        return back()->with(...$this->invite($member));
    }

    public function suspend(User $member, AuditLogger $audit): RedirectResponse
    {
        abort_unless($member->isExpert(), 404);

        $member->status = AccountStatus::Suspended;
        $member->save();
        DB::table('sessions')->where('user_id', $member->id)->delete();
        $audit->record('team.member_suspended', $member);

        return back()->with('status', $member->name.' can no longer sign in. Their assigned orders stay assigned until you change them.');
    }

    public function reactivate(User $member, AuditLogger $audit): RedirectResponse
    {
        abort_unless($member->isExpert(), 404);

        $member->status = AccountStatus::Active;
        $member->save();
        $audit->record('team.member_reactivated', $member);

        return back()->with('status', $member->name.' can sign in again.');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function invite(User $member): array
    {
        try {
            $token = Password::createToken($member);
            // Built from APP_URL, never from the request host.
            $url = rtrim((string) config('app.url'), '/').route('password.reset', ['token' => $token, 'email' => $member->email], false);

            Mail::to((string) $member->email)->send(new TeamInvitation($member->name, $url, (int) config('auth.passwords.users.expire')));
        } catch (Throwable $exception) {
            Log::error('Team invitation could not be sent.', ['user' => $member->id, 'exception' => class_basename($exception)]);

            return ['error', 'The invitation email could not be sent. Check the mail settings, then use "Resend invite".'];
        }

        return ['status', 'Invitation sent to '.$member->email.'. They set a password from the link, then sign in.'];
    }
}
