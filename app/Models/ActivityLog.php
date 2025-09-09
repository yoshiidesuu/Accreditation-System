<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_log';

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'event',
        'causer_type',
        'causer_id',
        'properties',
        'batch_uuid',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'batch_uuid',
    ];

    /**
     * Get the user that caused this activity.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the subject of this activity.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that caused this activity (specific to User model).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id')
            ->where('causer_type', User::class);
    }

    /**
     * Scope to filter by log name.
     */
    public function scopeByLogName($query, $logName)
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to filter by subject type.
     */
    public function scopeBySubjectType($query, $subjectType)
    {
        return $query->where('subject_type', $subjectType);
    }

    /**
     * Scope to filter by causer (user).
     */
    public function scopeByCauser($query, $causerId)
    {
        return $query->where('causer_id', $causerId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
    }

    /**
     * Scope to get recent activities.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Get formatted description with context.
     */
    public function getFormattedDescriptionAttribute()
    {
        $description = $this->description;
        
        if ($this->subject) {
            $subjectName = method_exists($this->subject, 'getDisplayName') 
                ? $this->subject->getDisplayName() 
                : ($this->subject->name ?? $this->subject->title ?? 'Item #' . $this->subject->id);
            
            $description = str_replace('{subject}', $subjectName, $description);
        }
        
        return $description;
    }

    /**
     * Get the IP address from properties.
     */
    public function getIpAddressAttribute()
    {
        return $this->properties['ip_address'] ?? null;
    }

    /**
     * Get the user agent from properties.
     */
    public function getUserAgentAttribute()
    {
        return $this->properties['user_agent'] ?? null;
    }

    /**
     * Get changes from properties.
     */
    public function getChangesAttribute()
    {
        return $this->properties['attributes'] ?? [];
    }

    /**
     * Get old values from properties.
     */
    public function getOldValuesAttribute()
    {
        return $this->properties['old'] ?? [];
    }

    /**
     * Check if this activity has changes.
     */
    public function hasChanges()
    {
        return !empty($this->changes) || !empty($this->old_values);
    }

    /**
     * Get activity statistics.
     */
    public static function getStats($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'total' => static::where('created_at', '>=', $startDate)->count(),
            'by_event' => static::where('created_at', '>=', $startDate)
                ->selectRaw('event, COUNT(*) as count')
                ->groupBy('event')
                ->pluck('count', 'event')
                ->toArray(),
            'by_user' => static::with('causer')
                ->where('created_at', '>=', $startDate)
                ->whereNotNull('causer_id')
                ->selectRaw('causer_id, COUNT(*) as count')
                ->groupBy('causer_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'daily' => static::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray(),
        ];
    }
}