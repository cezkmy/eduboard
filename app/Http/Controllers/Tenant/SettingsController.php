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
        if ($request->hasAny(['school_name', 'school_short_name', 'site_description', 'primary_email', 'logo'])) {
            
            // Validate logo if uploaded
            if ($request->hasFile('logo')) {
                $request->validate([
                    'logo' => 'file|mimes:png,jpg,jpeg|max:102400', // 100MB max
                ]);
            }

            // Update text fields via central DB connection
            $textData = array_filter($request->only(['school_name', 'school_short_name', 'site_description', 'primary_email']));
            if (!empty($textData)) {
                \Illuminate\Support\Facades\DB::connection('mysql')
                    ->table('tenants')
                    ->where('id', $tenant->id)
                    ->update($textData);
            }
            
            // Handle Logo Upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($tenant->logo) {
                    Storage::disk('public')->delete($tenant->logo);
                }
                $file = $request->file('logo');
                $fileSize = $file->getSize();
                $path = $file->store('branding', 'public');
                
                // Track Upload Bandwidth
                if ($fileSize > 0) {
                    $gb = $fileSize / 1073741824;
                    \Illuminate\Support\Facades\DB::connection('mysql')
                        ->table('tenants')
                        ->where('id', $tenant->id)
                        ->increment('bandwidth_used_gb', $gb);
                }
                
                // Save via central DB connection
                \Illuminate\Support\Facades\DB::connection('mysql')
                    ->table('tenants')
                    ->where('id', $tenant->id)
                    ->update(['logo' => $path]);

                // Also refresh the in-memory tenant so tenant('logo') returns the new value
                $tenant->logo = $path;
                $tenant->syncOriginal();
                
                // Re-calculate total storage
                $tenant->updateStorageUsage();
            }
            
            // has_updated_settings is stored in the JSON data blob (not a real column)
            // so use Eloquent here — Tenancy's model handles writing it to the `data` field
            $tenant->setAttribute('has_updated_settings', true);
            $tenant->save();

            return back()->with('success', 'Settings updated successfully.');
        }

        // Handle Appearance
        if ($request->has('theme') || $request->has('theme_color')) {
            $appearanceData = [];
            if ($request->has('theme')) $appearanceData['theme_preference'] = $request->theme;
            if ($request->has('theme_color')) $appearanceData['theme_color'] = $request->theme_color;
            
            \Illuminate\Support\Facades\DB::connection('mysql')
                ->table('tenants')
                ->where('id', $tenant->id)
                ->update($appearanceData);

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
