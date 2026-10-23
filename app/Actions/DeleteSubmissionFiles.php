<?php

namespace App\Actions;

use App\Models\Submission;
use App\Support\PrivateFileStore;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Removes a submission's stored files from private storage. Database rows are
 * removed by the caller (or by cascading deletes).
 */
class DeleteSubmissionFiles
{
    public function handle(Submission $submission): void
    {
        $disk = Storage::disk(config('zytecs.uploads.disk'));

        foreach ($submission->files as $file) {
            try {
                $disk->delete($file->storage_path);
            } catch (Throwable $exception) {
                Log::warning('Could not delete a stored brief.', ['file' => $file->id, 'exception' => class_basename($exception)]);
            }
        }

        foreach ($submission->deliverables as $deliverable) {
            PrivateFileStore::delete($deliverable->storage_path);
        }

        foreach ($submission->messages as $message) {
            PrivateFileStore::delete($message->attachment_path);
        }
    }
}
