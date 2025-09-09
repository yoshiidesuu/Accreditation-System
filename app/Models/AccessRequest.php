<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AccessRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'requester_id',
        'reason',
        'status',
        'approver_id',
        'expires_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'share_link',
        'share_link_expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'share_link_expires_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }

    /**
     * Get the parameter content (file) that is being requested.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(ParameterContent::class, 'file_id');
    }

    /**
     * Get the user who made the request.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the user who approved/rejected the request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if the request is expired.
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the share link is valid.
     */
    public function isShareLinkValid()
    {
        return $this->share_link && 
               $this->share_link_expires_at && 
               $this->share_link_expires_at->isFuture() &&
               $this->isApproved();
    }

    /**
     * Approve the access request.
     */
    public function approve($approverId, $generateShareLink = false, $shareLinkDuration = 24)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approver_id' => $approverId,
            'approved_at' => now(),
        ]);

        if ($generateShareLink) {
            $this->generateShareLink($shareLinkDuration);
        }

        // Log the approval
        activity()
            ->performedOn($this)
            ->causedBy($approverId)
            ->withProperties([
                'file_id' => $this->file_id,
                'requester_id' => $this->requester_id,
                'share_link_generated' => $generateShareLink,
            ])
            ->log('Access request approved');
    }

    /**
     * Reject the access request.
     */
    public function reject($approverId, $reason = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approver_id' => $approverId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Log the rejection
        activity()
            ->performedOn($this)
            ->causedBy($approverId)
            ->withProperties([
                'file_id' => $this->file_id,
                'requester_id' => $this->requester_id,
                'rejection_reason' => $reason,
            ])
            ->log('Access request rejected');
    }

    /**
     * Generate a temporary share link.
     */
    public function generateShareLink($durationInHours = 24)
    {
        $this->update([
            'share_link' => \Str::random(64),
            'share_link_expires_at' => now()->addHours($durationInHours),
        ]);
    }

    /**
     * Revoke the share link.
     */
    public function revokeShareLink()
    {
        $this->update([
            'share_link' => null,
            'share_link_expires_at' => null,
        ]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by requester.
     */
    public function scopeByRequester($query, $requesterId)
    {
        return $query->where('requester_id', $requesterId);
    }

    /**
     * Scope to filter by file.
     */
    public function scopeByFile($query, $fileId)
    {
        return $query->where('file_id', $fileId);
    }

    /**
     * Scope to get pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope to get expired requests.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
                    ->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get requests for files owned by a user.
     */
    public function scopeForUserFiles($query, $userId)
    {
        return $query->whereHas('file', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Get the file owner.
     */
    public function getFileOwnerAttribute()
    {
        return $this->file ? $this->file->user : null;
    }

    /**
     * Check if user can approve this request.
     */
    public function canBeApprovedBy($userId)
    {
        // File owner can approve
        if ($this->file && $this->file->user_id == $userId) {
            return true;
        }

        // Admin can approve
        $user = User::find($userId);
        if ($user && $user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-expire requests
        static::creating(function ($accessRequest) {
            if (!$accessRequest->expires_at) {
                $accessRequest->expires_at = now()->addDays(7); // Default 7 days
            }
        });

        // Log creation
        static::created(function ($accessRequest) {
            activity()
                ->performedOn($accessRequest)
                ->causedBy($accessRequest->requester_id)
                ->withProperties([
                    'file_id' => $accessRequest->file_id,
                    'reason' => $accessRequest->reason,
                ])
                ->log('Access request created');
        });
    }
}