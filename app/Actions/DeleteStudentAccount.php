<?php

namespace App\Actions;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Permanently deletes a student, their submissions and their stored files.
 * The audit trail keeps only non-identifying facts about the deletion.
 */
class DeleteStudentAccount
{
    public function __construct(private DeleteSubmissionFiles $deleteFiles, private AuditLogger $audit) {}

    public function handle(User $student): void
    {
        $student->load('submissions.files');
        $submissionCount = $student->submissions->count();
        $studentId = $student->id;

        DB::transaction(function () use ($student) {
            DB::table('sessions')->where('user_id', $student->id)->delete();
            $student->delete();
        });

        foreach ($student->submissions as $submission) {
            $this->deleteFiles->handle($submission);
        }

        $this->audit->record('student.deleted', null, [
            'former_user_id' => $studentId,
            'submissions_deleted' => $submissionCount,
        ]);
    }
}
