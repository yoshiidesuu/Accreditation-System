@extends('layouts.user')

@section('title', 'Edit Accreditation')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Accreditation</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.accreditations.index') }}">Accreditations</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.accreditations.show', $accreditation) }}">{{ $accreditation->title }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('user.accreditations.show', $accreditation) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('user.accreditations.update', $accreditation) }}" method="POST" enctype="multipart/form-data" id="accreditationForm">
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
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $accreditation->title) }}" 
                                           placeholder="Enter accreditation title" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Code</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code', $accreditation->code) }}" 
                                           placeholder="Auto-generated" readonly>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Auto-generated based on title</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Enter accreditation description">{{ old('description', $accreditation->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
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
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                    <select class="form-select @error('academic_year_id') is-invalid @enderror" 
                                            id="academic_year_id" name="academic_year_id" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" 
                                                    {{ old('academic_year_id', $accreditation->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                {{ $year->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accreditation Details -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Accreditation Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="accreditation_body" class="form-label">Accreditation Body</label>
                                    <input type="text" class="form-control @error('accreditation_body') is-invalid @enderror" 
                                           id="accreditation_body" name="accreditation_body" 
                                           value="{{ old('accreditation_body', $accreditation->accreditation_body) }}" 
                                           placeholder="e.g., ABET, NAAC, etc.">
                                    @error('accreditation_body')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="accreditation_level" class="form-label">Accreditation Level</label>
                                    <select class="form-select @error('accreditation_level') is-invalid @enderror" 
                                            id="accreditation_level" name="accreditation_level">
                                        <option value="">Select Level</option>
                                        <option value="institutional" {{ old('accreditation_level', $accreditation->accreditation_level) == 'institutional' ? 'selected' : '' }}>Institutional</option>
                                        <option value="program" {{ old('accreditation_level', $accreditation->accreditation_level) == 'program' ? 'selected' : '' }}>Program</option>
                                        <option value="department" {{ old('accreditation_level', $accreditation->accreditation_level) == 'department' ? 'selected' : '' }}>Department</option>
                                        <option value="course" {{ old('accreditation_level', $accreditation->accreditation_level) == 'course' ? 'selected' : '' }}>Course</option>
                                    </select>
                                    @error('accreditation_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="application_date" class="form-label">Application Date</label>
                                    <input type="date" class="form-control @error('application_date') is-invalid @enderror" 
                                           id="application_date" name="application_date" 
                                           value="{{ old('application_date', $accreditation->application_date?->format('Y-m-d')) }}">
                                    @error('application_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="review_date" class="form-label">Review Date</label>
                                    <input type="date" class="form-control @error('review_date') is-invalid @enderror" 
                                           id="review_date" name="review_date" 
                                           value="{{ old('review_date', $accreditation->review_date?->format('Y-m-d')) }}">
                                    @error('review_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                           id="expiry_date" name="expiry_date" 
                                           value="{{ old('expiry_date', $accreditation->expiry_date?->format('Y-m-d')) }}">
                                    @error('expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Attachments</h6>
                    </div>
                    <div class="card-body">
                        <!-- Current Attachments -->
                        @if($accreditation->attachments && count($accreditation->attachments) > 0)
                            <div class="mb-3">
                                <label class="form-label">Current Attachments:</label>
                                <div id="currentAttachments">
                                    @foreach($accreditation->attachments as $index => $attachment)
                                        <div class="attachment-item d-flex align-items-center justify-content-between p-2 bg-light rounded mb-2" data-index="{{ $index }}">
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $extension = pathinfo($attachment['name'], PATHINFO_EXTENSION);
                                                    $iconClass = match(strtolower($extension)) {
                                                        'pdf' => 'fas fa-file-pdf text-danger',
                                                        'doc', 'docx' => 'fas fa-file-word text-primary',
                                                        'xls', 'xlsx' => 'fas fa-file-excel text-success',
                                                        'ppt', 'pptx' => 'fas fa-file-powerpoint text-warning',
                                                        'jpg', 'jpeg', 'png', 'gif' => 'fas fa-file-image text-info',
                                                        default => 'fas fa-file text-secondary'
                                                    };
                                                @endphp
                                                <i class="{{ $iconClass }} me-2"></i>
                                                <span>{{ $attachment['name'] }}</span>
                                                <small class="text-muted ms-2">({{ number_format($attachment['size'] / 1024, 2) }} KB)</small>
                                            </div>
                                            <div>
                                                <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-attachment" data-index="{{ $index }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- New Attachments -->
                        <div class="mb-3">
                            <label for="attachments" class="form-label">Add New Attachments</label>
                            <input type="file" class="form-control @error('attachments.*') is-invalid @enderror" 
                                   id="attachments" name="attachments[]" multiple 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif">
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF. Max size: 10MB per file.
                            </small>
                        </div>

                        <!-- Attachment Preview -->
                        <div id="attachmentPreview" class="mt-3" style="display: none;">
                            <label class="form-label">New Attachments Preview:</label>
                            <div id="previewContainer"></div>
                        </div>

                        <!-- Hidden input for removed attachments -->
                        <input type="hidden" id="removedAttachments" name="removed_attachments" value="">
                    </div>
                </div>

                <!-- Notes -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Notes</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Enter any additional notes or comments">{{ old('notes', $accreditation->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status & Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Status & Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Current Status:</label>
                            <span class="badge badge-{{ 
                                $accreditation->status === 'approved' ? 'success' : 
                                ($accreditation->status === 'rejected' ? 'danger' : 
                                ($accreditation->status === 'under_review' ? 'info' : 'warning'))
                            }} fs-6">
                                {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                            </span>
                        </div>

                        @if(auth()->user()->hasRole(['admin', 'staff']))
                            <div class="mb-3">
                                <label for="status" class="form-label">Update Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status">
                                    <option value="draft" {{ old('status', $accreditation->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ old('status', $accreditation->status) == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="under_review" {{ old('status', $accreditation->status) == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="approved" {{ old('status', $accreditation->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status', $accreditation->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Accreditation
                            </button>
                            <a href="{{ route('user.accreditations.show', $accreditation) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Help</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-info-circle text-info me-2"></i>
                                <small>Fill in all required fields marked with <span class="text-danger">*</span></small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-calendar text-warning me-2"></i>
                                <small>Dates should be in chronological order (Application → Review → Expiry)</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-file text-primary me-2"></i>
                                <small>Upload relevant documents to support your accreditation</small>
                            </li>
                            <li>
                                <i class="fas fa-shield-alt text-success me-2"></i>
                                <small>Your data is automatically saved as you type</small>
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
.attachment-item {
    transition: all 0.2s ease;
}

.attachment-item:hover {
    background-color: #e9ecef !important;
}

.badge.fs-6 {
    font-size: 0.875rem !important;
}

.preview-item {
    transition: all 0.2s ease;
}

.preview-item:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .attachment-item {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .attachment-item > div:last-child {
        margin-top: 0.5rem;
        align-self: flex-end;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let removedAttachments = [];
    
    // Auto-generate code from title
    $('#title').on('input', function() {
        const title = $(this).val();
        const code = title.toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '')
            .replace(/\s+/g, '_')
            .substring(0, 20);
        $('#code').val(code);
    });
    
    // Handle attachment preview
    $('#attachments').on('change', function() {
        const files = this.files;
        const previewContainer = $('#previewContainer');
        const attachmentPreview = $('#attachmentPreview');
        
        previewContainer.empty();
        
        if (files.length > 0) {
            attachmentPreview.show();
            
            Array.from(files).forEach((file, index) => {
                const fileSize = (file.size / 1024).toFixed(2);
                const extension = file.name.split('.').pop().toLowerCase();
                
                let iconClass = 'fas fa-file text-secondary';
                switch(extension) {
                    case 'pdf': iconClass = 'fas fa-file-pdf text-danger'; break;
                    case 'doc':
                    case 'docx': iconClass = 'fas fa-file-word text-primary'; break;
                    case 'xls':
                    case 'xlsx': iconClass = 'fas fa-file-excel text-success'; break;
                    case 'ppt':
                    case 'pptx': iconClass = 'fas fa-file-powerpoint text-warning'; break;
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif': iconClass = 'fas fa-file-image text-info'; break;
                }
                
                const previewItem = $(`
                    <div class="preview-item d-flex align-items-center justify-content-between p-2 bg-light rounded mb-2">
                        <div class="d-flex align-items-center">
                            <i class="${iconClass} me-2"></i>
                            <span>${file.name}</span>
                            <small class="text-muted ms-2">(${fileSize} KB)</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-preview" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);
                
                previewContainer.append(previewItem);
            });
        } else {
            attachmentPreview.hide();
        }
    });
    
    // Remove attachment preview
    $(document).on('click', '.remove-preview', function() {
        const index = $(this).data('index');
        const fileInput = $('#attachments')[0];
        const dt = new DataTransfer();
        
        Array.from(fileInput.files).forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        fileInput.files = dt.files;
        $(this).closest('.preview-item').remove();
        
        if (fileInput.files.length === 0) {
            $('#attachmentPreview').hide();
        }
    });
    
    // Remove current attachment
    $('.remove-attachment').on('click', function() {
        const index = $(this).data('index');
        removedAttachments.push(index);
        $('#removedAttachments').val(JSON.stringify(removedAttachments));
        $(this).closest('.attachment-item').fadeOut(300, function() {
            $(this).remove();
        });
    });
    
    // Date validation
    function validateDates() {
        const applicationDate = new Date($('#application_date').val());
        const reviewDate = new Date($('#review_date').val());
        const expiryDate = new Date($('#expiry_date').val());
        
        let isValid = true;
        
        // Clear previous validation states
        $('#application_date, #review_date, #expiry_date').removeClass('is-invalid');
        $('.date-error').remove();
        
        if ($('#application_date').val() && $('#review_date').val()) {
            if (applicationDate >= reviewDate) {
                $('#review_date').addClass('is-invalid');
                $('#review_date').after('<div class="invalid-feedback date-error">Review date must be after application date</div>');
                isValid = false;
            }
        }
        
        if ($('#review_date').val() && $('#expiry_date').val()) {
            if (reviewDate >= expiryDate) {
                $('#expiry_date').addClass('is-invalid');
                $('#expiry_date').after('<div class="invalid-feedback date-error">Expiry date must be after review date</div>');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    $('#application_date, #review_date, #expiry_date').on('change', validateDates);
    
    // Form validation
    $('#accreditationForm').on('submit', function(e) {
        if (!validateDates()) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
            return false;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
        
        // Re-enable button after 10 seconds (fallback)
        setTimeout(() => {
            submitBtn.html(originalText).prop('disabled', false);
        }, 10000);
    });
    
    // Auto-save functionality (optional)
    let autoSaveTimeout;
    $('input, textarea, select').on('input change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // You can implement auto-save functionality here
            console.log('Auto-save triggered');
        }, 2000);
    });
    
    // Initialize tooltips
    $('[title]').tooltip();
});
</script>
@endpush