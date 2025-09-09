@extends('layouts.user')

@section('title', 'Add SWOT Entry')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Add SWOT Entry</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.swot-entries.index') }}">SWOT Entries</a></li>
                    <li class="breadcrumb-item active">Add Entry</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('user.swot-entries.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
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

    <form action="{{ route('user.swot-entries.store') }}" method="POST" enctype="multipart/form-data" id="swotEntryForm">
        @csrf
        
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
                                           id="title" name="title" value="{{ old('title') }}" 
                                           placeholder="Enter SWOT entry title" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="type" class="form-label">SWOT Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="strength" {{ old('type') == 'strength' ? 'selected' : '' }}>Strength</option>
                                        <option value="weakness" {{ old('type') == 'weakness' ? 'selected' : '' }}>Weakness</option>
                                        <option value="opportunity" {{ old('type') == 'opportunity' ? 'selected' : '' }}>Opportunity</option>
                                        <option value="threat" {{ old('type') == 'threat' ? 'selected' : '' }}>Threat</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Provide a detailed description of this SWOT factor" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Explain the factor and its impact on the organization.</small>
                        </div>

                        <div class="row">
                            @if(auth()->user()->hasRole(['admin', 'staff', 'coordinator']))
                                <div class="col-md-6">
                                    <div class="mb-3">
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
                            @else
                                <input type="hidden" name="college_id" value="{{ auth()->user()->college_id }}">
                            @endif
                            <div class="col-md-6">
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
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analysis Details -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Analysis Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="impact_level" class="form-label">Impact Level</label>
                                    <select class="form-select @error('impact_level') is-invalid @enderror" 
                                            id="impact_level" name="impact_level">
                                        <option value="">Select Impact Level</option>
                                        <option value="low" {{ old('impact_level') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('impact_level') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('impact_level') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="critical" {{ old('impact_level') == 'critical' ? 'selected' : '' }}>Critical</option>
                                    </select>
                                    @error('impact_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" name="priority">
                                        <option value="">Select Priority</option>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="current_status" class="form-label">Current Status</label>
                            <textarea class="form-control @error('current_status') is-invalid @enderror" 
                                      id="current_status" name="current_status" rows="3" 
                                      placeholder="Describe the current state of this factor">{{ old('current_status') }}</textarea>
                            @error('current_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="proposed_action" class="form-label">Proposed Action</label>
                            <textarea class="form-control @error('proposed_action') is-invalid @enderror" 
                                      id="proposed_action" name="proposed_action" rows="3" 
                                      placeholder="Suggest actions to address this factor">{{ old('proposed_action') }}</textarea>
                            @error('proposed_action')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="target_date" class="form-label">Target Date</label>
                                    <input type="date" class="form-control @error('target_date') is-invalid @enderror" 
                                           id="target_date" name="target_date" value="{{ old('target_date') }}">
                                    @error('target_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="responsible_person" class="form-label">Responsible Person</label>
                                    <input type="text" class="form-control @error('responsible_person') is-invalid @enderror" 
                                           id="responsible_person" name="responsible_person" 
                                           value="{{ old('responsible_person') }}" 
                                           placeholder="Name or department responsible">
                                    @error('responsible_person')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supporting Evidence -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Supporting Evidence</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="evidence" class="form-label">Evidence/Data</label>
                            <textarea class="form-control @error('evidence') is-invalid @enderror" 
                                      id="evidence" name="evidence" rows="3" 
                                      placeholder="Provide data, statistics, or evidence supporting this SWOT factor">{{ old('evidence') }}</textarea>
                            @error('evidence')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="attachments" class="form-label">Attachments</label>
                            <input type="file" class="form-control @error('attachments.*') is-invalid @enderror" 
                                   id="attachments" name="attachments[]" multiple 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif">
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Upload supporting documents, charts, or images. Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF. Max size: 10MB per file.
                            </small>
                        </div>

                        <!-- Attachment Preview -->
                        <div id="attachmentPreview" class="mt-3" style="display: none;">
                            <label class="form-label">Attachment Preview:</label>
                            <div id="previewContainer"></div>
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Additional Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Any additional notes or comments">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                   id="tags" name="tags" value="{{ old('tags') }}" 
                                   placeholder="Enter tags separated by commas (e.g., strategic, financial, operational)">
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Use tags to categorize and easily find this entry later.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- SWOT Type Guide -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">SWOT Analysis Guide</h6>
                    </div>
                    <div class="card-body">
                        <div class="swot-guide">
                            <div class="swot-item mb-3 p-3 rounded" style="background-color: #d4edda; border-left: 4px solid #28a745;">
                                <h6 class="text-success mb-2"><i class="fas fa-plus-circle"></i> Strengths</h6>
                                <small class="text-muted">Internal positive factors that give advantages</small>
                            </div>
                            <div class="swot-item mb-3 p-3 rounded" style="background-color: #f8d7da; border-left: 4px solid #dc3545;">
                                <h6 class="text-danger mb-2"><i class="fas fa-minus-circle"></i> Weaknesses</h6>
                                <small class="text-muted">Internal negative factors that need improvement</small>
                            </div>
                            <div class="swot-item mb-3 p-3 rounded" style="background-color: #d1ecf1; border-left: 4px solid #17a2b8;">
                                <h6 class="text-info mb-2"><i class="fas fa-lightbulb"></i> Opportunities</h6>
                                <small class="text-muted">External positive factors to leverage</small>
                            </div>
                            <div class="swot-item p-3 rounded" style="background-color: #fff3cd; border-left: 4px solid #ffc107;">
                                <h6 class="text-warning mb-2"><i class="fas fa-exclamation-triangle"></i> Threats</h6>
                                <small class="text-muted">External negative factors to mitigate</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" name="action" value="save">
                                <i class="fas fa-save"></i> Save as Draft
                            </button>
                            @if(auth()->user()->hasRole(['coordinator', 'faculty']))
                                <button type="submit" class="btn btn-success" name="action" value="submit">
                                    <i class="fas fa-paper-plane"></i> Save & Submit for Review
                                </button>
                            @endif
                            <a href="{{ route('user.swot-entries.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                <small>Be specific and provide concrete examples</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-chart-bar text-info me-2"></i>
                                <small>Include quantifiable data when possible</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-users text-success me-2"></i>
                                <small>Consider stakeholder perspectives</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-clock text-primary me-2"></i>
                                <small>Think about both current and future implications</small>
                            </li>
                            <li>
                                <i class="fas fa-file-alt text-secondary me-2"></i>
                                <small>Attach supporting documents for credibility</small>
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
.swot-guide .swot-item {
    transition: all 0.2s ease;
}

.swot-guide .swot-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.preview-item {
    transition: all 0.2s ease;
}

.preview-item:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .swot-guide {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
    
    .swot-item {
        margin-bottom: 0 !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
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
    
    // Dynamic type-based styling
    $('#type').on('change', function() {
        const type = $(this).val();
        const card = $('.card-header').first();
        
        // Reset classes
        card.removeClass('bg-success bg-danger bg-info bg-warning text-white');
        
        // Apply type-specific styling
        switch(type) {
            case 'strength':
                card.addClass('bg-success text-white');
                break;
            case 'weakness':
                card.addClass('bg-danger text-white');
                break;
            case 'opportunity':
                card.addClass('bg-info text-white');
                break;
            case 'threat':
                card.addClass('bg-warning text-white');
                break;
        }
    });
    
    // Form validation
    $('#swotEntryForm').on('submit', function(e) {
        const submitBtn = $(this).find('button[type="submit"]:focus');
        const action = submitBtn.attr('name') === 'action' ? submitBtn.val() : 'save';
        
        // Add action to form if not already present
        if (!$(this).find('input[name="action"]').length) {
            $(this).append(`<input type="hidden" name="action" value="${action}">`);
        }
        
        // Show loading state
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
        
        // Re-enable button after 10 seconds (fallback)
        setTimeout(() => {
            submitBtn.html(originalText).prop('disabled', false);
        }, 10000);
    });
    
    // Tag input enhancement
    $('#tags').on('input', function() {
        let tags = $(this).val().split(',').map(tag => tag.trim()).filter(tag => tag.length > 0);
        $(this).val(tags.join(', '));
    });
    
    // Character count for textareas
    $('textarea').each(function() {
        const maxLength = $(this).attr('maxlength');
        if (maxLength) {
            const counterId = $(this).attr('id') + '_counter';
            $(this).after(`<small class="form-text text-muted" id="${counterId}">0/${maxLength} characters</small>`);
            
            $(this).on('input', function() {
                const currentLength = $(this).val().length;
                $(`#${counterId}`).text(`${currentLength}/${maxLength} characters`);
                
                if (currentLength > maxLength * 0.9) {
                    $(`#${counterId}`).addClass('text-warning');
                } else {
                    $(`#${counterId}`).removeClass('text-warning');
                }
            });
        }
    });
    
    // Initialize tooltips
    $('[title]').tooltip();
});
</script>
@endpush