<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ThemeSetting;
use Symfony\Component\HttpFoundation\Response;

class ThemeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get system theme settings
        $systemTheme = ThemeSetting::getByCategory('general');
        
        // Get user theme preferences if authenticated
        $userTheme = [];
        if (Auth::check()) {
            $user = Auth::user();
            $userTheme = [
                'mode' => $user->getThemeMode(),
                'preferences' => $user->getThemePreferences()
            ];
        }
        
        // Merge system and user themes
        $themeConfig = array_merge($systemTheme, $userTheme);
        
        // Share theme configuration with all views
        View::share('themeConfig', $themeConfig);
        View::share('isDarkMode', ($userTheme['mode'] ?? 'light') === 'dark');
        
        return $next($request);
    }
}
