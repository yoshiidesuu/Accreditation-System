<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'accreditation_id',
        'parameter_content_id',
        'tagged_by',
        'notes',
    ];

    /**
     * Get the accreditation that owns the tag.
     */
    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    /**
     * Get the parameter content that is tagged.
     */
    public function parameterContent(): BelongsTo
    {
        return $this->belongsTo(ParameterContent::class);
    }

    /**
     * Get the user who tagged this content.
     */
    public function taggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tagged_by');
    }

    /**
     * Scope to filter by accreditation.
     */
    public function scopeByAccreditation($query, $accreditationId)
    {
        return $query->where('accreditation_id', $accreditationId);
    }

    /**
     * Scope to filter by parameter content.
     */
    public function scopeByParameterContent($query, $parameterContentId)
    {
        return $query->where('parameter_content_id', $parameterContentId);
    }

    /**
     * Scope to filter by tagged user.
     */
    public function scopeTaggedByUser($query, $userId)
    {
        return $query->where('tagged_by', $userId);
    }
}