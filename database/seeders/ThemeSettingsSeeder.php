<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ThemeSetting;

class ThemeSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Color Settings
            [
                'key' => 'primary_color',
                'value' => '#8B0000',
                'type' => 'color',
                'category' => 'colors',
                'description' => 'Primary maroon color for the application'
            ],
            [
                'key' => 'secondary_color',
                'value' => '#A0522D',
                'type' => 'color',
                'category' => 'colors',
                'description' => 'Secondary color for accents'
            ],
            [
                'key' => 'accent_color',
                'value' => '#D4AF37',
                'type' => 'color',
                'category' => 'colors',
                'description' => 'Accent gold color'
            ],
            [
                'key' => 'success_color',
                'value' => '#28a745',
                'type' => 'color',
                'category' => 'colors',
                'description' => 'Success state color'
            ],
            [
                'key' => 'warning_color',
                'value' => '#ffc107',
                'type' => 'color',
                'category' => 'colors',
                'description' => 'Warning state color'
            ],
            [
                'key' => 'danger_color',
                'value' => '#dc3545',
                'type' => 'color',
                'category' => 'colors',
                'description' => 'Danger state color'
            ],
            [
                'key' => 'info_color',
                'value' => '#17a2b8',
                'type' => 'color',
                'category' => 'colors',
                'description' => 'Info state color'
            ],
            
            // Layout Settings
            [
                'key' => 'sidebar_width',
                'value' => '250',
                'type' => 'string',
                'category' => 'layout',
                'description' => 'Sidebar width in pixels'
            ],
            [
                'key' => 'header_height',
                'value' => '60',
                'type' => 'string',
                'category' => 'layout',
                'description' => 'Header height in pixels'
            ],
            [
                'key' => 'border_radius',
                'value' => '0.375rem',
                'type' => 'string',
                'category' => 'layout',
                'description' => 'Default border radius'
            ],
            
            // Typography Settings
            [
                'key' => 'font_family',
                'value' => 'Inter, system-ui, -apple-system, sans-serif',
                'type' => 'string',
                'category' => 'typography',
                'description' => 'Primary font family'
            ],
            [
                'key' => 'font_size_base',
                'value' => '1rem',
                'type' => 'string',
                'category' => 'typography',
                'description' => 'Base font size'
            ],
            
            // General Settings
            [
                'key' => 'dark_mode_enabled',
                'value' => '1',
                'type' => 'boolean',
                'category' => 'general',
                'description' => 'Enable dark mode toggle'
            ],
            [
                'key' => 'default_theme_mode',
                'value' => 'light',
                'type' => 'string',
                'category' => 'general',
                'description' => 'Default theme mode (light/dark)'
            ],
            [
                'key' => 'logo_url',
                'value' => '/images/logo.png',
                'type' => 'string',
                'category' => 'branding',
                'description' => 'Application logo URL'
            ],
            [
                'key' => 'favicon_url',
                'value' => '/images/favicon.ico',
                'type' => 'string',
                'category' => 'branding',
                'description' => 'Application favicon URL'
            ],
            [
                'key' => 'logo_alt_text',
                'value' => 'Accreditation Management System',
                'type' => 'string',
                'category' => 'branding',
                'description' => 'Logo alternative text'
            ]
        ];

        foreach ($settings as $setting) {
            ThemeSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
