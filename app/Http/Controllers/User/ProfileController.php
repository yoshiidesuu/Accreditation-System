<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the user profile
     */
    public function show()
    {
        $user = Auth::user();
        $user->load(['roles', 'colleges']);
        
        return view('user.profile.show', compact('user'));
    }

    /**
     * Update the user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
        ]);
        
        $user->update($validated);
        
        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Show user settings
     */
    public function settings()
    {
        $user = Auth::user();
        
        return view('user.profile.settings', compact('user'));
    }

    /**
     * Update user settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        
        // Handle password change
        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => 'required',
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);
            
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            
            $user->update([
                'password' => Hash::make($request->password)
            ]);
            
            return back()->with('success', 'Password updated successfully.');
        }
        
        // Handle notification preferences
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'notification_frequency' => 'in:immediate,daily,weekly',
            'timezone' => 'string|max:50',
            'language' => 'string|max:10',
        ]);
        
        // Store preferences in user meta or settings table
        $settings = [
            'email_notifications' => $request->has('email_notifications'),
            'sms_notifications' => $request->has('sms_notifications'),
            'push_notifications' => $request->has('push_notifications'),
            'notification_frequency' => $validated['notification_frequency'] ?? 'daily',
            'timezone' => $validated['timezone'] ?? 'UTC',
            'language' => $validated['language'] ?? 'en',
        ];
        
        $user->update(['settings' => $settings]);
        
        return back()->with('success', 'Settings updated successfully.');
    }
    
    /**
     * Update user theme preferences
     */
    public function updateTheme(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'theme_mode' => 'required|in:light,dark,auto',
            'theme_preferences' => 'nullable|array',
            'theme_preferences.primary_color' => 'nullable|string|max:7',
            'theme_preferences.sidebar_style' => 'nullable|in:default,compact,minimal',
            'theme_preferences.font_size' => 'nullable|in:small,medium,large',
        ]);
        
        $user->setThemeMode($validated['theme_mode']);
        
        if (isset($validated['theme_preferences'])) {
            $user->updateThemePreferences($validated['theme_preferences']);
        }
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme preferences updated successfully.',
                'theme_mode' => $user->getThemeMode(),
                'theme_preferences' => $user->getThemePreferences()
            ]);
        }
        
        return back()->with('success', 'Theme preferences updated successfully.');
    }
}