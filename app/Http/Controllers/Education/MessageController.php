<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Services\LearningPlatformService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class MessageController extends Controller
{
    public function __construct(
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->canMessageTeachers(), 403);

        $user = $request->user();
        $tab = $request->string('tab', 'inbox')->toString();
        if (! in_array($tab, ['inbox', 'sent'], true)) {
            $tab = 'inbox';
        }

        $inbox = Message::query()
            ->where('recipient_id', $user->id)
            ->with(['sender:id,name,role', 'course:id,title'])
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->latest()
            ->paginate(15, ['*'], 'inbox_page')
            ->withQueryString();

        $sent = Message::query()
            ->where('sender_id', $user->id)
            ->with(['recipient:id,name,role', 'course:id,title'])
            ->latest()
            ->paginate(15, ['*'], 'sent_page')
            ->withQueryString();

        return view('education.messages.index', [
            'user' => $user,
            'inbox' => $inbox,
            'sent' => $sent,
            'tab' => $tab,
            'recipients' => $this->learningPlatformService->messageRecipientsFor($user),
            'unreadCount' => $this->learningPlatformService->unreadMessageCount($user),
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

        try {
            $this->learningPlatformService->sendMessage($request->user(), $recipient, $validated);
        } catch (InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['recipient_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('education.messages.index', ['tab' => 'sent'])
            ->with('status', 'Message sent. It is now in the recipient inbox.');
    }

    public function show(Request $request, Message $message): View
    {
        abort_unless(
            $message->sender_id === $request->user()->id || $message->recipient_id === $request->user()->id,
            403
        );

        if ($message->recipient_id === $request->user()->id && $message->read_at === null) {
            $message->update(['read_at' => now()]);
            session()->now('status', 'Marked as read. Unread count updated.');
        }

        $message->load(['sender', 'recipient', 'course']);

        return view('education.messages.show', [
            'user' => $request->user(),
            'message' => $message->fresh(['sender', 'recipient', 'course']),
            'recipients' => $this->learningPlatformService->messageRecipientsFor($request->user()),
        ]);
    }
}
