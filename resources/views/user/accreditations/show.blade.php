@extends('layouts.user')

@section('title', 'Accreditation Details')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Accreditation Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.accreditations.index') }}">Accreditations</a></li>
                    <li class="breadcrumb-item active">{{ $accreditation->title }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group" role="group">
            @can('update', $accreditation)
                <a href="{{ route('user.accreditations.edit', $accreditation) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endcan
            @if(auth()->user()->hasRole(['admin', 'staff']) && $accreditation->status === 'submitted')
                <a href="{{ route('user.accreditations.evaluate', $accreditation) }}" class="btn btn-success">
                    <i class="fas fa-clipboard-check"></i> Evaluate
                </a>
            @endif
            @can('delete', $accreditation)
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endcan
            <a href="{{ route('user.accreditations.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                    <span class="badge badge-{{ 
                        $accreditation->status === 'approved' ? 'success' : 
                        ($accreditation->status === 'rejected' ? 'danger' : 
                        ($accreditation->status === 'under_review' ? 'info' : 'warning'))
                    }}">
                        {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Title:</label>
                                <p class="mb-0">{{ $accreditation->title }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Code:</label>
                                <p class="mb-0">{{ $accreditation->code ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    @if($accreditation->description)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description:</label>
                            <p class="mb-0">{{ $accreditation->description }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">College:</label>
                                <p class="mb-0">{{ $accreditation->college->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Academic Year:</label>
                                <p class="mb-0">{{ $accreditation->academicYear->label ?? 'N/A' }}</p>
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
                                <label class="form-label fw-bold">Accreditation Body:</label>
                                <p class="mb-0">{{ $accreditation->accreditation_body ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Accreditation Level:</label>
                                <p class="mb-0">
                                    @if($accreditation->accreditation_level)
                                        <span class="badge badge-info">{{ ucfirst($accreditation->accreditation_level) }}</span>
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Application Date:</label>
                                <p class="mb-0">
                                    @if($accreditation->application_date)
                                        {{ $accreditation->application_date->format('M d, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Review Date:</label>
                                <p class="mb-0">
                                    @if($accreditation->review_date)
                                        {{ $accreditation->review_date->format('M d, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Expiry Date:</label>
                                <p class="mb-0">
                                    @if($accreditation->expiry_date)
                                        {{ $accreditation->expiry_date->format('M d, Y') }}
                                        @if($accreditation->expiry_date->isPast())
                                            <span class="badge badge-danger ms-2">Expired</span>
                                        @elseif($accreditation->expiry_date->diffInDays() <= 90)
                                            <span class="badge badge-warning ms-2">Expiring Soon</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            @if($accreditation->attachments && count($accreditation->attachments) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Attachments</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($accreditation->attachments as $attachment)
                                <div class="col-md-6 mb-3">
                                    <div class="attachment-item d-flex align-items-center p-3 bg-light rounded border">
                                        <div class="me-3">
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
                                            <i class="{{ $iconClass }} fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $attachment['name'] }}</h6>
                                            <small class="text-muted">{{ number_format($attachment['size'] / 1024, 2) }} KB</small>
                                        </div>
                                        <div>
                                            <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($accreditation->notes)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Notes</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $accreditation->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Evaluation History -->
            @if($accreditation->evaluations && count($accreditation->evaluations) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Evaluation History</h6>
                    </div>
                    <div class="card-body">
                        @foreach($accreditation->evaluations as $evaluation)
                            <div class="evaluation-item border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $evaluation['evaluator_name'] ?? 'Unknown Evaluator' }}</h6>
                                        <span class="badge badge-{{ $evaluation['status'] === 'approved' ? 'success' : 'danger' }}">
                                            {{ ucfirst($evaluation['status']) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $evaluation['evaluated_at'] ?? 'N/A' }}</small>
                                </div>
                                @if($evaluation['comments'])
                                    <p class="mt-2 mb-0">{{ $evaluation['comments'] }}</p>
                                @endif
                                @if($evaluation['score'])
                                    <div class="mt-2">
                                        <small class="text-muted">Score: </small>
                                        <span class="fw-bold">{{ $evaluation['score'] }}/100</span>
                                    </div>
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
                        <span class="badge badge-{{ 
                            $accreditation->status === 'approved' ? 'success' : 
                            ($accreditation->status === 'rejected' ? 'danger' : 
                            ($accreditation->status === 'under_review' ? 'info' : 'warning'))
                        }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                        </span>
                    </div>

                    @if($accreditation->status === 'draft')
                        @can('update', $accreditation)
                            <form action="{{ route('user.accreditations.submit-report', $accreditation) }}" method="POST" class="mb-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-paper-plane"></i> Submit for Review
                                </button>
                            </form>
                        @endcan
                    @endif

                    @can('update', $accreditation)
                        <a href="{{ route('user.accreditations.edit', $accreditation) }}" class="btn btn-primary btn-sm w-100 mb-2">
                            <i class="fas fa-edit"></i> Edit Accreditation
                        </a>
                    @endcan

                    @if(auth()->user()->hasRole(['admin', 'staff']) && $accreditation->status === 'submitted')
                        <a href="{{ route('user.accreditations.evaluate', $accreditation) }}" class="btn btn-success btn-sm w-100 mb-2">
                            <i class="fas fa-clipboard-check"></i> Evaluate
                        </a>
                    @endif

                    <a href="{{ route('user.accreditations.index') }}" class="btn btn-secondary btn-sm w-100">
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
                        <p class="mb-0">{{ $accreditation->user->name ?? 'Unknown' }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Created at:</small>
                        <p class="mb-0">{{ $accreditation->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Last updated:</small>
                        <p class="mb-0">{{ $accreditation->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    @if($accreditation->submitted_at)
                        <div class="mb-2">
                            <small class="text-muted">Submitted at:</small>
                            <p class="mb-0">{{ $accreditation->submitted_at->format('M d, Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            @if($accreditation->application_date || $accreditation->review_date || $accreditation->expiry_date)
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Timeline</h6>
                    </div>
                    <div class="card-body">
                        @if($accreditation->application_date)
                            <div class="timeline-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Application</h6>
                                        <small class="text-muted">{{ $accreditation->application_date->format('M d, Y') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($accreditation->review_date)
                            <div class="timeline-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Review</h6>
                                        <small class="text-muted">{{ $accreditation->review_date->format('M d, Y') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if($accreditation->expiry_date)
                            <div class="timeline-item">
                                <div class="d-flex align-items-center">
                                    <div class="timeline-marker {{ $accreditation->expiry_date->isPast() ? 'bg-danger' : 'bg-warning' }}"></div>
                                    <div class="ms-3">
                                        <h6 class="mb-0">Expiry</h6>
                                        <small class="text-muted">{{ $accreditation->expiry_date->format('M d, Y') }}</small>
                                        @if($accreditation->expiry_date->isPast())
                                            <span class="badge badge-danger ms-2">Expired</span>
                                        @elseif($accreditation->expiry_date->diffInDays() <= 90)
                                            <span class="badge badge-warning ms-2">{{ $accreditation->expiry_date->diffInDays() }} days left</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@can('delete', $accreditation)
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the accreditation "{{ $accreditation->title }}"?</p>
                <p class="text-danger"><strong>This action cannot be undone.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('user.accreditations.destroy', $accreditation) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@push('styles')
<style>
.attachment-item {
    transition: all 0.2s ease;
}

.attachment-item:hover {
    background-color: #e9ecef !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.evaluation-item:last-child {
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

.timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
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
    
    .attachment-item {
        flex-direction: column;
        text-align: center;
    }
    
    .attachment-item .me-3 {
        margin-right: 0 !important;
        margin-bottom: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle submit for review confirmation
    $('form[action*="submit-report"]').on('submit', function(e) {
        if (!confirm('Are you sure you want to submit this accreditation for review? You won\'t be able to edit it until it\'s reviewed.')) {
            e.preventDefault();
        }
    });
    
    // Handle attachment previews for images
    $('.attachment-item a').on('click', function(e) {
        const href = $(this).attr('href');
        const fileName = $(this).closest('.attachment-item').find('h6').text().trim();
        const extension = fileName.split('.').pop().toLowerCase();
        
        // For image files, show preview
        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
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
    
    // Tooltip initialization
    $('[title]').tooltip();
});
</script>
@endpush