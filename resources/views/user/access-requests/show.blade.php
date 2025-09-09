@extends('layouts.user')

@section('title', 'Access Request Details')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Access Request Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.access-requests.index') }}">Access Requests</a></li>
                    <li class="breadcrumb-item active">Request #{{ $accessRequest->id }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('user.access-requests.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Request Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Request Information</h6>
                    @php
                        $statusClass = match($accessRequest->status) {
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'expired' => 'secondary',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge badge-{{ $statusClass }} badge-lg">
                        {{ ucfirst($accessRequest->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Requester</h6>
                            <div class="mb-3">
                                <strong>{{ $accessRequest->requester->name }}</strong><br>
                                <small class="text-muted">{{ $accessRequest->requester->email }}</small><br>
                                @if($accessRequest->requester->college)
                                    <small class="text-muted">{{ $accessRequest->requester->college->name }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Request Date</h6>
                            <div class="mb-3">
                                <strong>{{ $accessRequest->created_at->format('F d, Y') }}</strong><br>
                                <small class="text-muted">{{ $accessRequest->created_at->format('g:i A') }}</small><br>
                                <small class="text-muted">{{ $accessRequest->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Reason for Request</h6>
                        <div class="bg-light p-3 rounded">
                            {{ $accessRequest->reason }}
                        </div>
                    </div>
                    
                    @if($accessRequest->approver)
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">{{ $accessRequest->status === 'approved' ? 'Approved' : 'Rejected' }} By</h6>
                                <div class="mb-3">
                                    <strong>{{ $accessRequest->approver->name }}</strong><br>
                                    <small class="text-muted">{{ $accessRequest->approver->email }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">{{ $accessRequest->status === 'approved' ? 'Approval' : 'Rejection' }} Date</h6>
                                <div class="mb-3">
                                    <strong>{{ $accessRequest->updated_at->format('F d, Y') }}</strong><br>
                                    <small class="text-muted">{{ $accessRequest->updated_at->format('g:i A') }}</small>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($accessRequest->status === 'rejected' && $accessRequest->rejection_reason)
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Rejection Reason</h6>
                            <div class="bg-danger-light p-3 rounded border border-danger">
                                {{ $accessRequest->rejection_reason }}
                            </div>
                        </div>
                    @endif
                    
                    @if($accessRequest->status === 'approved' && $accessRequest->share_link)
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Access Information</h6>
                            <div class="bg-success-light p-3 rounded border border-success">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Access Granted</strong><br>
                                        @if($accessRequest->share_link_expires_at)
                                            <small class="text-muted">
                                                Expires: {{ $accessRequest->share_link_expires_at->format('F d, Y g:i A') }}
                                                ({{ $accessRequest->share_link_expires_at->diffForHumans() }})
                                            </small>
                                        @endif
                                    </div>
                                    <a href="{{ $accessRequest->share_link }}" target="_blank" class="btn btn-success btn-sm">
                                        <i class="fas fa-external-link-alt"></i> Access File
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- File Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Requested File</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-2">{{ $accessRequest->file->parameter->title ?? 'N/A' }}</h5>
                            <div class="mb-3">
                                <span class="badge badge-info me-2">{{ $accessRequest->file->college->name ?? 'N/A' }}</span>
                                <span class="badge badge-secondary me-2">{{ $accessRequest->file->academicYear->label ?? 'N/A' }}</span>
                                @if($accessRequest->file->area)
                                    <span class="badge badge-outline-primary">{{ $accessRequest->file->area->name }}</span>
                                @endif
                            </div>
                            
                            @if($accessRequest->file->description)
                                <p class="text-muted mb-3">{{ $accessRequest->file->description }}</p>
                            @endif
                            
                            <div class="row text-sm">
                                <div class="col-sm-6">
                                    <strong>Uploaded:</strong> {{ $accessRequest->file->created_at->format('M d, Y') }}
                                </div>
                                <div class="col-sm-6">
                                    <strong>Last Updated:</strong> {{ $accessRequest->file->updated_at->format('M d, Y') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            @if(Auth::user()->can('view', $accessRequest->file))
                                <a href="{{ route('user.parameter-contents.show', $accessRequest->file) }}" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i> View File
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions -->
            @if($accessRequest->isPending() && $accessRequest->canBeApprovedBy(Auth::id()))
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" onclick="approveRequest({{ $accessRequest->id }})">
                                <i class="fas fa-check"></i> Approve Request
                            </button>
                            <button type="button" class="btn btn-danger" onclick="rejectRequest({{ $accessRequest->id }})">
                                <i class="fas fa-times"></i> Reject Request
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Request Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Request Submitted</h6>
                                <p class="mb-1 text-sm">{{ $accessRequest->created_at->format('F d, Y g:i A') }}</p>
                                <p class="text-muted text-sm mb-0">by {{ $accessRequest->requester->name }}</p>
                            </div>
                        </div>
                        
                        @if($accessRequest->status !== 'pending')
                            <div class="timeline-item">
                                @php
                                    $markerClass = $accessRequest->status === 'approved' ? 'bg-success' : 'bg-danger';
                                @endphp
                                <div class="timeline-marker {{ $markerClass }}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Request {{ ucfirst($accessRequest->status) }}</h6>
                                    <p class="mb-1 text-sm">{{ $accessRequest->updated_at->format('F d, Y g:i A') }}</p>
                                    @if($accessRequest->approver)
                                        <p class="text-muted text-sm mb-0">by {{ $accessRequest->approver->name }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        @if($accessRequest->status === 'approved' && $accessRequest->share_link_expires_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Access Expires</h6>
                                    <p class="mb-1 text-sm">{{ $accessRequest->share_link_expires_at->format('F d, Y g:i A') }}</p>
                                    <p class="text-muted text-sm mb-0">{{ $accessRequest->share_link_expires_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Request Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" method="POST" action="{{ route('user.access-requests.approve', $accessRequest) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Approve Access Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this access request?</p>
                    <div class="mb-3">
                        <label for="share_link_duration" class="form-label">Access Duration (days)</label>
                        <select class="form-select" id="share_link_duration" name="share_link_duration">
                            <option value="7">7 days</option>
                            <option value="14">14 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="90">90 days</option>
                        </select>
                        <div class="form-text">How long should the requester have access to the file?</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Request Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST" action="{{ route('user.access-requests.reject', $accessRequest) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Access Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject this access request?</p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason for Rejection (Optional)</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" 
                                  placeholder="Provide a reason for rejecting this request..."></textarea>
                        <div class="form-text">This reason will be sent to the requester.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}

.bg-success-light {
    background-color: #d4edda;
}

.bg-danger-light {
    background-color: #f8d7da;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 20px;
    width: 2px;
    height: calc(100% + 10px);
    background-color: #e3e6f0;
}

.timeline-marker {
    position: absolute;
    left: -26px;
    top: 4px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e3e6f0;
}

.timeline-content h6 {
    font-size: 0.9rem;
    font-weight: 600;
}

.text-sm {
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
function approveRequest(requestId) {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectRequest(requestId) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endpush