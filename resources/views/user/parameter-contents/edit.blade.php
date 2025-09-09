@extends('layouts.user')

@section('title', 'Edit Parameter Content')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Parameter Content</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.parameter-contents.index') }}">Parameter Contents</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.parameter-contents.show', $parameterContent) }}">{{ $parameterContent->parameter->title ?? 'Details' }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('user.parameter-contents.show', $parameterContent) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Form -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Parameter Content Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.parameter-contents.update', $parameterContent) }}" method="POST" enctype="multipart/form-data" id="parameterContentForm">
                        @csrf
                        @method('PUT')

                        <!-- Parameter Info (Read-only) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Parameter:</label>
                                <p class="form-control-plaintext">{{ $parameterContent->parameter->title ?? 'N/A' }}</p>
                                @if($parameterContent->parameter->code)
                                    <small class="text-muted">({{ $parameterContent->parameter->code }})</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Area:</label>
                                <p class="form-control-plaintext">{{ $parameterContent->parameter->area->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        @if($parameterContent->parameter->description)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Parameter Description:</label>
                                <p class="form-control-plaintext text-muted">{{ $parameterContent->parameter->description }}</p>
                            </div>
                        @endif

                        <!-- Academic Year -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $parameterContent->academic_year_id) == $year->id ? 'selected' : '' }}>
                                            {{ $year->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('academic_year_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="college_id" class="form-label">College <span class="text-danger">*</span></label>
                                <select class="form-select @error('college_id') is-invalid @enderror" id="college_id" name="college_id" required>
                                    <option value="">Select College</option>
                                    @foreach($colleges as $college)
                                        <option value="{{ $college->id }}" {{ old('college_id', $parameterContent->college_id) == $college->id ? 'selected' : '' }}>
                                            {{ $college->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('college_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Dynamic Content Field -->
                        <div class="mb-3">
                            <label for="content" class="form-label">
                                Content 
                                @if($parameterContent->parameter->is_required ?? false)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            
                            @if($parameterContent->parameter->type === 'text')
                                <input type="text" 
                                       class="form-control @error('content') is-invalid @enderror" 
                                       id="content" 
                                       name="content" 
                                       value="{{ old('content', $parameterContent->content) }}"
                                       placeholder="{{ $parameterContent->parameter->placeholder ?? 'Enter text' }}"
                                       {{ ($parameterContent->parameter->is_required ?? false) ? 'required' : '' }}>
                            
                            @elseif($parameterContent->parameter->type === 'textarea')
                                <textarea class="form-control @error('content') is-invalid @enderror" 
                                          id="content" 
                                          name="content" 
                                          rows="5"
                                          placeholder="{{ $parameterContent->parameter->placeholder ?? 'Enter detailed content' }}"
                                          {{ ($parameterContent->parameter->is_required ?? false) ? 'required' : '' }}>{{ old('content', $parameterContent->content) }}</textarea>
                            
                            @elseif($parameterContent->parameter->type === 'number')
                                <input type="number" 
                                       class="form-control @error('content') is-invalid @enderror" 
                                       id="content" 
                                       name="content" 
                                       value="{{ old('content', $parameterContent->content) }}"
                                       placeholder="{{ $parameterContent->parameter->placeholder ?? 'Enter number' }}"
                                       {{ ($parameterContent->parameter->is_required ?? false) ? 'required' : '' }}>
                            
                            @elseif($parameterContent->parameter->type === 'email')
                                <input type="email" 
                                       class="form-control @error('content') is-invalid @enderror" 
                                       id="content" 
                                       name="content" 
                                       value="{{ old('content', $parameterContent->content) }}"
                                       placeholder="{{ $parameterContent->parameter->placeholder ?? 'Enter email address' }}"
                                       {{ ($parameterContent->parameter->is_required ?? false) ? 'required' : '' }}>
                            
                            @elseif($parameterContent->parameter->type === 'date')
                                <input type="date" 
                                       class="form-control @error('content') is-invalid @enderror" 
                                       id="content" 
                                       name="content" 
                                       value="{{ old('content', $parameterContent->content) }}"
                                       {{ ($parameterContent->parameter->is_required ?? false) ? 'required' : '' }}>
                            
                            @elseif($parameterContent->parameter->type === 'select')
                                <select class="form-select @error('content') is-invalid @enderror" 
                                        id="content" 
                                        name="content"
                                        {{ ($parameterContent->parameter->is_required ?? false) ? 'required' : '' }}>
                                    <option value="">Select an option</option>
                                    @if($parameterContent->parameter->options)
                                        @foreach($parameterContent->parameter->options as $option)
                                            <option value="{{ $option }}" {{ old('content', $parameterContent->content) == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            
                            @elseif($parameterContent->parameter->type === 'radio')
                                @if($parameterContent->parameter->options)
                                    @foreach($parameterContent->parameter->options as $option)
                                        <div class="form-check">
                                            <input class="form-check-input @error('content') is-invalid @enderror" 
                                                   type="radio" 
                                                   name="content" 
                                                   id="content_{{ $loop->index }}" 
                                                   value="{{ $option }}"
                                                   {{ old('content', $parameterContent->content) == $option ? 'checked' : '' }}
                                                   {{ ($parameterContent->parameter->is_required ?? false) ? 'required' : '' }}>
                                            <label class="form-check-label" for="content_{{ $loop->index }}">
                                                {{ $option }}
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            
                            @elseif($parameterContent->parameter->type === 'checkbox')
                                @if($parameterContent->parameter->options)
                                    @php
                                        $selectedValues = old('content', $parameterContent->content);
                                        if (is_string($selectedValues)) {
                                            $selectedValues = json_decode($selectedValues, true) ?? [];
                                        }
                                        $selectedValues = is_array($selectedValues) ? $selectedValues : [];
                                    @endphp
                                    @foreach($parameterContent->parameter->options as $option)
                                        <div class="form-check">
                                            <input class="form-check-input @error('content') is-invalid @enderror" 
                                                   type="checkbox" 
                                                   name="content[]" 
                                                   id="content_{{ $loop->index }}" 
                                                   value="{{ $option }}"
                                                   {{ in_array($option, $selectedValues) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="content_{{ $loop->index }}">
                                                {{ $option }}
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            
                            @elseif($parameterContent->parameter->type === 'file')
                                <input type="file" 
                                       class="form-control @error('attachments') is-invalid @enderror" 
                                       id="attachments" 
                                       name="attachments[]" 
                                       multiple
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif">
                                <small class="form-text text-muted">
                                    Allowed file types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF. Max size: 10MB per file.
                                </small>
                                
                                <!-- Show existing attachments -->
                                @if($parameterContent->attachments && count($parameterContent->attachments) > 0)
                                    <div class="mt-2">
                                        <label class="form-label fw-bold">Current Attachments:</label>
                                        <div id="currentAttachments">
                                            @foreach($parameterContent->attachments as $index => $attachment)
                                                <div class="attachment-item d-flex align-items-center justify-content-between mb-2 p-2 bg-light rounded">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-file me-2"></i>
                                                        <span>{{ $attachment['name'] }}</span>
                                                        <small class="text-muted ms-2">({{ number_format($attachment['size'] / 1024, 2) }} KB)</small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-attachment" data-index="{{ $index }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Preview new attachments -->
                                <div id="attachmentPreview" class="mt-2" style="display: none;">
                                    <label class="form-label fw-bold">New Attachments:</label>
                                    <div id="attachmentList"></div>
                                </div>
                            @endif
                            
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('attachments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3"
                                      placeholder="Add any additional notes or comments">{{ old('notes', $parameterContent->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        @if(auth()->user()->hasRole(['admin', 'coordinator']))
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="draft" {{ old('status', $parameterContent->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ old('status', $parameterContent->status) === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="approved" {{ old('status', $parameterContent->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status', $parameterContent->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <!-- Hidden fields for removed attachments -->
                        <input type="hidden" id="removedAttachments" name="removed_attachments" value="">

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('user.parameter-contents.show', $parameterContent) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <div>
                                <button type="submit" name="action" value="save" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                @if($parameterContent->status === 'draft')
                                    <button type="submit" name="action" value="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane"></i> Save & Submit
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Parameter Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Parameter Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Type:</small>
                        <p class="mb-0">{{ ucfirst($parameterContent->parameter->type ?? 'N/A') }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Required:</small>
                        <p class="mb-0">
                            @if($parameterContent->parameter->is_required ?? false)
                                <span class="badge badge-warning">Required</span>
                            @else
                                <span class="badge badge-secondary">Optional</span>
                            @endif
                        </p>
                    </div>
                    @if($parameterContent->parameter->validation_rules)
                        <div class="mb-2">
                            <small class="text-muted">Validation Rules:</small>
                            <p class="mb-0"><code>{{ $parameterContent->parameter->validation_rules }}</code></p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Current Status -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge badge-{{ $parameterContent->status === 'approved' ? 'success' : ($parameterContent->status === 'rejected' ? 'danger' : 'warning') }} fs-6">
                            {{ ucfirst($parameterContent->status) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Last updated:</small>
                        <p class="mb-0">{{ $parameterContent->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    @if($parameterContent->submitted_at)
                        <div class="mb-2">
                            <small class="text-muted">Submitted at:</small>
                            <p class="mb-0">{{ $parameterContent->submitted_at->format('M d, Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.attachment-item {
    border: 1px solid #dee2e6;
}

.badge.fs-6 {
    font-size: 0.875rem !important;
}

.form-check {
    margin-bottom: 0.5rem;
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
    let removedAttachments = [];
    
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
                    <div class="attachment-item d-flex align-items-center justify-content-between mb-2 p-2 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file me-2"></i>
                            <span>${file.name}</span>
                            <small class="text-muted ms-2">(${(file.size / 1024).toFixed(2)} KB)</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-new-attachment" data-index="${index}">
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
    
    // Handle removing existing attachments
    $(document).on('click', '.remove-attachment', function() {
        const index = $(this).data('index');
        removedAttachments.push(index);
        $('#removedAttachments').val(JSON.stringify(removedAttachments));
        $(this).closest('.attachment-item').fadeOut(300, function() {
            $(this).remove();
        });
    });
    
    // Handle removing new attachments
    $(document).on('click', '.remove-new-attachment', function() {
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
    $('#parameterContentForm').on('submit', function(e) {
        const action = $('button[type="submit"]:focus').val() || $('button[type="submit"][name="action"]:first').val();
        
        if (action === 'submit') {
            if (!confirm('Are you sure you want to submit this content for review? You won\'t be able to edit it until it\'s reviewed.')) {
                e.preventDefault();
                return false;
            }
        }
        
        // Validate required fields
        let isValid = true;
        const requiredFields = $(this).find('[required]');
        
        requiredFields.each(function() {
            if (!$(this).val() || ($(this).is(':checkbox') && !$(this).is(':checked'))) {
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
    
    // Handle checkbox validation for required checkboxes
    $('input[type="checkbox"][name="content[]"]').on('change', function() {
        const checkboxes = $('input[type="checkbox"][name="content[]"]');
        const isRequired = checkboxes.first().prop('required');
        
        if (isRequired) {
            const isChecked = checkboxes.is(':checked');
            if (isChecked) {
                checkboxes.removeClass('is-invalid');
            } else {
                checkboxes.addClass('is-invalid');
            }
        }
    });
});
</script>
@endpush