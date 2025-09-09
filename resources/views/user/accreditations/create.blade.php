@extends('layouts.user')

@section('title', 'Create Accreditation')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create New Accreditation</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.accreditations.index') }}">Accreditations</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('user.accreditations.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Form -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Accreditation Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.accreditations.store') }}" method="POST" enctype="multipart/form-data" id="accreditationForm">
                        @csrf

                        <!-- Basic Information -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}"
                                       placeholder="Enter accreditation title" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="code" class="form-label">Code</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code') }}"
                                       placeholder="ACC-001">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4"
                                      placeholder="Provide a detailed description of the accreditation">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- College and Academic Year -->
                        <div class="row mb-3">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                <select class="form-select @error('academic_year_id') is-invalid @enderror" 
                                        id="academic_year_id" name="academic_year_id" required>
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $defaultAcademicYear?->id) == $year->id ? 'selected' : '' }}>
                                            {{ $year->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('academic_year_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Accreditation Details -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="accreditation_body" class="form-label">Accreditation Body</label>
                                <input type="text" class="form-control @error('accreditation_body') is-invalid @enderror" 
                                       id="accreditation_body" name="accreditation_body" value="{{ old('accreditation_body') }}"
                                       placeholder="e.g., ABET, NAAC, etc.">
                                @error('accreditation_body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="accreditation_level" class="form-label">Accreditation Level</label>
                                <select class="form-select @error('accreditation_level') is-invalid @enderror" 
                                        id="accreditation_level" name="accreditation_level">
                                    <option value="">Select Level</option>
                                    <option value="institutional" {{ old('accreditation_level') === 'institutional' ? 'selected' : '' }}>Institutional</option>
                                    <option value="program" {{ old('accreditation_level') === 'program' ? 'selected' : '' }}>Program</option>
                                    <option value="department" {{ old('accreditation_level') === 'department' ? 'selected' : '' }}>Department</option>
                                    <option value="course" {{ old('accreditation_level') === 'course' ? 'selected' : '' }}>Course</option>
                                </select>
                                @error('accreditation_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="application_date" class="form-label">Application Date</label>
                                <input type="date" class="form-control @error('application_date') is-invalid @enderror" 
                                       id="application_date" name="application_date" value="{{ old('application_date') }}">
                                @error('application_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="review_date" class="form-label">Review Date</label>
                                <input type="date" class="form-control @error('review_date') is-invalid @enderror" 
                                       id="review_date" name="review_date" value="{{ old('review_date') }}">
                                @error('review_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div class="mb-3">
                            <label for="attachments" class="form-label">Attachments</label>
                            <input type="file" class="form-control @error('attachments') is-invalid @enderror" 
                                   id="attachments" name="attachments[]" multiple
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif">
                            <small class="form-text text-muted">
                                Allowed file types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF. Max size: 10MB per file.
                            </small>
                            @error('attachments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Attachment Preview -->
                            <div id="attachmentPreview" class="mt-2" style="display: none;">
                                <label class="form-label fw-bold">Selected Files:</label>
                                <div id="attachmentList"></div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3"
                                      placeholder="Add any additional notes or comments">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        @if(auth()->user()->hasRole(['admin', 'staff']))
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ old('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="under_review" {{ old('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('user.accreditations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <div>
                                <button type="submit" name="action" value="save" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Save as Draft
                                </button>
                                <button type="submit" name="action" value="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Save & Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Help & Guidelines -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">Required Information</h6>
                        <ul class="small text-muted mb-0">
                            <li>Title: Clear and descriptive name</li>
                            <li>College: Select the appropriate college</li>
                            <li>Academic Year: Choose the relevant year</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Accreditation Details</h6>
                        <ul class="small text-muted mb-0">
                            <li>Body: Name of accrediting organization</li>
                            <li>Level: Scope of accreditation</li>
                            <li>Dates: Important milestones</li>
                        </ul>
                    </div>
                    <div>
                        <h6 class="text-primary">File Attachments</h6>
                        <ul class="small text-muted mb-0">
                            <li>Upload relevant documents</li>
                            <li>Max 10MB per file</li>
                            <li>Multiple files supported</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge badge-warning">Draft</span>
                        <small class="text-muted d-block">Can be edited and updated</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-info">Submitted</span>
                        <small class="text-muted d-block">Under review by staff</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-primary">Under Review</span>
                        <small class="text-muted d-block">Being evaluated</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-success">Approved</span>
                        <small class="text-muted d-block">Accreditation approved</small>
                    </div>
                    <div>
                        <span class="badge badge-danger">Rejected</span>
                        <small class="text-muted d-block">Needs revision</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.attachment-item {
    padding: 8px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    margin-bottom: 0.5rem;
}

.badge {
    font-size: 0.75em;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
    }
    
    .d-flex.justify-content-between > div {
        margin-bottom: 1rem;
    }
    
    .d-flex.justify-content-between > div:last-child {
        margin-bottom: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle file input change
    $('#attachments').on('change', function() {
        const files = this.files;
        const preview = $('#attachmentPreview');
        const list = $('#attachmentList');
        
        if (files.length > 0) {
            preview.show();
            list.empty();
            
            Array.from(files).forEach((file, index) => {
                const item = $(`
                    <div class="attachment-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file me-2"></i>
                            <span>${file.name}</span>
                            <small class="text-muted ms-2">(${(file.size / 1024).toFixed(2)} KB)</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-attachment" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);
                list.append(item);
            });
        } else {
            preview.hide();
        }
    });
    
    // Handle removing attachments
    $(document).on('click', '.remove-attachment', function() {
        const index = $(this).data('index');
        const fileInput = $('#attachments')[0];
        const dt = new DataTransfer();
        
        Array.from(fileInput.files).forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        fileInput.files = dt.files;
        $(this).closest('.attachment-item').remove();
        
        if (fileInput.files.length === 0) {
            $('#attachmentPreview').hide();
        }
    });
    
    // Form validation
    $('#accreditationForm').on('submit', function(e) {
        const action = $('button[type="submit"]:focus').val() || $('button[type="submit"][name="action"]:first').val();
        
        if (action === 'submit') {
            if (!confirm('Are you sure you want to submit this accreditation for review?')) {
                e.preventDefault();
                return false;
            }
        }
        
        // Validate required fields
        let isValid = true;
        const requiredFields = $(this).find('[required]');
        
        requiredFields.each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
    
    // Clear validation on input change
    $('input, select, textarea').on('change input', function() {
        $(this).removeClass('is-invalid');
    });
    
    // Auto-generate code from title
    $('#title').on('input', function() {
        const title = $(this).val();
        const code = title.toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '')
            .replace(/\s+/g, '-')
            .substring(0, 10);
        
        if (code && !$('#code').val()) {
            $('#code').val('ACC-' + code);
        }
    });
    
    // Date validation
    $('#application_date, #review_date, #expiry_date').on('change', function() {
        const applicationDate = new Date($('#application_date').val());
        const reviewDate = new Date($('#review_date').val());
        const expiryDate = new Date($('#expiry_date').val());
        
        if (applicationDate && reviewDate && applicationDate > reviewDate) {
            alert('Review date should be after application date.');
            $('#review_date').val('');
        }
        
        if (reviewDate && expiryDate && reviewDate > expiryDate) {
            alert('Expiry date should be after review date.');
            $('#expiry_date').val('');
        }
    });
});
</script>
@endpush