<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\ScheduledLessonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly ScheduledLessonService $scheduledLessonService,
    ) {}

    public function index(Request $request): View
    {
        $notifications = $this->scheduledLessonService->recentNotifications($request->user(), 50);

        return view('education.notifications.index', [
            'user' => $request->user(),
            'notifications' => $notifications,
        ]);
    }

    public function read(Request $request, AppNotification $notification): RedirectResponse
    {
        $this->scheduledLessonService->markNotificationRead($notification, $request->user());

        if ($notification->link) {
            return redirect()->to($notification->link);
        }

        return back()->with('status', 'Notification marked as read.');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $this->scheduledLessonService->markAllNotificationsRead($request->user());

        return back()->with('status', 'All notifications marked as read.');
    }
}
