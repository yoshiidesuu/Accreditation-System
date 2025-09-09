@extends('layouts.user')

@section('title', 'Parameter Content Details')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Parameter Content Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.parameter-contents.index') }}">Parameter Contents</a></li>
                    <li class="breadcrumb-item active">{{ $parameterContent->parameter->title ?? 'Details' }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group" role="group">
            @can('update', $parameterContent)
                <a href="{{ route('user.parameter-contents.edit', $parameterContent) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endcan
            @can('delete', $parameterContent)
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endcan
            <a href="{{ route('user.parameter-contents.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Parameter Content Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Content Information</h6>
                    <span class="badge badge-{{ $parameterContent->status === 'approved' ? 'success' : ($parameterContent->status === 'rejected' ? 'danger' : 'warning') }}">
                        {{ ucfirst($parameterContent->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Parameter:</label>
                                <p class="mb-0">{{ $parameterContent->parameter->title ?? 'N/A' }}</p>
                                @if($parameterContent->parameter->code)
                                    <small class="text-muted">({{ $parameterContent->parameter->code }})</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Area:</label>
                                <p class="mb-0">{{ $parameterContent->parameter->area->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Academic Year:</label>
                                <p class="mb-0">{{ $parameterContent->academicYear->label ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">College:</label>
                                <p class="mb-0">{{ $parameterContent->college->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    @if($parameterContent->parameter->description)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Parameter Description:</label>
                            <p class="mb-0">{{ $parameterContent->parameter->description }}</p>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">Content:</label>
                        <div class="content-display p-3 bg-light rounded">
                            @if($parameterContent->parameter->type === 'textarea')
                                {!! nl2br(e($parameterContent->content)) !!}
                            @elseif($parameterContent->parameter->type === 'select' || $parameterContent->parameter->type === 'radio')
                                {{ $parameterContent->content }}
                            @elseif($parameterContent->parameter->type === 'checkbox')
                                @php
                                    $values = is_array($parameterContent->content) ? $parameterContent->content : json_decode($parameterContent->content, true);
                                @endphp
                                @if(is_array($values))
                                    <ul class="mb-0">
                                        @foreach($values as $value)
                                            <li>{{ $value }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{ $parameterContent->content }}
                                @endif
                            @elseif($parameterContent->parameter->type === 'file')
                                @if($parameterContent->attachments && count($parameterContent->attachments) > 0)
                                    <div class="attachments-list">
                                        @foreach($parameterContent->attachments as $attachment)
                                            <div class="attachment-item d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file me-2"></i>
                                                    @can('view', $parameterContent)
                                                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="text-decoration-none">
                                                            {{ $attachment['name'] }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">{{ $attachment['name'] }}</span>
                                                    @endcan
                                                    <small class="text-muted ms-2">({{ number_format($attachment['size'] / 1024, 2) }} KB)</small>
                                                </div>
                                                @cannot('view', $parameterContent)
                                                    @can('parameter_contents.request_access')
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#requestAccessModal">
                                                            <i class="fas fa-key"></i> Request Access
                                                        </button>
                                                    @endcan
                                                @endcannot
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No files uploaded</p>
                                @endif
                            @else
                                {{ $parameterContent->content }}
                            @endif
                        </div>
                    </div>

                    @if($parameterContent->notes)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes:</label>
                            <p class="mb-0">{{ $parameterContent->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Review History -->
            @if($parameterContent->reviews && count($parameterContent->reviews) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Review History</h6>
                    </div>
                    <div class="card-body">
                        @foreach($parameterContent->reviews as $review)
                            <div class="review-item border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $review['reviewer_name'] ?? 'Unknown Reviewer' }}</h6>
                                        <span class="badge badge-{{ $review['status'] === 'approved' ? 'success' : 'danger' }}">
                                            {{ ucfirst($review['status']) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $review['reviewed_at'] ?? 'N/A' }}</small>
                                </div>
                                @if($review['comments'])
                                    <p class="mt-2 mb-0">{{ $review['comments'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
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
                        <label class="form-label fw-bold">Current Status:</label>
                        <span class="badge badge-{{ $parameterContent->status === 'approved' ? 'success' : ($parameterContent->status === 'rejected' ? 'danger' : 'warning') }} fs-6">
                            {{ ucfirst($parameterContent->status) }}
                        </span>
                    </div>

                    @if($parameterContent->status === 'draft')
                        @can('update', $parameterContent)
                            <form action="{{ route('user.parameter-contents.submit', $parameterContent) }}" method="POST" class="mb-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-paper-plane"></i> Submit for Review
                                </button>
                            </form>
                        @endcan
                    @endif

                    @can('update', $parameterContent)
                        <a href="{{ route('user.parameter-contents.edit', $parameterContent) }}" class="btn btn-primary btn-sm w-100 mb-2">
                            <i class="fas fa-edit"></i> Edit Content
                        </a>
                    @endcan

                    <a href="{{ route('user.parameter-contents.index') }}" class="btn btn-secondary btn-sm w-100">
                        <i class="fas fa-list"></i> Back to List
                    </a>
                </div>
            </div>

            <!-- Meta Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Meta Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Created by:</small>
                        <p class="mb-0">{{ $parameterContent->user->name ?? 'Unknown' }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Created at:</small>
                        <p class="mb-0">{{ $parameterContent->created_at->format('M d, Y H:i') }}</p>
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

            <!-- Parameter Details -->
            <div class="card shadow">
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
                    <div class="mb-2">
                        <small class="text-muted">Display Order:</small>
                        <p class="mb-0">{{ $parameterContent->parameter->display_order ?? 'N/A' }}</p>
                    </div>
                    @if($parameterContent->parameter->validation_rules)
                        <div class="mb-2">
                            <small class="text-muted">Validation Rules:</small>
                            <p class="mb-0"><code>{{ $parameterContent->parameter->validation_rules }}</code></p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@can('delete', $parameterContent)
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this parameter content?</p>
                <p class="text-danger"><strong>This action cannot be undone.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('user.parameter-contents.destroy', $parameterContent) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- Request Access Modal -->
@cannot('view', $parameterContent)
@can('parameter_contents.request_access')
<div class="modal fade" id="requestAccessModal" tabindex="-1" aria-labelledby="requestAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('user.access-requests.store') }}" method="POST">
                @csrf
                <input type="hidden" name="file_id" value="{{ $parameterContent->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestAccessModalLabel">Request Access</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Requesting access to:</label>
                        <p class="mb-0">{{ $parameterContent->parameter->title ?? 'Parameter Content' }}</p>
                        <small class="text-muted">{{ $parameterContent->college->name ?? 'N/A' }} - {{ $parameterContent->academicYear->label ?? 'N/A' }}</small>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-bold">Reason for Access <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" 
                                  placeholder="Please explain why you need access to this content..." required></textarea>
                        <div class="form-text">Provide a clear justification for your access request. This will be reviewed by the file owner or administrator.</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Your request will be sent to the file owner and administrators for review. You will be notified via email when a decision is made.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endcannot
@endsection

@push('styles')
<style>
.content-display {
    min-height: 100px;
    word-wrap: break-word;
}

.attachment-item {
    padding: 8px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.review-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.badge {
    font-size: 0.75em;
}

.badge.fs-6 {
    font-size: 0.875rem !important;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 0.25rem;
    }
    
    .btn-group .btn:last-child {
        margin-bottom: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle submit for review confirmation
    $('form[action*="submit"]').on('submit', function(e) {
        if (!confirm('Are you sure you want to submit this content for review? You won\'t be able to edit it until it\'s reviewed.')) {
            e.preventDefault();
        }
    });
    
    // Handle attachment previews
    $('.attachment-item a').on('click', function(e) {
        const href = $(this).attr('href');
        const fileName = $(this).text().trim();
        const extension = fileName.split('.').pop().toLowerCase();
        
        // For image files, show preview
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
            e.preventDefault();
            showImagePreview(href, fileName);
        }
    });
    
    function showImagePreview(src, title) {
        const modal = $(`
            <div class="modal fade" id="imagePreviewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${src}" class="img-fluid" alt="${title}">
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(modal);
        modal.modal('show');
        
        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });
    }
});
</script>
@endpush