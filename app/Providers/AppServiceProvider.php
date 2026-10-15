<?php

namespace App\Providers;

use App\Models\AppNotification;
use App\Models\Message;
use App\Services\ScheduledLessonService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.education', function ($view) {
            $user = auth()->user();
            $unreadMessagesCount = 0;
            $unreadNotificationsCount = 0;
            $upcomingLessonToasts = [];

            if ($user) {
                try {
                    app(ScheduledLessonService::class)->dispatchUpcomingReminders();
                } catch (\Throwable) {
                    // Tables may not exist yet during early migrate/test bootstrap.
                }

                if ($user->canMessageTeachers()) {
                    $unreadMessagesCount = Message::query()
                        ->where('recipient_id', $user->id)
                        ->whereNull('read_at')
                        ->count();
                }

                $unreadNotificationsCount = AppNotification::query()
                    ->where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count();

                $upcomingLessonToasts = AppNotification::query()
                    ->where('user_id', $user->id)
                    ->where('type', 'lesson_upcoming')
                    ->whereNull('read_at')
                    ->latest()
                    ->limit(3)
                    ->get(['title', 'body'])
                    ->map(fn (AppNotification $n) => [
                        'title' => $n->title,
                        'body' => $n->body,
                    ])
                    ->all();
            }

            $view->with([
                'user' => $user,
                'unreadMessagesCount' => $unreadMessagesCount,
                'unreadNotificationsCount' => $unreadNotificationsCount,
                'upcomingLessonToasts' => $upcomingLessonToasts,
            ]);
        });
    }
}
