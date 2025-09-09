<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accreditation extends Model
{
    use HasFactory;

    protected $fillable = [
        'college_id',
        'academic_year_id',
        'status',
        'assigned_lead_id',
        'assigned_members',
    ];

    protected $casts = [
        'assigned_members' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get the college that owns the accreditation.
     */
    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    /**
     * Get the academic year that owns the accreditation.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the assigned lead user.
     */
    public function assignedLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_lead_id');
    }

    /**
     * Get the accreditation tags for this accreditation.
     */
    public function accreditationTags(): HasMany
    {
        return $this->hasMany(AccreditationTag::class);
    }

    /**
     * Get the parameter contents through accreditation tags.
     */
    public function parameterContents(): BelongsToMany
    {
        return $this->belongsToMany(ParameterContent::class, 'accreditation_tags')
                    ->withTimestamps();
    }

    /**
     * Get assigned member users.
     */
    public function getAssignedMemberUsersAttribute()
    {
        if (empty($this->assigned_members)) {
            return collect();
        }

        return User::whereIn('id', $this->assigned_members)->get();
    }

    /**
     * Check if user is assigned to this accreditation.
     */
    public function isUserAssigned($userId)
    {
        return $this->assigned_lead_id == $userId || 
               (is_array($this->assigned_members) && in_array($userId, $this->assigned_members));
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by college.
     */
    public function scopeByCollege($query, $collegeId)
    {
        return $query->where('college_id', $collegeId);
    }

    /**
     * Scope to filter by academic year.
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to filter by assigned user.
     */
    public function scopeAssignedToUser($query, $userId)
    {
        return $query->where('assigned_lead_id', $userId)
                    ->orWhereJsonContains('assigned_members', $userId);
    }
}