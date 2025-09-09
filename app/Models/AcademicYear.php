<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AcademicYear extends Model
{
    protected $fillable = [
        'label',
        'start_date',
        'end_date',
        'active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one active academic year
        static::saving(function ($academicYear) {
            if ($academicYear->active) {
                // Deactivate all other academic years
                static::where('id', '!=', $academicYear->id)
                    ->where('active', true)
                    ->update(['active' => false]);
            }
        });
    }

    /**
     * Scope to get active academic year
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get current academic year based on date
     */
    public function scopeCurrent(Builder $query)
    {
        $today = Carbon::today();
        return $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
    }

    /**
     * Get the active academic year
     */
    public static function getActive()
    {
        return static::active()->first();
    }

    /**
     * Check if this academic year is currently active
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Check if this academic year is current based on dates
     */
    public function isCurrent()
    {
        $today = Carbon::today();
        return $today->between($this->start_date, $this->end_date);
    }

    /**
     * Get formatted date range
     */
    public function getDateRangeAttribute()
    {
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->active) {
            return 'bg-success';
        } elseif ($this->isCurrent()) {
            return 'bg-warning';
        }
        return 'bg-secondary';
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        if ($this->active) {
            return 'Active';
        } elseif ($this->isCurrent()) {
            return 'Current';
        }
        return 'Inactive';
    }
}
