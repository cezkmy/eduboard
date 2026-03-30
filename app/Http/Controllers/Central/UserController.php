<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Update the user's profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'school_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            // Delete old photo if it exists
            if ($user->profile_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo);
            }
            
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo'] = $path;
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    /**
     * Update school-specific settings.
     */
    public function updateSettings(Request $request)
    {
        $user = auth()->user();
        $activeTab = $request->input('active_tab', 'general');

        // Update basic user info if present
        $user->update($request->only(['school_name', 'address', 'phone']));

        // You can add logic here to save other settings (language, notifications, etc.)
        // to a 'settings' JSON column or a separate table if needed.
        
        return back()->with([
            'success' => ucfirst($activeTab) . ' settings updated successfully!',
            'active_tab' => $activeTab
        ]);
    }
}
