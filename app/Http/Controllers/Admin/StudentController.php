<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DeleteStudentAccount;
use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'filter' => ['nullable', 'in:unverified,suspended,deletion'],
        ]);

        $term = trim($filters['q'] ?? '');
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        $students = User::students()
            ->withCount('submissions')
            ->when($term !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('whatsapp_number', 'like', $like)))
            ->when(($filters['filter'] ?? null) === 'unverified', fn (Builder $query) => $query->whereNull('email_verified_at'))
            ->when(($filters['filter'] ?? null) === 'suspended', fn (Builder $query) => $query->where('status', AccountStatus::Suspended))
            ->when(($filters['filter'] ?? null) === 'deletion', fn (Builder $query) => $query->whereNotNull('deletion_requested_at'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.students.index', ['students' => $students, 'filters' => $filters]);
    }

    public function show(User $student): View
    {
        abort_unless($student->isStudent(), 404);

        return view('admin.students.show', [
            'student' => $student,
            'submissions' => $student->submissions()->latest('submitted_at')->get(),
        ]);
    }

    public function suspend(User $student, AuditLogger $audit): RedirectResponse
    {
        abort_unless($student->isStudent(), 404);

        $student->status = AccountStatus::Suspended;
        $student->save();
        DB::table('sessions')->where('user_id', $student->id)->delete();

        $audit->record('student.suspended', $student);

        return back()->with('status', 'Account suspended and signed out.');
    }

    public function reactivate(User $student, AuditLogger $audit): RedirectResponse
    {
        abort_unless($student->isStudent(), 404);

        $student->status = AccountStatus::Active;
        $student->save();

        $audit->record('student.reactivated', $student);

        return back()->with('status', 'Account reactivated.');
    }

    public function destroy(Request $request, User $student, DeleteStudentAccount $deleteStudent): RedirectResponse
    {
        abort_unless($student->isStudent(), 404);

        $request->validate(
            ['confirm_email' => ['required', 'string', Rule::in([$student->email])]],
            ['confirm_email.in' => 'Type the student\'s email address exactly to confirm deletion.'],
        );

        $deleteStudent->handle($student);

        return redirect()->route('admin.students.index')->with('status', 'The account, its submissions and stored files were permanently deleted.');
    }
}
