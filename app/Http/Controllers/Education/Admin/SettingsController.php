<?php

namespace App\Http\Controllers\Education\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\LearningPlatformService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private readonly LearningPlatformService $learningPlatformService,
    ) {}

    public function edit(Request $request): View
    {
        abort_unless($request->user()->canConfigurePlatformSettings(), 403);

        return view('education.admin.settings', [
            'user' => $request->user(),
            'settings' => [
                'platform_name' => PlatformSetting::getValue('platform_name', 'Woven Education'),
                'support_email' => PlatformSetting::getValue('support_email', 'support@example.com'),
                'allow_registration' => PlatformSetting::getValue('allow_registration', '1'),
                'maintenance_mode' => PlatformSetting::getValue('maintenance_mode', '0'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canConfigurePlatformSettings(), 403);

        $validated = $request->validate([
            'platform_name' => ['required', 'string', 'max:255'],
            'support_email' => ['required', 'email'],
            'allow_registration' => ['required', 'in:0,1'],
            'maintenance_mode' => ['required', 'in:0,1'],
        ]);

        $this->learningPlatformService->updatePlatformSettings($validated);

        return back()->with('status', 'Platform settings saved.');
    }
}
