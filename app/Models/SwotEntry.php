<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SwotEntry extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'college_id',
        'area_id',
        'type',
        'description',
        'created_by',
        'status',
        'reviewed_by',
        'reviewed_at',
        'notes'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // SWOT Types
    const TYPE_STRENGTH = 'S';
    const TYPE_WEAKNESS = 'W';
    const TYPE_OPPORTUNITY = 'O';
    const TYPE_THREAT = 'T';

    // Status
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public static function getTypes(): array
    {
        return [
            self::TYPE_STRENGTH => 'Strength',
            self::TYPE_WEAKNESS => 'Weakness',
            self::TYPE_OPPORTUNITY => 'Opportunity',
            self::TYPE_THREAT => 'Threat',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    public function getTypeNameAttribute(): string
    {
        return self::getTypes()[$this->type] ?? 'Unknown';
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Unknown';
    }

    // Relationships
    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopeByCollege($query, $collegeId)
    {
        return $query->where('college_id', $collegeId);
    }

    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['college_id', 'area_id', 'type', 'description', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}