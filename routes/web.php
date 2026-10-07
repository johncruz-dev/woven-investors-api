<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Education\Admin\ReportController;
use App\Http\Controllers\Education\Admin\SecurityController;
use App\Http\Controllers\Education\Admin\SettingsController;
use App\Http\Controllers\Education\Admin\SubscriptionController;
use App\Http\Controllers\Education\Admin\UserManagementController;
use App\Http\Controllers\Education\AssignmentController;
use App\Http\Controllers\Education\CourseController;
use App\Http\Controllers\Education\DashboardController as EducationDashboardController;
use App\Http\Controllers\Education\LessonController;
use App\Http\Controllers\Education\MaterialController;
use App\Http\Controllers\Education\MessageController;
use App\Http\Controllers\Education\ProfileController;
use App\Http\Controllers\Education\RosterImportController;
use App\Http\Controllers\Education\StudentController;
use App\Http\Controllers\Education\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'education.access'])->prefix('education')->name('education.')->group(function () {
    Route::get('/', [EducationDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::get('/courses/{course}/students', [CourseController::class, 'manageStudents'])->name('courses.students');
    Route::post('/courses/{course}/students', [CourseController::class, 'addStudent'])->name('courses.students.add');
    Route::delete('/courses/{course}/students/{student}', [CourseController::class, 'removeStudent'])->name('courses.students.remove');

    Route::post('/courses/{course}/lessons', [LessonController::class, 'store'])->name('lessons.store');
    Route::get('/courses/{course}/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::get('/courses/{course}/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
    Route::put('/courses/{course}/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
    Route::delete('/courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
    Route::post('/courses/{course}/lessons/{lesson}/move', [LessonController::class, 'move'])->name('lessons.move');
    Route::post('/courses/{course}/lessons/{lesson}/complete', [LessonController::class, 'complete'])->name('lessons.complete');

    Route::post('/courses/{course}/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

    Route::post('/courses/{course}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');
    Route::post('/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('submissions.grade');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');

    Route::middleware('education.staff')->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    });

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');

    Route::get('/roster/import', [RosterImportController::class, 'create'])->name('roster.create');
    Route::post('/roster/import', [RosterImportController::class, 'store'])->name('roster.store');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{managedUser}', [UserManagementController::class, 'update'])->name('users.update');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');

        Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
        Route::post('/security/users/{managedUser}/revoke-tokens', [SecurityController::class, 'revokeTokens'])->name('security.revoke-tokens');
        Route::post('/security/users/{managedUser}/deactivate', [SecurityController::class, 'deactivateUser'])->name('security.deactivate');
    });
});

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route('education.dashboard');
})->name('dashboard');
