<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ThemeSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get theme setting by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("theme_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->where('is_active', true)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return match ($setting->type) {
                'json' => json_decode($setting->value, true),
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'color' => $setting->value,
                default => $setting->value,
            };
        });
    }

    /**
     * Set theme setting
     */
    public static function set(string $key, $value, string $type = 'string', string $category = 'general', string $description = null)
    {
        $formattedValue = match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $formattedValue,
                'type' => $type,
                'category' => $category,
                'description' => $description,
                'is_active' => true
            ]
        );

        Cache::forget("theme_setting_{$key}");
        Cache::forget('theme_settings_all');
        
        return $setting;
    }

    /**
     * Get all theme settings by category
     */
    public static function getByCategory(string $category)
    {
        return Cache::remember("theme_settings_{$category}", 3600, function () use ($category) {
            return static::where('category', $category)
                ->where('is_active', true)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Clear theme cache
     */
    public static function clearCache()
    {
        Cache::forget('theme_settings_all');
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("theme_setting_{$key}");
        }
        
        $categories = static::distinct('category')->pluck('category');
        foreach ($categories as $category) {
            Cache::forget("theme_settings_{$category}");
        }
    }
}
