<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Services\LearningPlatformService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->canMessageTeachers(), 403);

        $user = $request->user();
        $inbox = Message::query()
            ->where('recipient_id', $user->id)
            ->with(['sender', 'course'])
            ->latest()
            ->paginate(15, ['*'], 'inbox');

        $sent = Message::query()
            ->where('sender_id', $user->id)
            ->with(['recipient', 'course'])
            ->latest()
            ->paginate(15, ['*'], 'sent');

        $recipients = $user->isStudent()
            ? User::query()->whereIn('role', ['teacher', 'admin'])->where('is_active', true)->orderBy('name')->get()
            : User::query()->where('role', 'student')->where('is_active', true)->orderBy('name')->get();

        return view('education.messages.index', [
            'user' => $user,
            'inbox' => $inbox,
            'sent' => $sent,
            'recipients' => $recipients,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canMessageTeachers(), 403);

        $validated = $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'course_id' => ['nullable', 'exists:courses,id'],
        ]);

        $recipient = User::query()->findOrFail($validated['recipient_id']);

        $this->learningPlatformService->sendMessage($request->user(), $recipient, $validated);

        return back()->with('status', 'Message sent.');
    }

    public function show(Request $request, Message $message): View
    {
        abort_unless(
            $message->sender_id === $request->user()->id || $message->recipient_id === $request->user()->id,
            403
        );

        if ($message->recipient_id === $request->user()->id && $message->read_at === null) {
            $message->update(['read_at' => now()]);
        }

        $message->load(['sender', 'recipient', 'course']);

        return view('education.messages.show', [
            'user' => $request->user(),
            'message' => $message,
        ]);
    }
}
