<?php

namespace App\Services;

use App\Enums\DeliverableStatus;
use App\Models\Deliverable;
use App\Models\NotificationLog;
use App\Models\Submission;
use App\Models\User;
use App\Support\PrivateFileStore;
use Illuminate\Http\UploadedFile;
use Throwable;

/**
 * Guidance and feedback materials: team members upload, administrators
 * approve or send back, and approved materials are released to the student
 * once the order is paid in full (or earlier if the administrator chooses).
 */
class MaterialsService
{
    public function __construct(private OrderNotifier $notifier) {}

    public function upload(Submission $submission, User $uploader, UploadedFile $file, ?string $note): Deliverable
    {
        $stored = PrivateFileStore::store($file, 'materials');

        try {
            $deliverable = new Deliverable;
            $deliverable->forceFill($stored);
            $deliverable->submission_id = $submission->id;
            $deliverable->uploaded_by = $uploader->id;
            $deliverable->note = $note;

            // Administrators are the reviewers, so their own uploads are approved immediately.
            if ($uploader->isAdmin()) {
                $deliverable->status = DeliverableStatus::Approved;
                $deliverable->reviewed_by = $uploader->id;
                $deliverable->reviewed_at = now();
            } else {
                $deliverable->status = DeliverableStatus::PendingReview;
            }

            $deliverable->save();
        } catch (Throwable $exception) {
            PrivateFileStore::delete($stored['storage_path']);

            throw $exception;
        }

        if ($uploader->isAdmin()) {
            $this->releaseReady($submission);
        } else {
            $this->notifier->send($submission, NotificationLog::TYPE_MATERIALS_UPLOADED, $this->notifier->adminAddress());
        }

        return $deliverable;
    }

    public function approve(Deliverable $deliverable, User $admin, bool $releaseBeforePayment): void
    {
        $deliverable->status = DeliverableStatus::Approved;
        $deliverable->reviewed_by = $admin->id;
        $deliverable->reviewed_at = now();
        $deliverable->review_note = null;
        $deliverable->release_before_payment = $releaseBeforePayment;
        $deliverable->save();

        $this->releaseReady($deliverable->submission);
    }

    public function sendBack(Deliverable $deliverable, User $admin, string $note): void
    {
        $deliverable->status = DeliverableStatus::Returned;
        $deliverable->reviewed_by = $admin->id;
        $deliverable->reviewed_at = now();
        $deliverable->review_note = $note;
        $deliverable->released_at = null;
        $deliverable->save();

        $this->notifier->send($deliverable->submission, NotificationLog::TYPE_MESSAGE_TO_TEAM, $deliverable->submission->expert?->email);
    }

    /**
     * Marks approved materials as released when the student may now see them,
     * and tells the student once per release.
     */
    public function releaseReady(Submission $submission): int
    {
        $submission->loadMissing('quotation');
        $released = 0;

        foreach ($submission->deliverables()->where('status', DeliverableStatus::Approved)->whereNull('released_at')->get() as $deliverable) {
            if ($deliverable->isAvailableToStudent($submission)) {
                $deliverable->released_at = now();
                $deliverable->save();
                $released++;
            }
        }

        if ($released > 0) {
            $this->notifier->send($submission, NotificationLog::TYPE_MATERIALS_READY, $submission->contact_email);
        }

        return $released;
    }
}
