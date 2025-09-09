@extends('user.layout')

@section('title', 'Edit Parameter')

@section('page-header')
@endsection

@section('page-title')
<div class="d-flex align-items-center">
    <i class="fas fa-edit me-2 text-warning"></i>
    Edit Parameter: {{ $parameter->title }}
</div>
@endsection

@section('page-description', 'Modify parameter configuration and settings')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('user.parameters.show', $parameter) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Parameter
    </a>
    <a href="{{ route('user.parameters.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-list me-1"></i>All Parameters
    </a>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cog me-2"></i>Parameter Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.parameters.update', $parameter) }}" id="parameterForm">
                    @csrf
                    @method('PATCH')
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Parameter Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title', $parameter->title) }}" 
                                       placeholder="Enter parameter title" required>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">Parameter Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code', $parameter->code) }}" 
                                       placeholder="parameter_code" required>
                                <div class="form-text">Unique identifier (lowercase, underscores allowed)</div>
                                @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Describe what this parameter is for...">{{ old('description', $parameter->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Area Selection -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="area_id" class="form-label">Area <span class="text-danger">*</span></label>
                                <select class="form-select @error('area_id') is-invalid @enderror" 
                                        id="area_id" name="area_id" required>
                                    <option value="">Select an area</option>
                                    @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ old('area_id', $parameter->area_id) == $area->id ? 'selected' : '' }}>
                                        {{ $area->name }} ({{ $area->college->name }})
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
                                <label for="type" class="form-label">Field Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type" required onchange="handleTypeChange()">
                                    <option value="">Select field type</option>
                                    <option value="text" {{ old('type', $parameter->type) == 'text' ? 'selected' : '' }}>Text Input</option>
                                    <option value="textarea" {{ old('type', $parameter->type) == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                    <option value="number" {{ old('type', $parameter->type) == 'number' ? 'selected' : '' }}>Number</option>
                                    <option value="email" {{ old('type', $parameter->type) == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="url" {{ old('type', $parameter->type) == 'url' ? 'selected' : '' }}>URL</option>
                                    <option value="date" {{ old('type', $parameter->type) == 'date' ? 'selected' : '' }}>Date</option>
                                    <option value="select" {{ old('type', $parameter->type) == 'select' ? 'selected' : '' }}>Select Dropdown</option>
                                    <option value="checkbox" {{ old('type', $parameter->type) == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                    <option value="radio" {{ old('type', $parameter->type) == 'radio' ? 'selected' : '' }}>Radio Buttons</option>
                                    <option value="file" {{ old('type', $parameter->type) == 'file' ? 'selected' : '' }}>File Upload</option>
                                </select>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Field Configuration -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="required" name="required" 
                                           value="1" {{ old('required', $parameter->required) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="required">
                                        Required Field
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" 
                                           value="1" {{ old('active', $parameter->active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="order" class="form-label">Display Order</label>
                                <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                       id="order" name="order" value="{{ old('order', $parameter->order) }}" min="0">
                                @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="placeholder" class="form-label">Placeholder Text</label>
                        <input type="text" class="form-control @error('placeholder') is-invalid @enderror" 
                               id="placeholder" name="placeholder" value="{{ old('placeholder', $parameter->placeholder) }}" 
                               placeholder="Enter placeholder text...">
                        @error('placeholder')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Options Section (for select, checkbox, radio) -->
                    <div id="optionsSection" class="mb-3" style="display: none;">
                        <label class="form-label">Options <span class="text-danger">*</span></label>
                        <div id="optionsContainer">
                            @php
                                $options = old('options', $parameter->options ?? []);
                            @endphp
                            @if($options && count($options) > 0)
                                @foreach($options as $index => $option)
                                <div class="option-item mb-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="options[]" 
                                               value="{{ is_array($option) ? ($option['label'] ?? $option['value'] ?? '') : $option }}" 
                                               placeholder="Option {{ $index + 1 }}">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOption()">
                            <i class="fas fa-plus me-1"></i>Add Option
                        </button>
                    </div>
                    
                    <!-- Validation Rules -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-shield-alt me-2"></i>Validation Rules
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="validationRules">
                                @php
                                    $validationRules = old('validation_rules', $parameter->validation_rules ?? []);
                                @endphp
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="min_length" class="form-label">Minimum Length</label>
                                            <input type="number" class="form-control" id="min_length" 
                                                   name="validation_rules[min_length]" value="{{ $validationRules['min_length'] ?? '' }}" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_length" class="form-label">Maximum Length</label>
                                            <input type="number" class="form-control" id="max_length" 
                                                   name="validation_rules[max_length]" value="{{ $validationRules['max_length'] ?? '' }}" min="0">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row" id="numberValidation" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="min_value" class="form-label">Minimum Value</label>
                                            <input type="number" class="form-control" id="min_value" 
                                                   name="validation_rules[min_value]" value="{{ $validationRules['min_value'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_value" class="form-label">Maximum Value</label>
                                            <input type="number" class="form-control" id="max_value" 
                                                   name="validation_rules[max_value]" value="{{ $validationRules['max_value'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row" id="fileValidation" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_file_size" class="form-label">Max File Size (MB)</label>
                                            <input type="number" class="form-control" id="max_file_size" 
                                                   name="validation_rules[max_file_size]" value="{{ $validationRules['max_file_size'] ?? 2 }}" min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="allowed_extensions" class="form-label">Allowed Extensions</label>
                                            <input type="text" class="form-control" id="allowed_extensions" 
                                                   name="validation_rules[allowed_extensions]" value="{{ $validationRules['allowed_extensions'] ?? '' }}" 
                                                   placeholder="pdf,doc,docx,jpg,png">
                                            <div class="form-text">Comma-separated list</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('user.parameters.show', $parameter) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Update Parameter
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
let optionCounter = {{ count($parameter->options ?? []) }};

function handleTypeChange() {
    const type = document.getElementById('type').value;
    const optionsSection = document.getElementById('optionsSection');
    const numberValidation = document.getElementById('numberValidation');
    const fileValidation = document.getElementById('fileValidation');
    
    // Show/hide options section
    if (['select', 'checkbox', 'radio'].includes(type)) {
        optionsSection.style.display = 'block';
        if (document.querySelectorAll('.option-item').length === 0) {
            addOption();
            addOption();
        }
    } else {
        optionsSection.style.display = 'none';
    }
    
    // Show/hide number validation
    if (type === 'number') {
        numberValidation.style.display = 'flex';
    } else {
        numberValidation.style.display = 'none';
    }
    
    // Show/hide file validation
    if (type === 'file') {
        fileValidation.style.display = 'flex';
    } else {
        fileValidation.style.display = 'none';
    }
}

function addOption() {
    optionCounter++;
    const container = document.getElementById('optionsContainer');
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-item mb-2';
    optionDiv.innerHTML = `
        <div class="input-group">
            <input type="text" class="form-control" name="options[]" 
                   placeholder="Option ${optionCounter}" required>
            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(optionDiv);
}

function removeOption(button) {
    const optionItem = button.closest('.option-item');
    optionItem.remove();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    handleTypeChange();
});
</script>
@endpush

@push('styles')
<style>
.option-item {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.card-title {
    font-size: 1rem;
}

.form-label {
    font-weight: 600;
}

.text-danger {
    font-weight: bold;
}
</style>
@endpush