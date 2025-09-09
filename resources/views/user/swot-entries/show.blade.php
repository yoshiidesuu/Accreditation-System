@extends('layouts.user')

@section('title', 'SWOT Entry Details')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">SWOT Entry Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.swot-entries.index') }}">SWOT Entries</a></li>
                    <li class="breadcrumb-item active">{{ $swotEntry->title }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('user.swot-entries.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @can('update', $swotEntry)
                <a href="{{ route('user.swot-entries.edit', $swotEntry) }}" class="btn btn-primary me-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endcan
            @can('delete', $swotEntry)
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endcan
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

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 
                    @if($swotEntry->type === 'strength') bg-success text-white
                    @elseif($swotEntry->type === 'weakness') bg-danger text-white
                    @elseif($swotEntry->type === 'opportunity') bg-info text-white
                    @elseif($swotEntry->type === 'threat') bg-warning text-white
                    @endif">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            @if($swotEntry->type === 'strength')
                                <i class="fas fa-plus-circle"></i> Strength
                            @elseif($swotEntry->type === 'weakness')
                                <i class="fas fa-minus-circle"></i> Weakness
                            @elseif($swotEntry->type === 'opportunity')
                                <i class="fas fa-lightbulb"></i> Opportunity
                            @elseif($swotEntry->type === 'threat')
                                <i class="fas fa-exclamation-triangle"></i> Threat
                            @endif
                            - {{ $swotEntry->title }}
                        </h6>
                        <span class="badge 
                            @if($swotEntry->status === 'draft') badge-secondary
                            @elseif($swotEntry->status === 'submitted') badge-warning
                            @elseif($swotEntry->status === 'reviewed') badge-info
                            @elseif($swotEntry->status === 'approved') badge-success
                            @elseif($swotEntry->status === 'rejected') badge-danger
                            @endif">
                            {{ ucfirst($swotEntry->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>College:</strong>
                            <p class="mb-0">{{ $swotEntry->college->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Academic Year:</strong>
                            <p class="mb-0">{{ $swotEntry->academicYear->label }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            {!! nl2br(e($swotEntry->description)) !!}
                        </div>
                    </div>

                    @if($swotEntry->tags)
                        <div class="mb-3">
                            <strong>Tags:</strong>
                            <div class="mt-2">
                                @foreach(explode(',', $swotEntry->tags) as $tag)
                                    <span class="badge badge-light me-1">{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Analysis Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Analysis Details</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        @if($swotEntry->impact_level)
                            <div class="col-md-6">
                                <strong>Impact Level:</strong>
                                <p class="mb-0">
                                    <span class="badge 
                                        @if($swotEntry->impact_level === 'low') badge-secondary
                                        @elseif($swotEntry->impact_level === 'medium') badge-warning
                                        @elseif($swotEntry->impact_level === 'high') badge-danger
                                        @elseif($swotEntry->impact_level === 'critical') badge-dark
                                        @endif">
                                        {{ ucfirst($swotEntry->impact_level) }}
                                    </span>
                                </p>
                            </div>
                        @endif
                        @if($swotEntry->priority)
                            <div class="col-md-6">
                                <strong>Priority:</strong>
                                <p class="mb-0">
                                    <span class="badge 
                                        @if($swotEntry->priority === 'low') badge-secondary
                                        @elseif($swotEntry->priority === 'medium') badge-info
                                        @elseif($swotEntry->priority === 'high') badge-warning
                                        @elseif($swotEntry->priority === 'urgent') badge-danger
                                        @endif">
                                        {{ ucfirst($swotEntry->priority) }}
                                    </span>
                                </p>
                            </div>
                        @endif
                    </div>

                    @if($swotEntry->current_status)
                        <div class="mb-3">
                            <strong>Current Status:</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                {!! nl2br(e($swotEntry->current_status)) !!}
                            </div>
                        </div>
                    @endif

                    @if($swotEntry->proposed_action)
                        <div class="mb-3">
                            <strong>Proposed Action:</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                {!! nl2br(e($swotEntry->proposed_action)) !!}
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        @if($swotEntry->target_date)
                            <div class="col-md-6">
                                <strong>Target Date:</strong>
                                <p class="mb-0">{{ $swotEntry->target_date->format('M d, Y') }}</p>
                            </div>
                        @endif
                        @if($swotEntry->responsible_person)
                            <div class="col-md-6">
                                <strong>Responsible Person:</strong>
                                <p class="mb-0">{{ $swotEntry->responsible_person }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Supporting Evidence -->
            @if($swotEntry->evidence || $swotEntry->attachments)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Supporting Evidence</h6>
                    </div>
                    <div class="card-body">
                        @if($swotEntry->evidence)
                            <div class="mb-3">
                                <strong>Evidence/Data:</strong>
                                <div class="mt-2 p-3 bg-light rounded">
                                    {!! nl2br(e($swotEntry->evidence)) !!}
                                </div>
                            </div>
                        @endif

                        @if($swotEntry->attachments && count($swotEntry->attachments) > 0)
                            <div class="mb-3">
                                <strong>Attachments:</strong>
                                <div class="mt-2">
                                    @foreach($swotEntry->attachments as $attachment)
                                        @php
                                            $extension = pathinfo($attachment['name'], PATHINFO_EXTENSION);
                                            $iconClass = 'fas fa-file text-secondary';
                                            switch(strtolower($extension)) {
                                                case 'pdf': $iconClass = 'fas fa-file-pdf text-danger'; break;
                                                case 'doc':
                                                case 'docx': $iconClass = 'fas fa-file-word text-primary'; break;
                                                case 'xls':
                                                case 'xlsx': $iconClass = 'fas fa-file-excel text-success'; break;
                                                case 'ppt':
                                                case 'pptx': $iconClass = 'fas fa-file-powerpoint text-warning'; break;
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'png':
                                                case 'gif': $iconClass = 'fas fa-file-image text-info'; break;
                                            }
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="{{ $iconClass }} me-2"></i>
                                                <span>{{ $attachment['name'] }}</span>
                                                @if(isset($attachment['size']))
                                                    <small class="text-muted ms-2">({{ number_format($attachment['size'] / 1024, 2) }} KB)</small>
                                                @endif
                                            </div>
                                            <div>
                                                @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                                                    <button type="button" class="btn btn-sm btn-outline-info me-1 preview-image" 
                                                            data-src="{{ $attachment['path'] }}" data-name="{{ $attachment['name'] }}">
                                                        <i class="fas fa-eye"></i> Preview
                                                    </button>
                                                @endif
                                                <a href="{{ $attachment['path'] }}" class="btn btn-sm btn-outline-primary" download>
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Additional Notes -->
            @if($swotEntry->notes)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Additional Notes</h6>
                    </div>
                    <div class="card-body">
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($swotEntry->notes)) !!}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Review History -->
            @if($swotEntry->reviews && count($swotEntry->reviews) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Review History</h6>
                    </div>
                    <div class="card-body">
                        @foreach($swotEntry->reviews as $review)
                            <div class="review-item mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $review['reviewer_name'] ?? 'Unknown Reviewer' }}</strong>
                                        <span class="badge 
                                            @if($review['status'] === 'approved') badge-success
                                            @elseif($review['status'] === 'rejected') badge-danger
                                            @else badge-info
                                            @endif ms-2">
                                            {{ ucfirst($review['status']) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $review['reviewed_at'] ?? 'Date not available' }}</small>
                                </div>
                                @if(isset($review['comments']) && $review['comments'])
                                    <div class="review-comments">
                                        {!! nl2br(e($review['comments'])) !!}
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
                        <strong>Current Status:</strong>
                        <p class="mb-2">
                            <span class="badge badge-lg 
                                @if($swotEntry->status === 'draft') badge-secondary
                                @elseif($swotEntry->status === 'submitted') badge-warning
                                @elseif($swotEntry->status === 'reviewed') badge-info
                                @elseif($swotEntry->status === 'approved') badge-success
                                @elseif($swotEntry->status === 'rejected') badge-danger
                                @endif">
                                {{ ucfirst($swotEntry->status) }}
                            </span>
                        </p>
                    </div>

                    @if($swotEntry->status === 'draft' && auth()->user()->can('update', $swotEntry))
                        <div class="d-grid gap-2 mb-3">
                            <form action="{{ route('user.swot-entries.submit', $swotEntry) }}" method="POST" class="submit-form">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Submit for Review
                                </button>
                            </form>
                        </div>
                    @endif

                    @if(auth()->user()->hasRole(['coordinator']) && in_array($swotEntry->status, ['submitted', 'reviewed']))
                        <div class="d-grid gap-2 mb-3">
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="fas fa-clipboard-check"></i> Review Entry
                            </button>
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        @can('update', $swotEntry)
                            <a href="{{ route('user.swot-entries.edit', $swotEntry) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Entry
                            </a>
                        @endcan
                        
                        <a href="{{ route('user.swot-entries.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Meta Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Created by:</strong>
                        <p class="mb-0">{{ $swotEntry->user->name }}</p>
                    </div>
                    <div class="mb-2">
                        <strong>Created:</strong>
                        <p class="mb-0">{{ $swotEntry->created_at->format('M d, Y g:i A') }}</p>
                    </div>
                    @if($swotEntry->updated_at != $swotEntry->created_at)
                        <div class="mb-2">
                            <strong>Last Updated:</strong>
                            <p class="mb-0">{{ $swotEntry->updated_at->format('M d, Y g:i A') }}</p>
                        </div>
                    @endif
                    @if($swotEntry->submitted_at)
                        <div class="mb-2">
                            <strong>Submitted:</strong>
                            <p class="mb-0">{{ $swotEntry->submitted_at->format('M d, Y g:i A') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- SWOT Type Info -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">SWOT Type Information</h6>
                </div>
                <div class="card-body">
                    @if($swotEntry->type === 'strength')
                        <div class="p-3 rounded" style="background-color: #d4edda; border-left: 4px solid #28a745;">
                            <h6 class="text-success mb-2"><i class="fas fa-plus-circle"></i> Strength</h6>
                            <small class="text-muted">Internal positive factors that provide competitive advantages and should be leveraged for success.</small>
                        </div>
                    @elseif($swotEntry->type === 'weakness')
                        <div class="p-3 rounded" style="background-color: #f8d7da; border-left: 4px solid #dc3545;">
                            <h6 class="text-danger mb-2"><i class="fas fa-minus-circle"></i> Weakness</h6>
                            <small class="text-muted">Internal negative factors that need improvement or pose challenges to achieving objectives.</small>
                        </div>
                    @elseif($swotEntry->type === 'opportunity')
                        <div class="p-3 rounded" style="background-color: #d1ecf1; border-left: 4px solid #17a2b8;">
                            <h6 class="text-info mb-2"><i class="fas fa-lightbulb"></i> Opportunity</h6>
                            <small class="text-muted">External positive factors that can be exploited to gain advantages or achieve growth.</small>
                        </div>
                    @elseif($swotEntry->type === 'threat')
                        <div class="p-3 rounded" style="background-color: #fff3cd; border-left: 4px solid #ffc107;">
                            <h6 class="text-warning mb-2"><i class="fas fa-exclamation-triangle"></i> Threat</h6>
                            <small class="text-muted">External negative factors that could harm the organization and need to be mitigated.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@can('delete', $swotEntry)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this SWOT entry?</p>
                <p><strong>{{ $swotEntry->title }}</strong></p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('user.swot-entries.destroy', $swotEntry) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Entry</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- Review Modal -->
@if(auth()->user()->hasRole(['coordinator']) && in_array($swotEntry->status, ['submitted', 'reviewed']))
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('user.swot-entries.review', $swotEntry) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Review SWOT Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="review_status" class="form-label">Review Decision <span class="text-danger">*</span></label>
                        <select class="form-select" id="review_status" name="status" required>
                            <option value="">Select Decision</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                            <option value="reviewed">Request Revision</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="review_comments" class="form-label">Comments</label>
                        <textarea class="form-control" id="review_comments" name="comments" rows="4" 
                                  placeholder="Provide feedback or reasons for your decision"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewTitle">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagePreviewImg" src="" alt="Preview" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}

.review-item {
    transition: all 0.2s ease;
}

.review-item:hover {
    background-color: #f8f9fa;
}

.review-comments {
    background-color: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.375rem;
    border-left: 3px solid #007bff;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-group {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle image preview
    $('.preview-image').on('click', function() {
        const src = $(this).data('src');
        const name = $(this).data('name');
        
        $('#imagePreviewImg').attr('src', src);
        $('#imagePreviewTitle').text(name);
        $('#imagePreviewModal').modal('show');
    });
    
    // Handle submission confirmation
    $('.submit-form').on('submit', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to submit this SWOT entry for review? You will not be able to edit it after submission.')) {
            $(this).find('button[type="submit"]')
                .html('<i class="fas fa-spinner fa-spin"></i> Submitting...')
                .prop('disabled', true);
            
            this.submit();
        }
    });
    
    // Handle review form
    $('#reviewModal form').on('submit', function(e) {
        const status = $('#review_status').val();
        if (!status) {
            e.preventDefault();
            alert('Please select a review decision.');
            return;
        }
        
        $(this).find('button[type="submit"]')
            .html('<i class="fas fa-spinner fa-spin"></i> Processing...')
            .prop('disabled', true);
    });
    
    // Initialize tooltips
    $('[title]').tooltip();
});
</script>
@endpush