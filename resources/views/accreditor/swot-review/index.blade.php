@extends('layouts.accreditor')

@section('title', 'SWOT Review Queue')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">SWOT Review Queue</h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" id="bulkApproveBtn" disabled>
                            <i class="fas fa-check"></i> Bulk Approve
                        </button>
                        <button type="button" class="btn btn-info" onclick="loadStats()">
                            <i class="fas fa-chart-bar"></i> Statistics
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <select name="college_id" class="form-select">
                                    <option value="">All Colleges</option>
                                    @foreach($colleges as $college)
                                        <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                            {{ $college->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="area_id" class="form-select">
                                    <option value="">All Areas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                            {{ $area->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach(App\Models\SwotEntry::getTypes() as $key => $type)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search description..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($swotEntries->count() > 0)
                        <form id="bulkForm" method="POST" action="{{ route('accreditor.swot-review.bulk-approve') }}">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <th>Type</th>
                                            <th>College</th>
                                            <th>Area</th>
                                            <th>Description</th>
                                            <th>Created By</th>
                                            <th>Created At</th>
                                            <th width="200">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($swotEntries as $entry)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="swot_ids[]" value="{{ $entry->id }}" class="form-check-input swot-checkbox">
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $entry->type === 'S' ? 'success' : ($entry->type === 'W' ? 'warning' : ($entry->type === 'O' ? 'info' : 'danger')) }}">
                                                        {{ $entry->getTypeName() }}
                                                    </span>
                                                </td>
                                                <td>{{ $entry->college->name }}</td>
                                                <td>{{ $entry->area->name }}</td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 300px;" title="{{ $entry->description }}">
                                                        {{ $entry->description }}
                                                    </div>
                                                </td>
                                                <td>{{ $entry->creator->name }}</td>
                                                <td>{{ $entry->created_at->format('M d, Y H:i') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('accreditor.swot-review.show', $entry) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="quickApprove({{ $entry->id }})">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quickReject({{ $entry->id }})">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Bulk Action Form -->
                            <div class="row mt-3" id="bulkActionForm" style="display: none;">
                                <div class="col-md-8">
                                    <textarea name="notes" class="form-control" placeholder="Optional notes for bulk approval..." rows="2"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-success me-2">
                                        <i class="fas fa-check"></i> Approve Selected
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="cancelBulkAction()">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $swotEntries->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No SWOT entries pending review</h5>
                            <p class="text-muted">All SWOT entries have been reviewed or no entries match your filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Modals -->
<div class="modal fade" id="quickApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Approve</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickApproveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="quickRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Reject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickRejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Statistics Modal -->
<div class="modal fade" id="statsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Statistics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="statsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.swot-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkActions();
});

// Individual checkbox change
document.querySelectorAll('.swot-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.swot-checkbox:checked');
    const bulkBtn = document.getElementById('bulkApproveBtn');
    const bulkForm = document.getElementById('bulkActionForm');
    
    if (checkedBoxes.length > 0) {
        bulkBtn.disabled = false;
        bulkBtn.textContent = `Bulk Approve (${checkedBoxes.length})`;
    } else {
        bulkBtn.disabled = true;
        bulkBtn.innerHTML = '<i class="fas fa-check"></i> Bulk Approve';
        bulkForm.style.display = 'none';
    }
}

// Bulk approve button click
document.getElementById('bulkApproveBtn').addEventListener('click', function() {
    const bulkForm = document.getElementById('bulkActionForm');
    bulkForm.style.display = bulkForm.style.display === 'none' ? 'block' : 'none';
});

function cancelBulkAction() {
    document.getElementById('bulkActionForm').style.display = 'none';
}

// Quick approve
function quickApprove(entryId) {
    const form = document.getElementById('quickApproveForm');
    form.action = `/accreditor/swot-review/${entryId}/approve`;
    new bootstrap.Modal(document.getElementById('quickApproveModal')).show();
}

// Quick reject
function quickReject(entryId) {
    const form = document.getElementById('quickRejectForm');
    form.action = `/accreditor/swot-review/${entryId}/reject`;
    new bootstrap.Modal(document.getElementById('quickRejectModal')).show();
}

// Load statistics
function loadStats() {
    const modal = new bootstrap.Modal(document.getElementById('statsModal'));
    modal.show();
    
    fetch('/accreditor/swot-review/stats')
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3>${data.pending}</h3>
                                <p class="mb-0">Pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>${data.approved}</h3>
                                <p class="mb-0">Approved</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3>${data.rejected}</h3>
                                <p class="mb-0">Rejected</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3>${data.pending + data.approved + data.rejected}</h3>
                                <p class="mb-0">Total</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6>By Type (Pending)</h6>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Strengths</span>
                                <span class="badge bg-success">${data.by_type.S || 0}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Weaknesses</span>
                                <span class="badge bg-warning">${data.by_type.W || 0}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Opportunities</span>
                                <span class="badge bg-info">${data.by_type.O || 0}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Threats</span>
                                <span class="badge bg-danger">${data.by_type.T || 0}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>By College (Pending)</h6>
                        <ul class="list-group" style="max-height: 200px; overflow-y: auto;">
                            ${Object.entries(data.by_college).map(([college, count]) => 
                                `<li class="list-group-item d-flex justify-content-between">
                                    <span>${college}</span>
                                    <span class="badge bg-primary">${count}</span>
                                </li>`
                            ).join('')}
                        </ul>
                    </div>
                </div>
            `;
            document.getElementById('statsContent').innerHTML = content;
        })
        .catch(error => {
            document.getElementById('statsContent').innerHTML = '<div class="alert alert-danger">Error loading statistics</div>';
        });
}
</script>
@endpush