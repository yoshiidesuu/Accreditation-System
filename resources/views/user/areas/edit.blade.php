@extends('user.layout')

@section('title', 'Edit Area - ' . $area->name)

@section('page-header')
@endsection

@section('page-title')
<div class="d-flex align-items-center">
    <i class="fas fa-edit me-2 text-primary"></i>
    Edit Area
</div>
@endsection

@section('page-description', 'Update area information and settings')

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('user.areas.show', $area) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Area
    </a>
    <a href="{{ route('user.areas.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-list me-1"></i>All Areas
    </a>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Area Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.areas.update', $area) }}" id="areaForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label required">Area Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $area->name) }}" 
                                       placeholder="Enter area name" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">A descriptive name for this area</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label required">Area Code</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code', $area->code) }}" 
                                       placeholder="Enter area code" required
                                       @if(!auth()->user()->hasRole('admin')) readonly @endif>
                                @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Unique identifier for this area
                                    @if(!auth()->user()->hasRole('admin'))
                                        <br><small class="text-warning">Only administrators can modify the area code</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Enter area description">{{ old('description', $area->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Optional description of this area</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parent_id" class="form-label">Parent Area</label>
                                <select class="form-select @error('parent_id') is-invalid @enderror" 
                                        id="parent_id" name="parent_id"
                                        @if(!auth()->user()->hasRole(['admin', 'coordinator'])) disabled @endif>
                                    <option value="">Select parent area (optional)</option>
                                    @foreach($parentAreas as $parentArea)
                                        @if($parentArea->id !== $area->id)
                                            <option value="{{ $parentArea->id }}" 
                                                    {{ old('parent_id', $area->parent_id) == $parentArea->id ? 'selected' : '' }}>
                                                {{ str_repeat('— ', $parentArea->level) }}{{ $parentArea->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Choose a parent area to create hierarchy
                                    @if(!auth()->user()->hasRole(['admin', 'coordinator']))
                                        <br><small class="text-warning">Only administrators and coordinators can modify hierarchy</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="level" class="form-label required">Level</label>
                                <select class="form-select @error('level') is-invalid @enderror" 
                                        id="level" name="level" required
                                        @if(!auth()->user()->hasRole(['admin', 'coordinator'])) disabled @endif>
                                    <option value="">Select level</option>
                                    <option value="1" {{ old('level', $area->level) == '1' ? 'selected' : '' }}>Level 1 (Main Area)</option>
                                    <option value="2" {{ old('level', $area->level) == '2' ? 'selected' : '' }}>Level 2 (Sub Area)</option>
                                    <option value="3" {{ old('level', $area->level) == '3' ? 'selected' : '' }}>Level 3 (Sub-Sub Area)</option>
                                </select>
                                @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Hierarchical level of this area
                                    @if(!auth()->user()->hasRole(['admin', 'coordinator']))
                                        <br><small class="text-warning">Only administrators and coordinators can modify level</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                       id="display_order" name="display_order" value="{{ old('display_order', $area->display_order) }}" 
                                       min="1" placeholder="1">
                                @error('display_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Order in which this area appears in lists</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="color" class="form-label">Color</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                           id="color" name="color" value="{{ old('color', $area->color ?? '#007bff') }}" 
                                           title="Choose area color">
                                    <input type="text" class="form-control" id="colorText" 
                                           value="{{ old('color', $area->color ?? '#007bff') }}" readonly>
                                </div>
                                @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Color used for visual identification</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" 
                                       {{ old('is_active', $area->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                    <br><small class="text-muted">Area is available for use</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="requires_approval" 
                                       name="requires_approval" value="1" 
                                       {{ old('requires_approval', $area->requires_approval) ? 'checked' : '' }}
                                       @if(!auth()->user()->hasRole(['admin', 'coordinator'])) disabled @endif>
                                <label class="form-check-label" for="requires_approval">
                                    <strong>Requires Approval</strong>
                                    <br><small class="text-muted">
                                        Changes to this area need approval
                                        @if(!auth()->user()->hasRole(['admin', 'coordinator']))
                                            <br><span class="text-warning">Only administrators and coordinators can modify this setting</span>
                                        @endif
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Meta Fields -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-tags me-2"></i>Additional Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="metaFields">
                                @if(old('meta', $area->meta))
                                    @foreach(old('meta', $area->meta) as $key => $value)
                                        <div class="meta-field mb-3">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" 
                                                           name="meta[{{ $loop->index }}][key]" 
                                                           value="{{ $key }}" 
                                                           placeholder="Field name">
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" 
                                                           name="meta[{{ $loop->index }}][value]" 
                                                           value="{{ $value }}" 
                                                           placeholder="Field value">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-meta">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="addMetaField">
                                <i class="fas fa-plus me-1"></i>Add Custom Field
                            </button>
                        </div>
                    </div>
                    
                    <!-- Change Summary -->
                    @if($area->children->count() > 0 || $area->parameters->count() > 0)
                        <div class="alert alert-info mt-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Impact Summary
                            </h6>
                            <p class="mb-2">This area has:</p>
                            <ul class="mb-0">
                                @if($area->children->count() > 0)
                                    <li>{{ $area->children->count() }} sub-area(s)</li>
                                @endif
                                @if($area->parameters->count() > 0)
                                    <li>{{ $area->parameters->count() }} parameter(s)</li>
                                @endif
                            </ul>
                            <p class="mt-2 mb-0"><small>Changes to this area may affect related items.</small></p>
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('user.areas.show', $area) }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Area
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Color picker sync
document.getElementById('color').addEventListener('input', function() {
    document.getElementById('colorText').value = this.value;
});

// Level and parent area logic
document.getElementById('parent_id').addEventListener('change', function() {
    const levelSelect = document.getElementById('level');
    if (this.value && !levelSelect.disabled) {
        // If parent is selected, get its level and set child level
        const selectedOption = this.options[this.selectedIndex];
        const parentLevel = selectedOption.textContent.split('—').length - 1;
        levelSelect.value = Math.min(parentLevel + 1, 3);
    } else if (!this.value && !levelSelect.disabled) {
        levelSelect.value = '1';
    }
});

document.getElementById('level').addEventListener('change', function() {
    const parentSelect = document.getElementById('parent_id');
    const selectedLevel = parseInt(this.value);
    
    if (!parentSelect.disabled) {
        // Filter parent options based on selected level
        Array.from(parentSelect.options).forEach(option => {
            if (option.value) {
                const optionLevel = option.textContent.split('—').length - 1;
                option.style.display = (optionLevel < selectedLevel) ? 'block' : 'none';
            }
        });
        
        // Reset parent selection if invalid
        if (parentSelect.value) {
            const selectedParentLevel = parentSelect.options[parentSelect.selectedIndex].textContent.split('—').length - 1;
            if (selectedParentLevel >= selectedLevel) {
                parentSelect.value = '';
            }
        }
    }
});

// Meta fields management
let metaFieldIndex = {{ old('meta', $area->meta) ? count(old('meta', $area->meta)) : 0 }};

document.getElementById('addMetaField').addEventListener('click', function() {
    const metaFields = document.getElementById('metaFields');
    const fieldHtml = `
        <div class="meta-field mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" 
                           name="meta[${metaFieldIndex}][key]" 
                           placeholder="Field name">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" 
                           name="meta[${metaFieldIndex}][value]" 
                           placeholder="Field value">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-meta">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    metaFields.insertAdjacentHTML('beforeend', fieldHtml);
    metaFieldIndex++;
});

// Remove meta field
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-meta')) {
        e.target.closest('.meta-field').remove();
    }
});

// Form validation
document.getElementById('areaForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const code = document.getElementById('code').value.trim();
    const level = document.getElementById('level').value;
    
    if (!name || !code || !level) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return false;
    }
    
    // Validate code format (alphanumeric and underscores only)
    const codePattern = /^[a-zA-Z0-9_]+$/;
    if (!codePattern.test(code)) {
        e.preventDefault();
        alert('Area code can only contain letters, numbers, and underscores.');
        return false;
    }
    
    // Confirm if making significant changes
    const hasChildren = {{ $area->children->count() }};
    const hasParameters = {{ $area->parameters->count() }};
    
    if ((hasChildren > 0 || hasParameters > 0) && 
        (document.getElementById('is_active').checked !== {{ $area->is_active ? 'true' : 'false' }} ||
         document.getElementById('level').value !== '{{ $area->level }}')) {
        if (!confirm('This area has related items. Are you sure you want to make these changes?')) {
            e.preventDefault();
            return false;
        }
    }
});

// Initialize level filtering on page load
document.addEventListener('DOMContentLoaded', function() {
    const levelSelect = document.getElementById('level');
    if (levelSelect.value) {
        levelSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush

@push('styles')
<style>
.required::after {
    content: ' *';
    color: #dc3545;
}

.form-control-color {
    width: 60px;
    height: 38px;
    border-radius: 0.375rem 0 0 0.375rem;
}

.meta-field {
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    border: 1px solid #dee2e6;
}

.remove-meta {
    width: 100%;
}

.form-check-label {
    cursor: pointer;
}

.form-check-input {
    cursor: pointer;
}

.form-check-input:disabled {
    cursor: not-allowed;
}

.form-control:disabled,
.form-select:disabled {
    background-color: #f8f9fa;
    opacity: 0.8;
}

.card-title {
    font-size: 1.1rem;
}

.form-label {
    font-weight: 600;
}

.btn {
    font-weight: 500;
}

.form-text {
    font-size: 0.875rem;
}

.alert-heading {
    font-size: 1rem;
}
</style>
@endpush