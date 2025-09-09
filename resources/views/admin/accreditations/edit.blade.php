@extends('layouts.admin')

@section('title', 'Edit Accreditation')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.accreditations.index') }}">Accreditations</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.accreditations.show', $accreditation) }}">{{ $accreditation->title }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Accreditation</h1>
            <p class="mb-0 text-muted">Modify accreditation details and settings</p>
        </div>
        <div>
            <a href="{{ route('admin.accreditations.show', $accreditation) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <h6><i class="fas fa-exclamation-triangle"></i> Please correct the following errors:</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.accreditations.update', $accreditation) }}">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $accreditation->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="college_id" class="form-label">College <span class="text-danger">*</span></label>
                                    <select class="form-select @error('college_id') is-invalid @enderror" 
                                            id="college_id" name="college_id" required>
                                        <option value="">Select College</option>
                                        @foreach($colleges as $college)
                                            <option value="{{ $college->id }}" 
                                                    {{ old('college_id', $accreditation->college_id) == $college->id ? 'selected' : '' }}>
                                                {{ $college->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('college_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                    <select class="form-select @error('academic_year_id') is-invalid @enderror" 
                                            id="academic_year_id" name="academic_year_id" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" 
                                                    {{ old('academic_year_id', $accreditation->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="institutional" {{ old('type', $accreditation->type) == 'institutional' ? 'selected' : '' }}>Institutional</option>
                                        <option value="program" {{ old('type', $accreditation->type) == 'program' ? 'selected' : '' }}>Program</option>
                                        <option value="specialized" {{ old('type', $accreditation->type) == 'specialized' ? 'selected' : '' }}>Specialized</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="planning" {{ old('status', $accreditation->status) == 'planning' ? 'selected' : '' }}>Planning</option>
                                        <option value="in_progress" {{ old('status', $accreditation->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="under_review" {{ old('status', $accreditation->status) == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                        <option value="completed" {{ old('status', $accreditation->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="accredited" {{ old('status', $accreditation->status) == 'accredited' ? 'selected' : '' }}>Accredited</option>
                                        <option value="denied" {{ old('status', $accreditation->status) == 'denied' ? 'selected' : '' }}>Denied</option>
                                        <option value="suspended" {{ old('status', $accreditation->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="accrediting_body" class="form-label">Accrediting Body <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('accrediting_body') is-invalid @enderror" 
                                           id="accrediting_body" name="accrediting_body" 
                                           value="{{ old('accrediting_body', $accreditation->accrediting_body) }}" required>
                                    @error('accrediting_body')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Brief description of the accreditation...">{{ old('description', $accreditation->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Timeline Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Timeline</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" 
                                           value="{{ old('start_date', $accreditation->start_date?->format('Y-m-d')) }}">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" 
                                           value="{{ old('end_date', $accreditation->end_date?->format('Y-m-d')) }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="visit_date" class="form-label">Visit Date</label>
                                    <input type="date" class="form-control @error('visit_date') is-invalid @enderror" 
                                           id="visit_date" name="visit_date" 
                                           value="{{ old('visit_date', $accreditation->visit_date?->format('Y-m-d')) }}">
                                    @error('visit_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="report_due_date" class="form-label">Report Due Date</label>
                                    <input type="date" class="form-control @error('report_due_date') is-invalid @enderror" 
                                           id="report_due_date" name="report_due_date" 
                                           value="{{ old('report_due_date', $accreditation->report_due_date?->format('Y-m-d')) }}">
                                    @error('report_due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Additional Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="requirements" class="form-label">Requirements</label>
                            <textarea class="form-control @error('requirements') is-invalid @enderror" 
                                      id="requirements" name="requirements" rows="4" 
                                      placeholder="List the requirements for this accreditation...">{{ old('requirements', $accreditation->requirements) }}</textarea>
                            @error('requirements')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Additional notes or comments...">{{ old('notes', $accreditation->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Assignment -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Assignment</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assigned Lead</label>
                            <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                    id="assigned_to" name="assigned_to">
                                <option value="">Select Lead</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                            {{ old('assigned_to', $accreditation->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $accreditation->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                        <small class="form-text text-muted">Inactive accreditations will be hidden from regular users.</small>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Accreditation
                            </button>
                            
                            <a href="{{ route('admin.accreditations.show', $accreditation) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Warning -->
                <div class="card border-warning shadow mb-4">
                    <div class="card-header bg-warning py-3">
                        <h6 class="m-0 font-weight-bold text-white">
                            <i class="fas fa-exclamation-triangle"></i> Important
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted mb-0">
                            <small>
                                Changes to accreditation details may affect related processes and reports. 
                                Please ensure all information is accurate before saving.
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Date validation
    $('#start_date, #end_date, #visit_date, #report_due_date').change(function() {
        validateDates();
    });
    
    function validateDates() {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());
        const visitDate = new Date($('#visit_date').val());
        const reportDueDate = new Date($('#report_due_date').val());
        
        // Clear previous custom validation
        $('.date-error').remove();
        
        // Validate end date is after start date
        if (startDate && endDate && endDate <= startDate) {
            $('#end_date').after('<div class="text-danger date-error"><small>End date must be after start date</small></div>');
        }
        
        // Validate visit date is within start and end date range
        if (startDate && endDate && visitDate) {
            if (visitDate < startDate || visitDate > endDate) {
                $('#visit_date').after('<div class="text-danger date-error"><small>Visit date should be between start and end dates</small></div>');
            }
        }
        
        // Validate report due date
        if (visitDate && reportDueDate && reportDueDate <= visitDate) {
            $('#report_due_date').after('<div class="text-danger date-error"><small>Report due date should be after visit date</small></div>');
        }
    }
    
    // Form submission validation
    $('form').submit(function(e) {
        if ($('.date-error').length > 0) {
            e.preventDefault();
            alert('Please fix the date validation errors before submitting.');
            return false;
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
}

.text-danger {
    font-weight: bold;
}

.date-error {
    margin-top: 0.25rem;
}

.border-warning {
    border-color: #f6c23e !important;
}

.bg-warning {
    background-color: #f6c23e !important;
}
</style>
@endpush