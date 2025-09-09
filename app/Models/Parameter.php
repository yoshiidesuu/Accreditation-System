<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Parameter extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'code',
        'title',
        'description',
        'type',
        'validation_rules',
        'options',
        'required',
        'order',
        'active',
        'area_id',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'options' => 'array',
        'required' => 'boolean',
        'active' => 'boolean',
        'order' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relationships
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function parameterContents()
    {
        return $this->hasMany(ParameterContent::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('required', true);
    }

    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('title', 'asc');
    }

    // Accessors
    public function getFormattedValidationRulesAttribute()
    {
        if (!$this->validation_rules) {
            return [];
        }

        $rules = [];
        foreach ($this->validation_rules as $rule => $value) {
            if ($value === true) {
                $rules[] = $rule;
            } elseif (is_string($value) || is_numeric($value)) {
                $rules[] = "$rule:$value";
            }
        }

        return $rules;
    }

    public function getFormattedOptionsAttribute()
    {
        if (!$this->options || !in_array($this->type, ['select', 'checkbox', 'radio'])) {
            return [];
        }

        return $this->options;
    }

    public function getTypeDisplayAttribute()
    {
        $types = [
            'text' => 'Text Input',
            'textarea' => 'Text Area',
            'number' => 'Number Input',
            'date' => 'Date Input',
            'file' => 'File Upload',
            'select' => 'Select Dropdown',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Button',
        ];

        return $types[$this->type] ?? ucfirst($this->type);
    }

    // Methods
    public function hasContent()
    {
        return $this->parameterContents()->exists();
    }

    public function getContentForUser($userId)
    {
        return $this->parameterContents()
            ->where('user_id', $userId)
            ->first();
    }

    public function validateValue($value)
    {
        $rules = $this->formatted_validation_rules;
        
        if ($this->required && (is_null($value) || $value === '')) {
            return ['error' => 'This field is required.'];
        }

        // Type-specific validation
        switch ($this->type) {
            case 'number':
                if (!is_numeric($value) && $value !== null && $value !== '') {
                    return ['error' => 'This field must be a number.'];
                }
                break;
            case 'date':
                if ($value && !strtotime($value)) {
                    return ['error' => 'This field must be a valid date.'];
                }
                break;
            case 'select':
            case 'radio':
                if ($value && !in_array($value, array_keys($this->formatted_options))) {
                    return ['error' => 'Invalid option selected.'];
                }
                break;
            case 'checkbox':
                if ($value && is_array($value)) {
                    $validOptions = array_keys($this->formatted_options);
                    foreach ($value as $option) {
                        if (!in_array($option, $validOptions)) {
                            return ['error' => 'Invalid option selected.'];
                        }
                    }
                }
                break;
        }

        return ['success' => true];
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'code',
                'title',
                'description',
                'type',
                'validation_rules',
                'options',
                'required',
                'order',
                'active',
                'area_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Route key
    public function getRouteKeyName()
    {
        return 'id';
    }
}
