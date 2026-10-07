<?php

namespace App\Http\Controllers\Education\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageUsersAndPermissions(), 403);

        $users = User::query()->orderBy('role')->orderBy('name')->paginate(20);

        return view('education.admin.users.index', [
            'user' => $request->user(),
            'users' => $users,
            'roles' => [UserRole::Admin, UserRole::Teacher, UserRole::Student, UserRole::Counselor],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageUsersAndPermissions(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::in([
                UserRole::Admin->value,
                UserRole::Teacher->value,
                UserRole::Student->value,
                UserRole::Counselor->value,
            ])],
        ]);

        User::create($validated);

        return back()->with('status', 'User created.');
    }

    public function update(Request $request, User $managedUser): RedirectResponse
    {
        abort_unless($request->user()->canManageUsersAndPermissions(), 403);

        $validated = $request->validate([
            'role' => ['required', Rule::in([
                UserRole::Admin->value,
                UserRole::Teacher->value,
                UserRole::Student->value,
                UserRole::Counselor->value,
            ])],
            'is_active' => ['required', 'boolean'],
        ]);

        $managedUser->update($validated);

        return back()->with('status', 'User permissions updated.');
    }
}
