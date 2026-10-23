<?php

namespace App\Http\Controllers\Student;

use App\Enums\MessageChannel;
use App\Http\Controllers\Controller;
use App\Services\MessageThreads;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, MessageThreads $threads): View
    {
        $user = $request->user();

        $submissions = $user->submissions()
            ->with(['brief', 'quotation.payments'])
            ->latest('submitted_at')
            ->paginate(10);

        return view('student.dashboard', [
            'user' => $user,
            'submissions' => $submissions,
            'unread' => $submissions->getCollection()->mapWithKeys(fn ($s) => [$s->id => $threads->unreadCount($s, $user, MessageChannel::Student)]),
        ]);
    }
}
