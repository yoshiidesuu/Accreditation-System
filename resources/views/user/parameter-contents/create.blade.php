@extends('layouts.user')

@section('title', 'Add Parameter Content')

@section('page-title')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">Add Parameter Content</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('user.parameter-contents.index') }}">Parameter Contents</a></li>
                    <li class="breadcrumb-item active">Add Content</li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        <a href="{{ route('user.parameter-contents.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('user.parameter-contents.store') }}" method="POST" enctype="multipart/form-data" id="contentForm">
        @csrf
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Content Information</h5>
                    </div>
                    <div class="card-body">
                        <!-- Parameter Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="area_id" class="form-label">Area <span class="text-danger">*</span></label>
                                <select class="form-select @error('area_id') is-invalid @enderror" 
                                        id="area_id" name="area_id" required>
                                    <option value="">Select Area</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                            {{ $area->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('area_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="parameter_id" class="form-label">Parameter <span class="text-danger">*</span></label>
                                <select class="form-select @error('parameter_id') is-invalid @enderror" 
                                        id="parameter_id" name="parameter_id" required disabled>
                                    <option value="">Select Parameter</option>
                                </select>
                                @error('parameter_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Parameter Details Display -->
                        <div id="parameterDetails" class="alert alert-info d-none mb-3">
                            <h6 class="alert-heading">Parameter Information</h6>
                            <div id="parameterInfo"></div>
                        </div>
                        
                        <!-- Academic Year -->
                        <div class="mb-3">
                            <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <select class="form-select @error('academic_year_id') is-invalid @enderror" 
                                    id="academic_year_id" name="academic_year_id" required>
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" 
                                            {{ old('academic_year_id', $activeAcademicYear?->id) == $year->id ? 'selected' : '' }}>
                                        {{ $year->label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Dynamic Content Fields -->
                        <div id="contentFields">
                            <!-- Fields will be populated dynamically based on parameter type -->
                        </div>
                        
                        <!-- Additional Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Any additional notes or comments...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Attachments -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Attachments</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="attachments" class="form-label">Upload Files</label>
                            <input type="file" class="form-control @error('attachments.*') is-invalid @enderror" 
                                   id="attachments" name="attachments[]" multiple 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif">
                            <div class="form-text">
                                Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF. Max size: 10MB per file.
                            </div>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div id="attachmentPreview" class="row g-3"></div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- College Info -->
                @if(!auth()->user()->hasRole('admin'))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">College Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="college-icon me-3">
                                    <i class="fas fa-university text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ auth()->user()->college->name }}</h6>
                                    <small class="text-muted">{{ auth()->user()->college->code }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">College Selection</h6>
                        </div>
                        <div class="card-body">
                            <label for="college_id" class="form-label">College <span class="text-danger">*</span></label>
                            <select class="form-select @error('college_id') is-invalid @enderror" 
                                    id="college_id" name="college_id" required>
                                <option value="">Select College</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}" {{ old('college_id') == $college->id ? 'selected' : '' }}>
                                        {{ $college->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('college_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif
                
                <!-- Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="statusDraft" 
                                   value="draft" {{ old('status', 'draft') == 'draft' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusDraft">
                                <strong>Save as Draft</strong>
                                <br><small class="text-muted">You can edit and submit later</small>
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="status" id="statusSubmit" 
                                   value="pending_review" {{ old('status') == 'pending_review' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusSubmit">
                                <strong>Submit for Review</strong>
                                <br><small class="text-muted">Send to coordinator for approval</small>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Content
                            </button>
                            <a href="{{ route('user.parameter-contents.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Help -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Need Help?</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-info-circle text-info me-2"></i>
                                <small>Select an area first to see available parameters</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                <small>Form fields will appear based on parameter type</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-file text-primary me-2"></i>
                                <small>Attach supporting documents if needed</small>
                            </li>
                            <li>
                                <i class="fas fa-clock text-secondary me-2"></i>
                                <small>Save as draft to continue editing later</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.college-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(13, 110, 253, 0.1);
    border-radius: 12px;
    font-size: 1.5rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.attachment-preview {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    background-color: #f8f9fa;
}

.attachment-preview .file-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
    border-radius: 8px;
    font-size: 1.5rem;
}

.dynamic-field {
    margin-bottom: 1rem;
}

.required-field::after {
    content: ' *';
    color: #dc3545;
}

@media (max-width: 768px) {
    .col-lg-4 {
        margin-top: 2rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const areaSelect = document.getElementById('area_id');
    const parameterSelect = document.getElementById('parameter_id');
    const contentFields = document.getElementById('contentFields');
    const parameterDetails = document.getElementById('parameterDetails');
    const parameterInfo = document.getElementById('parameterInfo');
    const attachmentsInput = document.getElementById('attachments');
    const attachmentPreview = document.getElementById('attachmentPreview');
    
    // Load parameters when area changes
    areaSelect.addEventListener('change', function() {
        const areaId = this.value;
        parameterSelect.innerHTML = '<option value="">Select Parameter</option>';
        parameterSelect.disabled = !areaId;
        contentFields.innerHTML = '';
        parameterDetails.classList.add('d-none');
        
        if (areaId) {
            fetch(`/api/areas/${areaId}/parameters`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(parameter => {
                        const option = document.createElement('option');
                        option.value = parameter.id;
                        option.textContent = `${parameter.title} (${parameter.code})`;
                        parameterSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading parameters:', error);
                });
        }
    });
    
    // Load parameter details and generate form fields
    parameterSelect.addEventListener('change', function() {
        const parameterId = this.value;
        contentFields.innerHTML = '';
        parameterDetails.classList.add('d-none');
        
        if (parameterId) {
            fetch(`/api/parameters/${parameterId}`)
                .then(response => response.json())
                .then(parameter => {
                    // Show parameter details
                    parameterInfo.innerHTML = `
                        <p><strong>Description:</strong> ${parameter.description || 'No description available'}</p>
                        <p><strong>Type:</strong> ${parameter.type}</p>
                        <p><strong>Required:</strong> ${parameter.is_required ? 'Yes' : 'No'}</p>
                        ${parameter.placeholder ? `<p><strong>Placeholder:</strong> ${parameter.placeholder}</p>` : ''}
                    `;
                    parameterDetails.classList.remove('d-none');
                    
                    // Generate form fields based on parameter type
                    generateContentFields(parameter);
                })
                .catch(error => {
                    console.error('Error loading parameter details:', error);
                });
        }
    });
    
    // Generate dynamic form fields based on parameter type
    function generateContentFields(parameter) {
        let fieldsHtml = '';
        
        switch (parameter.type) {
            case 'text':
                fieldsHtml = `
                    <div class="dynamic-field">
                        <label for="content_text" class="form-label ${parameter.is_required ? 'required-field' : ''}">
                            ${parameter.title}
                        </label>
                        <input type="text" class="form-control" id="content_text" name="content[text]" 
                               placeholder="${parameter.placeholder || ''}" ${parameter.is_required ? 'required' : ''}>
                    </div>
                `;
                break;
                
            case 'textarea':
                fieldsHtml = `
                    <div class="dynamic-field">
                        <label for="content_textarea" class="form-label ${parameter.is_required ? 'required-field' : ''}">
                            ${parameter.title}
                        </label>
                        <textarea class="form-control" id="content_textarea" name="content[textarea]" rows="4"
                                  placeholder="${parameter.placeholder || ''}" ${parameter.is_required ? 'required' : ''}></textarea>
                    </div>
                `;
                break;
                
            case 'number':
                fieldsHtml = `
                    <div class="dynamic-field">
                        <label for="content_number" class="form-label ${parameter.is_required ? 'required-field' : ''}">
                            ${parameter.title}
                        </label>
                        <input type="number" class="form-control" id="content_number" name="content[number]" 
                               placeholder="${parameter.placeholder || ''}" ${parameter.is_required ? 'required' : ''}>
                    </div>
                `;
                break;
                
            case 'select':
                let optionsHtml = '<option value="">Select an option</option>';
                if (parameter.options) {
                    parameter.options.forEach(option => {
                        optionsHtml += `<option value="${option}">${option}</option>`;
                    });
                }
                fieldsHtml = `
                    <div class="dynamic-field">
                        <label for="content_select" class="form-label ${parameter.is_required ? 'required-field' : ''}">
                            ${parameter.title}
                        </label>
                        <select class="form-select" id="content_select" name="content[select]" ${parameter.is_required ? 'required' : ''}>
                            ${optionsHtml}
                        </select>
                    </div>
                `;
                break;
                
            case 'checkbox':
                let checkboxHtml = '';
                if (parameter.options) {
                    parameter.options.forEach((option, index) => {
                        checkboxHtml += `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="checkbox_${index}" 
                                       name="content[checkbox][]" value="${option}">
                                <label class="form-check-label" for="checkbox_${index}">
                                    ${option}
                                </label>
                            </div>
                        `;
                    });
                }
                fieldsHtml = `
                    <div class="dynamic-field">
                        <label class="form-label ${parameter.is_required ? 'required-field' : ''}">
                            ${parameter.title}
                        </label>
                        <div class="mt-2">
                            ${checkboxHtml}
                        </div>
                    </div>
                `;
                break;
                
            case 'radio':
                let radioHtml = '';
                if (parameter.options) {
                    parameter.options.forEach((option, index) => {
                        radioHtml += `
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="radio_${index}" 
                                       name="content[radio]" value="${option}" ${parameter.is_required ? 'required' : ''}>
                                <label class="form-check-label" for="radio_${index}">
                                    ${option}
                                </label>
                            </div>
                        `;
                    });
                }
                fieldsHtml = `
                    <div class="dynamic-field">
                        <label class="form-label ${parameter.is_required ? 'required-field' : ''}">
                            ${parameter.title}
                        </label>
                        <div class="mt-2">
                            ${radioHtml}
                        </div>
                    </div>
                `;
                break;
                
            case 'date':
                fieldsHtml = `
                    <div class="dynamic-field">
                        <label for="content_date" class="form-label ${parameter.is_required ? 'required-field' : ''}">
                            ${parameter.title}
                        </label>
                        <input type="date" class="form-control" id="content_date" name="content[date]" 
                               ${parameter.is_required ? 'required' : ''}>
                    </div>
                `;
                break;
                
            case 'file':
                fieldsHtml = `
                    <div class="dynamic-field">
                        <label for="content_file" class="form-label ${parameter.is_required ? 'required-field' : ''}">
                            ${parameter.title}
                        </label>
                        <input type="file" class="form-control" id="content_file" name="content_files[]" 
                               multiple ${parameter.is_required ? 'required' : ''}>
                        <div class="form-text">Max size: 10MB per file</div>
                    </div>
                `;
                break;
        }
        
        contentFields.innerHTML = fieldsHtml;
    }
    
    // Handle attachment preview
    attachmentsInput.addEventListener('change', function() {
        attachmentPreview.innerHTML = '';
        
        Array.from(this.files).forEach((file, index) => {
            const fileExtension = file.name.split('.').pop().toLowerCase();
            const fileIcon = getFileIcon(fileExtension);
            
            const previewHtml = `
                <div class="col-md-6">
                    <div class="attachment-preview">
                        <div class="d-flex align-items-center">
                            <div class="file-icon me-3">
                                <i class="${fileIcon}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">${file.name}</h6>
                                <small class="text-muted">${formatFileSize(file.size)}</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            attachmentPreview.insertAdjacentHTML('beforeend', previewHtml);
        });
    });
    
    function getFileIcon(extension) {
        const iconMap = {
            'pdf': 'fas fa-file-pdf text-danger',
            'doc': 'fas fa-file-word text-primary',
            'docx': 'fas fa-file-word text-primary',
            'xls': 'fas fa-file-excel text-success',
            'xlsx': 'fas fa-file-excel text-success',
            'ppt': 'fas fa-file-powerpoint text-warning',
            'pptx': 'fas fa-file-powerpoint text-warning',
            'jpg': 'fas fa-file-image text-info',
            'jpeg': 'fas fa-file-image text-info',
            'png': 'fas fa-file-image text-info',
            'gif': 'fas fa-file-image text-info'
        };
        
        return iconMap[extension] || 'fas fa-file text-secondary';
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Form validation
    document.getElementById('contentForm').addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>
@endpush