<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MessageChannel;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Submission;
use App\Services\MessageThreads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Inbox across all orders: unread conversations first, then recent ones.
 */
class MessagesController extends Controller
{
    public function index(Request $request, MessageThreads $threads): View
    {
        $unread = $threads->unreadForAdmin($request->user())->pluck('unread', 'submission_id');

        $latest = DB::table('messages')
            ->selectRaw('submission_id, channel, MAX(id) as last_id')
            ->groupBy('submission_id', 'channel')
            ->orderByDesc('last_id')
            ->limit(60)
            ->get()
            ->map(fn (object $row) => ['submission_id' => (int) $row->submission_id, 'channel' => MessageChannel::from((string) $row->channel), 'last_id' => (int) $row->last_id]);

        $messages = Message::with('sender')->whereIn('id', $latest->pluck('last_id'))->get()->keyBy('id');
        $submissions = Submission::with(['student', 'expert'])->whereIn('id', $latest->pluck('submission_id'))->get()->keyBy('id');

        $conversations = $latest->map(fn (array $row) => [
            'submission' => $submissions[$row['submission_id']],
            'channel' => $row['channel'],
            'last' => $messages[$row['last_id']],
        ])->sortByDesc(fn (array $c) => [($unread[$c['submission']->id] ?? 0) > 0, $c['last']->id])->values();

        return view('admin.messages', ['conversations' => $conversations, 'unread' => $unread]);
    }
}
