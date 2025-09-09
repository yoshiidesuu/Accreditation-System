<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Area extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'code',
        'title',
        'description',
        'parent_area_id',
        'college_id',
        'academic_year_id',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get the college that owns the area.
     */
    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    /**
     * Get the academic year that owns the area.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the parent area.
     */
    public function parentArea(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'parent_area_id');
    }

    /**
     * Get the child areas.
     */
    public function childAreas()
    {
        return $this->hasMany(Area::class, 'parent_area_id');
    }

    /**
     * Get all parameters for this area.
     */
    public function parameters(): HasMany
    {
        return $this->hasMany(Parameter::class);
    }

    /**
     * Scope to get root areas (no parent).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_area_id');
    }

    /**
     * Scope to get areas by college.
     */
    public function scopeByCollege($query, $collegeId)
    {
        return $query->where('college_id', $collegeId);
    }

    /**
     * Scope to get areas by academic year.
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Get the full hierarchical path of the area.
     */
    public function getFullPathAttribute(): string
    {
        $path = collect();
        $current = $this;
        
        while ($current) {
            $path->prepend($current->title);
            $current = $current->parentArea;
        }
        
        return $path->implode(' > ');
    }

    /**
     * Get the depth level of the area in hierarchy.
     */
    public function getDepthLevelAttribute(): int
    {
        $level = 0;
        $current = $this->parentArea;
        
        while ($current) {
            $level++;
            $current = $current->parentArea;
        }
        
        return $level;
    }

    /**
     * Check if this area has children.
     */
    public function hasChildren(): bool
    {
        return $this->childAreas()->exists();
    }

    /**
     * Get all descendants (children, grandchildren, etc.).
     */
    public function descendants()
    {
        return $this->childAreas()->with('descendants');
    }

    /**
     * Get activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'title', 'description', 'parent_area_id', 'college_id', 'academic_year_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
