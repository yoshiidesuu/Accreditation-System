@extends('layouts.admin')

@section('title', 'Edit Academic Year')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Edit Academic Year: {{ $academicYear->label }}</h3>
                    <div>
                        <a href="{{ route('admin.academic-years.show', $academicYear) }}" class="btn btn-info me-2">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.academic-years.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Academic Years
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.academic-years.update', $academicYear) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="label" class="form-label">Academic Year Label <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('label') is-invalid @enderror" 
                                           id="label" name="label" value="{{ old('label', $academicYear->label) }}" 
                                           placeholder="e.g., 2024-2025, AY 2024-2025" required>
                                    @error('label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Unique identifier for the academic year</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input @error('active') is-invalid @enderror" 
                                               type="checkbox" id="active" name="active" value="1" 
                                               {{ old('active', $academicYear->active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">
                                            Set as Active Academic Year
                                        </label>
                                        @error('active')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Only one academic year can be active at a time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" 
                                           value="{{ old('start_date', $academicYear->start_date->format('Y-m-d')) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" 
                                           value="{{ old('end_date', $academicYear->end_date->format('Y-m-d')) }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">End date must be after start date</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Date Range Preview -->
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info" id="datePreview">
                                    <h6><i class="fas fa-info-circle"></i> Academic Year Preview</h6>
                                    <div id="previewContent"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Current Status Info -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Current Status</label>
                                    <div class="form-control-plaintext">
                                        <span class="badge {{ $academicYear->status_badge }} me-2">{{ $academicYear->status_text }}</span>
                                        @if($academicYear->isCurrent() && !$academicYear->active)
                                            <small class="text-warning">Date-based current but not active</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Updated</label>
                                    <div class="form-control-plaintext">{{ $academicYear->updated_at->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.academic-years.show', $academicYear) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Academic Year
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update date range preview
function updatePreview() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const label = document.getElementById('label').value;
    const active = document.getElementById('active').checked;
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        const preview = document.getElementById('datePreview');
        const content = document.getElementById('previewContent');
        
        // Check if dates are valid
        const isValidRange = end > start;
        const alertClass = isValidRange ? 'alert-info' : 'alert-warning';
        
        preview.className = `alert ${alertClass}`;
        
        content.innerHTML = `
            <div class="row">
                <div class="col-md-3"><strong>Label:</strong> ${label || 'Not set'}</div>
                <div class="col-md-3"><strong>Duration:</strong> ${isValidRange ? diffDays + ' days' : 'Invalid range'}</div>
                <div class="col-md-3"><strong>Status:</strong> ${active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</div>
                <div class="col-md-3"><strong>Range:</strong> ${start.toLocaleDateString()} - ${end.toLocaleDateString()}</div>
            </div>
            ${!isValidRange ? '<div class="mt-2"><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> End date must be after start date</small></div>' : ''}
        `;
    }
}

// Auto-generate label from dates if label is empty or matches old pattern
function updateLabelFromDates() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const labelField = document.getElementById('label');
    const currentLabel = labelField.value;
    
    if (startDate && endDate) {
        const startYear = new Date(startDate).getFullYear();
        const endYear = new Date(endDate).getFullYear();
        const suggestedLabel = `${startYear}-${endYear}`;
        
        // Only auto-update if current label follows the year pattern or is empty
        const yearPattern = /^\d{4}-\d{4}$/;
        if (!currentLabel || yearPattern.test(currentLabel)) {
            labelField.value = suggestedLabel;
        }
    }
    
    updatePreview();
}

// Event listeners
document.getElementById('start_date').addEventListener('change', function() {
    updateLabelFromDates();
});

document.getElementById('end_date').addEventListener('change', function() {
    updateLabelFromDates();
});

document.getElementById('label').addEventListener('input', updatePreview);
document.getElementById('active').addEventListener('change', updatePreview);

// Initialize preview on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
});
</script>
@endsection