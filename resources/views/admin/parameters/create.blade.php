@extends('layouts.admin')

@section('title', 'Create Parameter')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>Create New Parameter
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.parameters.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.parameters.store') }}" method="POST" id="parameterForm">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-8">
                                <!-- Basic Information -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Basic Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="area_id" class="form-label required">Area</label>
                                                    <select class="form-select @error('area_id') is-invalid @enderror" 
                                                            id="area_id" name="area_id" required>
                                                        <option value="">Select Area</option>
                                                        @foreach($areas as $area)
                                                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                                                {{ $area->code }} - {{ $area->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('area_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="code" class="form-label required">Parameter Code</label>
                                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                                           id="code" name="code" value="{{ old('code') }}" 
                                                           placeholder="e.g., PARAM_001" required>
                                                    @error('code')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Unique identifier for this parameter</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="title" class="form-label required">Title</label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                                   id="title" name="title" value="{{ old('title') }}" 
                                                   placeholder="Parameter display name" required>
                                            @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" rows="3" 
                                                      placeholder="Optional description for this parameter">{{ old('description') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Field Configuration -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Field Configuration</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="type" class="form-label required">Field Type</label>
                                                    <select class="form-select @error('type') is-invalid @enderror" 
                                                            id="type" name="type" required>
                                                        <option value="">Select Field Type</option>
                                                        <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Text Input</option>
                                                        <option value="textarea" {{ old('type') == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                                        <option value="number" {{ old('type') == 'number' ? 'selected' : '' }}>Number</option>
                                                        <option value="date" {{ old('type') == 'date' ? 'selected' : '' }}>Date</option>
                                                        <option value="file" {{ old('type') == 'file' ? 'selected' : '' }}>File Upload</option>
                                                        <option value="select" {{ old('type') == 'select' ? 'selected' : '' }}>Select Dropdown</option>
                                                        <option value="checkbox" {{ old('type') == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                                        <option value="radio" {{ old('type') == 'radio' ? 'selected' : '' }}>Radio Buttons</option>
                                                    </select>
                                                    @error('type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="required" class="form-label">Required</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="required" name="required" value="1" 
                                                               {{ old('required') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="required">
                                                            This field is required
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="order" class="form-label">Display Order</label>
                                                    <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                                           id="order" name="order" value="{{ old('order', 0) }}" 
                                                           min="0" step="1">
                                                    @error('order')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Options Configuration (for select, checkbox, radio) -->
                                        <div id="optionsSection" class="mb-3" style="display: none;">
                                            <label class="form-label">Options</label>
                                            <div id="optionsList">
                                                <!-- Options will be added dynamically -->
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption()">
                                                <i class="fas fa-plus me-1"></i>Add Option
                                            </button>
                                            <div class="form-text">Add options for select, checkbox, or radio field types</div>
                                        </div>

                                        <!-- Validation Rules -->
                                        <div class="mb-3">
                                            <label class="form-label">Validation Rules</label>
                                            <div id="validationRules">
                                                <!-- Validation rules will be added dynamically based on field type -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Preview -->
                            <div class="col-md-4">
                                <div class="card sticky-top">
                                    <div class="card-header">
                                        <h5 class="mb-0">Preview</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="fieldPreview">
                                            <p class="text-muted">Select a field type to see preview</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.parameters.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Parameter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let optionCounter = 0;

// Field type change handler
document.getElementById('type').addEventListener('change', function() {
    const type = this.value;
    updateValidationRules(type);
    updateOptionsSection(type);
    updatePreview();
});

// Title change handler for auto-generating code
document.getElementById('title').addEventListener('input', function() {
    const title = this.value;
    const codeField = document.getElementById('code');
    
    if (!codeField.value || codeField.dataset.autoGenerated === 'true') {
        const code = title.toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '')
            .replace(/\s+/g, '_')
            .substring(0, 20);
        
        codeField.value = code;
        codeField.dataset.autoGenerated = 'true';
    }
    
    updatePreview();
});

// Manual code editing
document.getElementById('code').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});

// Other field change handlers
['description', 'required', 'order'].forEach(field => {
    document.getElementById(field).addEventListener('change', updatePreview);
});

function updateValidationRules(type) {
    const container = document.getElementById('validationRules');
    container.innerHTML = '';
    
    const commonRules = [
        { name: 'min_length', label: 'Minimum Length', type: 'number', applicable: ['text', 'textarea'] },
        { name: 'max_length', label: 'Maximum Length', type: 'number', applicable: ['text', 'textarea'] },
        { name: 'min_value', label: 'Minimum Value', type: 'number', applicable: ['number'] },
        { name: 'max_value', label: 'Maximum Value', type: 'number', applicable: ['number'] },
        { name: 'file_types', label: 'Allowed File Types', type: 'text', placeholder: 'jpg,png,pdf', applicable: ['file'] },
        { name: 'max_file_size', label: 'Max File Size (MB)', type: 'number', applicable: ['file'] }
    ];
    
    commonRules.forEach(rule => {
        if (rule.applicable.includes(type)) {
            const div = document.createElement('div');
            div.className = 'mb-2';
            div.innerHTML = `
                <label class="form-label">${rule.label}</label>
                <input type="${rule.type}" class="form-control form-control-sm" 
                       name="validation_rules[${rule.name}]" 
                       placeholder="${rule.placeholder || ''}">
            `;
            container.appendChild(div);
        }
    });
}

function updateOptionsSection(type) {
    const section = document.getElementById('optionsSection');
    const needsOptions = ['select', 'checkbox', 'radio'].includes(type);
    
    section.style.display = needsOptions ? 'block' : 'none';
    
    if (needsOptions && document.getElementById('optionsList').children.length === 0) {
        addOption(); // Add first option
    }
}

function addOption() {
    const container = document.getElementById('optionsList');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="options[${optionCounter}][label]" 
               placeholder="Option label" onchange="updatePreview()">
        <input type="text" class="form-control" name="options[${optionCounter}][value]" 
               placeholder="Option value" onchange="updatePreview()">
        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
    optionCounter++;
}

function removeOption(button) {
    button.parentElement.remove();
    updatePreview();
}

function updatePreview() {
    const preview = document.getElementById('fieldPreview');
    const type = document.getElementById('type').value;
    const title = document.getElementById('title').value || 'Parameter Title';
    const description = document.getElementById('description').value;
    const required = document.getElementById('required').checked;
    
    if (!type) {
        preview.innerHTML = '<p class="text-muted">Select a field type to see preview</p>';
        return;
    }
    
    let html = `
        <div class="mb-3">
            <label class="form-label">
                ${title}
                ${required ? '<span class="text-danger">*</span>' : ''}
            </label>
    `;
    
    switch (type) {
        case 'text':
            html += '<input type="text" class="form-control" placeholder="Enter text...">';
            break;
        case 'textarea':
            html += '<textarea class="form-control" rows="3" placeholder="Enter text..."></textarea>';
            break;
        case 'number':
            html += '<input type="number" class="form-control" placeholder="Enter number...">';
            break;
        case 'date':
            html += '<input type="date" class="form-control">';
            break;
        case 'file':
            html += '<input type="file" class="form-control">';
            break;
        case 'select':
            html += '<select class="form-select"><option>Choose option...</option>';
            document.querySelectorAll('#optionsList input[name*="[label]"]').forEach(input => {
                if (input.value) html += `<option>${input.value}</option>`;
            });
            html += '</select>';
            break;
        case 'checkbox':
            document.querySelectorAll('#optionsList input[name*="[label]"]').forEach(input => {
                if (input.value) {
                    html += `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox">
                            <label class="form-check-label">${input.value}</label>
                        </div>
                    `;
                }
            });
            break;
        case 'radio':
            document.querySelectorAll('#optionsList input[name*="[label]"]').forEach(input => {
                if (input.value) {
                    html += `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="preview_radio">
                            <label class="form-check-label">${input.value}</label>
                        </div>
                    `;
                }
            });
            break;
    }
    
    if (description) {
        html += `<div class="form-text">${description}</div>`;
    }
    
    html += '</div>';
    preview.innerHTML = html;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const type = document.getElementById('type').value;
    if (type) {
        updateValidationRules(type);
        updateOptionsSection(type);
        updatePreview();
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

.sticky-top {
    top: 1rem;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.input-group .form-control {
    border-right: 0;
}

.input-group .form-control:not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.input-group .form-control:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: 0;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

#fieldPreview {
    background-color: #f8f9fa;
    border: 1px dashed #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    min-height: 100px;
}
</style>
@endpush