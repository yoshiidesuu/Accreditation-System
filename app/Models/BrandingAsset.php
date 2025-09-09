<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BrandingAsset extends Model
{
    protected $fillable = [
        'type',
        'name',
        'file_path',
        'file_url',
        'file_size',
        'mime_type',
        'width',
        'height',
        'version',
        'is_active',
        'metadata',
        'uploaded_by',
        'activated_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderBy('version', 'desc');
    }

    public static function getActiveLogo()
    {
        return static::byType('logo')->active()->first();
    }

    public static function getActiveFavicon()
    {
        return static::byType('favicon')->active()->first();
    }

    public static function getNextVersion($type)
    {
        $latest = static::byType($type)->latestVersion()->first();
        return $latest ? $latest->version + 1 : 1;
    }

    public function activate()
    {
        // Deactivate all other assets of the same type
        static::byType($this->type)->update([
            'is_active' => false,
            'activated_at' => null,
        ]);

        // Activate this asset
        $this->update([
            'is_active' => true,
            'activated_at' => now(),
        ]);
    }

    public function getFullUrlAttribute()
    {
        return $this->file_url ? asset($this->file_url) : null;
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDimensionsAttribute()
    {
        if ($this->width && $this->height) {
            return $this->width . ' × ' . $this->height;
        }
        return null;
    }
}
