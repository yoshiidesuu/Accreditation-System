@extends('layouts.user')

@section('title', 'Access Requests')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Access Requests</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Access Requests</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Requests</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('user.access-requests.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Search by reason or requester name...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Access Requests List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Access Requests</h6>
            <span class="badge badge-info">{{ $accessRequests->total() }} total</span>
        </div>
        <div class="card-body">
            @if($accessRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="accessRequestsTable">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Requester</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accessRequests as $request)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $request->file->parameter->title ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $request->file->college->name ?? 'N/A' }} - 
                                                {{ $request->file->academicYear->label ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            {{ $request->requester->name }}
                                            <br>
                                            <small class="text-muted">{{ $request->requester->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="reason-text" title="{{ $request->reason }}">
                                            {{ Str::limit($request->reason, 50) }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($request->status) {
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'expired' => 'secondary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $statusClass }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                        @if($request->status === 'approved' && $request->share_link_expires_at)
                                            <br>
                                            <small class="text-muted">
                                                Expires: {{ $request->share_link_expires_at->format('M d, Y') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            {{ $request->created_at->format('M d, Y') }}
                                            <br>
                                            <small class="text-muted">{{ $request->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('user.access-requests.show', $request) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($request->isPending() && $request->canBeApprovedBy(Auth::id()))
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="approveRequest({{ $request->id }})" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="rejectRequest({{ $request->id }})" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                            
                                            @if($request->status === 'approved' && $request->share_link)
                                <a href="{{ route('user.share.access', $request->share_link) }}" target="_blank" 
                                   class="btn btn-sm btn-outline-info" title="Access File">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $accessRequests->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Access Requests Found</h5>
                    <p class="text-muted">There are no access requests matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Request Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" method="POST">
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
                            <option value="30">30 days</option>
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
            <form id="rejectForm" method="POST">
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
.reason-text {
    max-width: 200px;
    word-wrap: break-word;
}

.badge {
    font-size: 0.75em;
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
function approveRequest(requestId) {
    const form = document.getElementById('approveForm');
    form.action = `/user/access-requests/${requestId}/approve`;
    
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectRequest(requestId) {
    const form = document.getElementById('rejectForm');
    form.action = `/user/access-requests/${requestId}/reject`;
    
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

$(document).ready(function() {
    // Initialize DataTable if needed
    if ($('#accessRequestsTable tbody tr').length > 10) {
        $('#accessRequestsTable').DataTable({
            "pageLength": 15,
            "order": [[4, "desc"]], // Sort by requested date
            "columnDefs": [
                { "orderable": false, "targets": [5] } // Disable sorting on actions column
            ]
        });
    }
});
</script>
@endpush