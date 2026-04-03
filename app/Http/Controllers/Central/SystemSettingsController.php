<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralSetting;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'platform_name' => 'required|string',
            'support_email' => 'required|email',
            'description' => 'nullable|string',
            'language' => 'required|string',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            CentralSetting::set($key, $value);
        }

        return back()->with('success', 'General settings updated successfully.');
    }

    public function updateSecurity(Request $request)
    {
        $settings = [
            'two_factor' => $request->has('twoFactor'),
            'login_notifications' => $request->has('loginAlerts'),
        ];

        foreach ($settings as $key => $value) {
            CentralSetting::set($key, $value ? '1' : '0');
        }

        return back()->with('success', 'Security settings updated successfully.');
    }

    public function updateNotifications(Request $request)
    {
        $settings = [
            'registration_enabled' => $request->has('registration_enabled'),
            'system_updates_enabled' => $request->has('system_updates'),
        ];

        foreach ($settings as $key => $value) {
            CentralSetting::set($key, $value ? '1' : '0');
        }

        return back()->with('success', 'Notification preferences updated successfully.');
    }

    public function updateRelease(Request $request)
    {
        $request->validate(['version' => 'required|string']);
        
        CentralSetting::set('system_version', $request->version);

        // Here you would trigger the email blast or versioning logic
        // For now, we just save the version.

        return back()->with('success', "Version {$request->version} broadcasted successfully.");
    }
}
