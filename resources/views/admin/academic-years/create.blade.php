@extends('layouts.admin')

@section('title', 'Create Academic Year')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Create New Academic Year</h3>
                    <a href="{{ route('admin.academic-years.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Academic Years
                    </a>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.academic-years.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="label" class="form-label">Academic Year Label <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('label') is-invalid @enderror" 
                                           id="label" name="label" value="{{ old('label') }}" 
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
                                               {{ old('active') ? 'checked' : '' }}>
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
                                           id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" value="{{ old('end_date') }}" required>
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
                                <div class="alert alert-info" id="datePreview" style="display: none;">
                                    <h6><i class="fas fa-info-circle"></i> Academic Year Preview</h6>
                                    <div id="previewContent"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Date Templates -->
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Quick Templates</label>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setCurrentAY()">
                                            Current AY ({{ date('Y') }}-{{ date('Y') + 1 }})
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setNextAY()">
                                            Next AY ({{ date('Y') + 1 }}-{{ date('Y') + 2 }})
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setPreviousAY()">
                                            Previous AY ({{ date('Y') - 1 }}-{{ date('Y') }})
                                        </button>
                                    </div>
                                    <div class="form-text">Click to auto-fill dates based on common academic year patterns</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.academic-years.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Academic Year
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-generate label from dates
function updateLabel() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const labelField = document.getElementById('label');
    
    if (startDate && endDate && !labelField.value) {
        const startYear = new Date(startDate).getFullYear();
        const endYear = new Date(endDate).getFullYear();
        labelField.value = `${startYear}-${endYear}`;
    }
    
    updatePreview();
}

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
        
        content.innerHTML = `
            <div class="row">
                <div class="col-md-3"><strong>Label:</strong> ${label || 'Not set'}</div>
                <div class="col-md-4"><strong>Duration:</strong> ${diffDays} days</div>
                <div class="col-md-3"><strong>Status:</strong> ${active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</div>
                <div class="col-md-2"><strong>Range:</strong> ${start.toLocaleDateString()} - ${end.toLocaleDateString()}</div>
            </div>
        `;
        
        preview.style.display = 'block';
    } else {
        document.getElementById('datePreview').style.display = 'none';
    }
}

// Quick template functions
function setCurrentAY() {
    const currentYear = new Date().getFullYear();
    const month = new Date().getMonth();
    
    // If we're in the first half of the year, use previous year as start
    const startYear = month < 6 ? currentYear - 1 : currentYear;
    const endYear = startYear + 1;
    
    document.getElementById('start_date').value = `${startYear}-08-01`;
    document.getElementById('end_date').value = `${endYear}-07-31`;
    document.getElementById('label').value = `${startYear}-${endYear}`;
    
    updatePreview();
}

function setNextAY() {
    const currentYear = new Date().getFullYear();
    const month = new Date().getMonth();
    
    const startYear = month < 6 ? currentYear : currentYear + 1;
    const endYear = startYear + 1;
    
    document.getElementById('start_date').value = `${startYear}-08-01`;
    document.getElementById('end_date').value = `${endYear}-07-31`;
    document.getElementById('label').value = `${startYear}-${endYear}`;
    
    updatePreview();
}

function setPreviousAY() {
    const currentYear = new Date().getFullYear();
    const month = new Date().getMonth();
    
    const startYear = month < 6 ? currentYear - 2 : currentYear - 1;
    const endYear = startYear + 1;
    
    document.getElementById('start_date').value = `${startYear}-08-01`;
    document.getElementById('end_date').value = `${endYear}-07-31`;
    document.getElementById('label').value = `${startYear}-${endYear}`;
    
    updatePreview();
}

// Event listeners
document.getElementById('start_date').addEventListener('change', updateLabel);
document.getElementById('end_date').addEventListener('change', updateLabel);
document.getElementById('label').addEventListener('input', updatePreview);
document.getElementById('active').addEventListener('change', updatePreview);

// Initialize preview on page load
document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endsection