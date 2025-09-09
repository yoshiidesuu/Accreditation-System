<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParameterContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parameter_id',
        'user_id',
        'college_id',
        'academic_year_id',
        'content',
        'notes',
        'attachments',
        'status',
        'meta',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'meta' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * Get the parameter that owns the content.
     */
    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }

    /**
     * Get the user that owns the content.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the college that owns the content.
     */
    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    /**
     * Get the academic year that owns the content.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the user who reviewed the content.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the accreditation tags for this content.
     */
    public function accreditationTags(): HasMany
    {
        return $this->hasMany(AccreditationTag::class);
    }

    /**
     * Get the access requests for this content.
     */
    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class, 'file_id');
    }

    /**
     * Check if content is editable.
     */
    public function isEditable()
    {
        return in_array($this->status, [self::STATUS_DRAFT]);
    }

    /**
     * Check if content is submitted.
     */
    public function isSubmitted()
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    /**
     * Check if content is approved.
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if content is rejected.
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
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
     * Scope to filter by parameter.
     */
    public function scopeByParameter($query, $parameterId)
    {
        return $query->where('parameter_id', $parameterId);
    }

    /**
     * Get formatted attachments with URLs.
     */
    public function getFormattedAttachmentsAttribute()
    {
        if (!$this->attachments) {
            return [];
        }

        return collect($this->attachments)->map(function ($attachment) {
            return [
                'filename' => $attachment['filename'],
                'path' => $attachment['path'],
                'url' => asset('storage/' . $attachment['path']),
                'size' => $attachment['size'] ?? 0,
                'mime_type' => $attachment['mime_type'] ?? 'application/octet-stream',
                'size_formatted' => $this->formatFileSize($attachment['size'] ?? 0),
            ];
        })->toArray();
    }

    /**
     * Format file size in human readable format.
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}