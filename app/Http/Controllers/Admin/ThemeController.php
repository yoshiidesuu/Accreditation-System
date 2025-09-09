<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ThemeController extends Controller
{
    public function index()
    {
        $colorSettings = ThemeSetting::getByCategory('colors');
        $layoutSettings = ThemeSetting::getByCategory('layout');
        $typographySettings = ThemeSetting::getByCategory('typography');
        $generalSettings = ThemeSetting::getByCategory('general');
        $brandingSettings = ThemeSetting::getByCategory('branding');
        
        return view('admin.theme.index', compact(
            'colorSettings',
            'layoutSettings', 
            'typographySettings',
            'generalSettings',
            'brandingSettings'
        ));
    }
    
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        foreach ($request->settings as $setting) {
            ThemeSetting::setSetting($setting['key'], $setting['value']);
        }
        
        // Clear theme cache
        Cache::forget('theme_settings');
        Cache::forget('theme_css');
        
        return back()->with('success', 'Theme settings updated successfully!');
    }
    
    public function preview(Request $request)
    {
        $settings = $request->input('settings', []);
        
        // Generate preview CSS
        $css = $this->generateCSS($settings);
        
        return response()->json([
            'css' => $css,
            'success' => true
        ]);
    }
    
    public function reset()
    {
        // Reset to default values by re-running seeder logic
        $defaultSettings = [
            'primary_color' => '#8B0000',
            'secondary_color' => '#A0522D',
            'accent_color' => '#D4AF37',
            'success_color' => '#28a745',
            'warning_color' => '#ffc107',
            'danger_color' => '#dc3545',
            'info_color' => '#17a2b8',
            'sidebar_width' => '250',
            'header_height' => '60',
            'border_radius' => '0.375rem',
            'font_family' => 'Inter, system-ui, -apple-system, sans-serif',
            'font_size_base' => '1rem',
            'dark_mode_enabled' => '1',
            'default_theme_mode' => 'light'
        ];
        
        foreach ($defaultSettings as $key => $value) {
            ThemeSetting::setSetting($key, $value);
        }
        
        Cache::forget('theme_settings');
        Cache::forget('theme_css');
        
        return back()->with('success', 'Theme settings reset to defaults!');
    }
    
    public function uploadLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }
        
        $file = $request->file('logo');
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/images', $filename);
        
        // Update logo setting
        ThemeSetting::setSetting('logo_url', '/storage/images/' . $filename);
        
        return back()->with('success', 'Logo uploaded successfully!');
    }
    
    public function uploadFavicon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'favicon' => 'required|image|mimes:ico,png|max:1024',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }
        
        $file = $request->file('favicon');
        $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/images', $filename);
        
        // Update favicon setting
        ThemeSetting::setSetting('favicon_url', '/storage/images/' . $filename);
        
        return back()->with('success', 'Favicon uploaded successfully!');
    }
    
    public function generateCSS($settings = null)
    {
        if (!$settings) {
            $settings = ThemeSetting::getAllSettings();
        }
        
        $css = ":root {\n";
        
        // Color variables
        if (isset($settings['primary_color'])) {
            $css .= "  --bs-primary: {$settings['primary_color']};\n";
        }
        if (isset($settings['secondary_color'])) {
            $css .= "  --bs-secondary: {$settings['secondary_color']};\n";
        }
        if (isset($settings['accent_color'])) {
            $css .= "  --bs-accent: {$settings['accent_color']};\n";
        }
        if (isset($settings['success_color'])) {
            $css .= "  --bs-success: {$settings['success_color']};\n";
        }
        if (isset($settings['warning_color'])) {
            $css .= "  --bs-warning: {$settings['warning_color']};\n";
        }
        if (isset($settings['danger_color'])) {
            $css .= "  --bs-danger: {$settings['danger_color']};\n";
        }
        if (isset($settings['info_color'])) {
            $css .= "  --bs-info: {$settings['info_color']};\n";
        }
        
        // Layout variables
        if (isset($settings['sidebar_width'])) {
            $css .= "  --sidebar-width: {$settings['sidebar_width']}px;\n";
        }
        if (isset($settings['header_height'])) {
            $css .= "  --header-height: {$settings['header_height']}px;\n";
        }
        if (isset($settings['border_radius'])) {
            $css .= "  --bs-border-radius: {$settings['border_radius']};\n";
        }
        
        // Typography variables
        if (isset($settings['font_family'])) {
            $css .= "  --bs-font-sans-serif: {$settings['font_family']};\n";
        }
        if (isset($settings['font_size_base'])) {
            $css .= "  --bs-body-font-size: {$settings['font_size_base']};\n";
        }
        
        $css .= "}\n";
        
        return $css;
    }
}
