<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function update(Request $request)
    {
        $tenant = tenant();

        // Handle Branding & General
        if ($request->hasAny(['school_name', 'school_short_name', 'site_description', 'primary_email'])) {
            $data = $request->only(['school_name', 'school_short_name', 'site_description', 'primary_email']);
            
            foreach ($data as $key => $value) {
                if ($value !== null) {
                    $tenant->update([$key => $value]);
                }
            }
            
            // Handle Logo Upload
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('branding', 'public');
                $tenant->update(['logo' => $path]);
            }
            
            $tenant->update(['has_updated_settings' => true]);
            return back()->with('success', 'Settings updated successfully.');
        }

        // Handle Appearance
        if ($request->has('theme') || $request->has('theme_color')) {
            if ($request->has('theme')) {
                // We store appearance settings in a JSON column or as direct tenant attributes
                // Based on the blade, it uses tenant('appearance') or simple attributes
                $tenant->update(['theme_preference' => $request->theme]);
            }
            if ($request->has('theme_color')) {
                $tenant->update(['theme_color' => $request->theme_color]);
            }
            return back()->with('success', 'Appearance updated successfully.');
        }

        return back();
    }

    public function updateSystemVersion(Request $request)
    {
        $request->validate(['action' => 'required|in:upgrade,rollback']);
        $tenant = tenant();

        if ($request->action === 'upgrade') {
            $tenant->update(['system_version' => 'v2.0']);
            return back()->with('success', 'Platform successfully upgraded to Version 2.0!');
        } else {
            $tenant->update(['system_version' => 'v1.0']);
            return back()->with('success', 'Platform successfully rolled back to Version 1.0.');
        }
    }
}
