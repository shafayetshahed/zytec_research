<?php

namespace App\Services;

use App\Enums\MessageChannel;
use App\Enums\UserRole;
use App\Models\Message;
use App\Models\NotificationLog;
use App\Models\Submission;
use App\Models\User;
use App\Support\PrivateFileStore;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Per-order message threads. The student thread connects the student with
 * the Zytecs Research team; the team thread connects the assigned team
 * member with administrators. Neither side sees the other thread.
 */
class MessageThreads
{
    public function __construct(private OrderNotifier $notifier) {}

    public function post(Submission $submission, MessageChannel $channel, User $sender, ?string $body, ?UploadedFile $attachment): Message
    {
        $stored = $attachment ? PrivateFileStore::store($attachment, 'messages') : null;

        try {
            $message = new Message;
            $message->submission_id = $submission->id;
            $message->channel = $channel;
            $message->sender_id = $sender->id;
            $message->body = filled($body) ? trim((string) $body) : null;

            if ($stored !== null) {
                $message->attachment_name = $stored['original_name'];
                $message->attachment_path = $stored['storage_path'];
                $message->attachment_mime = $stored['mime_type'];
                $message->attachment_size = $stored['size'];
            }

            $message->save();
        } catch (Throwable $exception) {
            PrivateFileStore::delete($stored['storage_path'] ?? null);

            throw $exception;
        }

        $this->markRead($submission, $sender, $channel);
        $this->alertCounterpart($submission, $channel, $sender);

        return $message;
    }

    /**
     * @return Collection<int, Message>
     */
    public function thread(Submission $submission, MessageChannel $channel): Collection
    {
        return $submission->messages()->where('channel', $channel)->with('sender')->get();
    }

    public function unreadCount(Submission $submission, User $user, MessageChannel $channel): int
    {
        $lastRead = (int) DB::table('message_reads')
            ->where(['submission_id' => $submission->id, 'user_id' => $user->id, 'channel' => $channel->value])
            ->value('last_read_message_id');

        return $submission->messages()
            ->where('channel', $channel)
            ->where('id', '>', $lastRead)
            ->where(fn ($query) => $query->whereNull('sender_id')->orWhere('sender_id', '!=', $user->id))
            ->count();
    }

    public function markRead(Submission $submission, User $user, MessageChannel $channel): void
    {
        $latest = (int) $submission->messages()->where('channel', $channel)->max('id');

        DB::table('message_reads')->updateOrInsert(
            ['submission_id' => $submission->id, 'user_id' => $user->id, 'channel' => $channel->value],
            ['last_read_message_id' => $latest, 'updated_at' => now(), 'created_at' => now()],
        );
    }

    /**
     * Unread messages from students and team members, per order, for an administrator.
     *
     * @return Collection<int, array{submission_id: int, unread: int}>
     */
    public function unreadForAdmin(User $admin): Collection
    {
        $adminIds = User::where('role', UserRole::Admin)->pluck('id');

        return DB::table('messages as m')
            ->leftJoin('message_reads as r', function ($join) use ($admin) {
                $join->on('r.submission_id', '=', 'm.submission_id')
                    ->on('r.channel', '=', 'm.channel')
                    ->where('r.user_id', '=', $admin->id);
            })
            ->whereNotIn('m.sender_id', $adminIds)
            ->whereRaw('m.id > COALESCE(r.last_read_message_id, 0)')
            ->groupBy('m.submission_id')
            ->selectRaw('m.submission_id, COUNT(*) as unread')
            ->get()
            ->map(fn (object $row) => ['submission_id' => (int) $row->submission_id, 'unread' => (int) $row->unread]);
    }

    private function alertCounterpart(Submission $submission, MessageChannel $channel, User $sender): void
    {
        match (true) {
            $channel === MessageChannel::Student && $sender->isAdmin() => $this->notifier->send($submission, NotificationLog::TYPE_MESSAGE_TO_STUDENT, $submission->contact_email, throttle: true),
            $channel === MessageChannel::Team && $sender->isAdmin() => $this->notifier->send($submission, NotificationLog::TYPE_MESSAGE_TO_TEAM, $submission->expert?->email, throttle: true),
            default => $this->notifier->send($submission, NotificationLog::TYPE_MESSAGE_TO_ADMIN, $this->notifier->adminAddress(), throttle: true),
        };
    }
}
