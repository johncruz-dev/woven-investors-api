<?php

namespace App\Http\Controllers\Education\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class SecurityController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canMaintainSecurity(), 403);

        return view('education.admin.security', [
            'user' => $request->user(),
            'inactiveUsers' => User::query()->where('is_active', false)->count(),
            'activeTokens' => PersonalAccessToken::query()->count(),
            'authRequired' => (bool) config('security.auth_required'),
            'registrationEnabled' => (bool) config('security.registration_enabled'),
            'headersEnabled' => (bool) config('security.headers.enabled'),
        ]);
    }

    public function revokeTokens(Request $request, User $managedUser): RedirectResponse
    {
        abort_unless($request->user()->canMaintainSecurity(), 403);

        $managedUser->tokens()->delete();

        return back()->with('status', "API tokens revoked for {$managedUser->email}.");
    }

    public function deactivateUser(Request $request, User $managedUser): RedirectResponse
    {
        abort_unless($request->user()->canMaintainSecurity(), 403);
        abort_if($managedUser->id === $request->user()->id, 422, 'You cannot deactivate yourself.');

        $managedUser->update(['is_active' => false]);
        $managedUser->tokens()->delete();

        return back()->with('status', "User {$managedUser->email} deactivated.");
    }
}
