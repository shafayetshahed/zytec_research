<?php

namespace App\Actions;

use App\Enums\NotificationStatus;
use App\Enums\SubmissionStatus;
use App\Jobs\SendBusinessNotification;
use App\Models\NotificationLog;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionStatusChange;
use App\Models\User;
use App\Support\PrivateFileStore;
use App\Support\SubmissionReference;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Stores a student's brief privately, records the submission atomically and
 * queues the business notification only once everything has been saved.
 */
class SubmitBrief
{
    /**
     * @param  array{assignment_name: string, module_code: string, institution?: ?string, country?: ?string, subject?: ?string, assessment_type?: ?string, deadline?: ?string, description?: ?string, client_token: string}  $details
     * @param  list<UploadedFile>  $supplementary
     */
    public function handle(User $student, string $whatsappNumber, array $details, UploadedFile $brief, array $supplementary = []): Submission
    {
        $existing = $this->findDuplicate($student, $details['client_token']);

        if ($existing !== null) {
            return $existing;
        }

        $disk = Storage::disk(config('zytecs.uploads.disk'));
        $storedPaths = [];

        try {
            $files = [];

            foreach ([$brief, ...$supplementary] as $index => $upload) {
                $file = $this->storeFile($upload, $index === 0);
                $storedPaths[] = $file->storage_path;
                $files[] = $file;
            }

            $submission = DB::transaction(function () use ($student, $whatsappNumber, $details, $files) {
                $submission = new Submission;
                $submission->fill(collect($details)->except('client_token')->all());
                $submission->user_id = $student->id;
                $submission->client_token = $details['client_token'];
                $submission->reference = SubmissionReference::generate();
                $submission->contact_email = $student->email;
                $submission->whatsapp_number = $whatsappNumber;
                $submission->status = SubmissionStatus::New;
                $submission->submitted_at = now();
                $submission->save();

                $submission->files()->saveMany($files);

                $change = new SubmissionStatusChange;
                $change->submission_id = $submission->id;
                $change->to_status = SubmissionStatus::New;
                $change->save();

                if ($student->whatsapp_number !== $whatsappNumber) {
                    $student->whatsapp_number = $whatsappNumber;
                    $student->save();
                }

                return $submission;
            });
        } catch (UniqueConstraintViolationException $exception) {
            $this->deleteFiles($storedPaths);

            return $this->findDuplicate($student, $details['client_token']) ?? throw $exception;
        } catch (Throwable $exception) {
            $this->deleteFiles($storedPaths);

            throw $exception;
        }

        $this->queueNotification($submission);

        return $submission;
    }

    private function findDuplicate(User $student, string $clientToken): ?Submission
    {
        return Submission::where('user_id', $student->id)->where('client_token', $clientToken)->first();
    }

    private function storeFile(UploadedFile $upload, bool $isBrief): SubmissionFile
    {
        $file = new SubmissionFile;
        $file->forceFill(PrivateFileStore::store($upload, (string) config('zytecs.uploads.directory')));
        $file->is_brief = $isBrief;

        return $file;
    }

    /**
     * @param  list<string>  $paths
     */
    private function deleteFiles(array $paths): void
    {
        foreach ($paths as $path) {
            try {
                Storage::disk(config('zytecs.uploads.disk'))->delete($path);
            } catch (Throwable) {
                Log::warning('Could not remove an orphaned upload during rollback.', ['path' => $path]);
            }
        }
    }

    private function queueNotification(Submission $submission): void
    {
        $log = new NotificationLog;
        $log->submission_id = $submission->id;
        $log->type = NotificationLog::TYPE_NEW_SUBMISSION;
        $log->recipient = (string) config('zytecs.notifications.admin_address');
        $log->status = NotificationStatus::Pending;
        $log->save();

        try {
            SendBusinessNotification::dispatch($log->id);
        } catch (Throwable $exception) {
            $log->status = NotificationStatus::Failed;
            $log->error_summary = 'Could not queue the notification ('.class_basename($exception).').';
            $log->save();

            Log::error('Submission notification could not be queued.', [
                'submission' => $submission->reference,
                'exception' => class_basename($exception),
            ]);
        }
    }
}
