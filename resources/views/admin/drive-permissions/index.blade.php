@extends('layouts.admin')

@section('title', 'Google Drive Permissions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Google Drive Permission Requests</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-info" onclick="loadStats()">
                            <i class="fas fa-chart-bar"></i> Statistics
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="showCleanupModal()">
                            <i class="fas fa-broom"></i> Cleanup
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.drive-permissions.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="college_id" class="form-label">College</label>
                            <select name="college_id" id="college_id" class="form-select">
                                <option value="">All Colleges</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                        {{ $college->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Search by file ID, link, parameter, or user..." 
                                   value="{{ request('search') }}">
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

                <!-- Bulk Actions -->
                <div class="card-body border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll">
                                    Select All
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('grant')" disabled id="bulkGrantBtn">
                                    <i class="fas fa-check"></i> Grant Selected
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('deny')" disabled id="bulkDenyBtn">
                                    <i class="fas fa-times"></i> Deny Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permission Requests Table -->
                <div class="card-body">
                    @if($permissionRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllTable" class="form-check-input">
                                        </th>
                                        <th>Parameter</th>
                                        <th>User</th>
                                        <th>College</th>
                                        <th>File Info</th>
                                        <th>Status</th>
                                        <th>Requested At</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissionRequests as $request)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input request-checkbox" 
                                                       value="{{ $request->id }}">
                                            </td>
                                            <td>
                                                <strong>{{ $request->parameter->title ?? 'N/A' }}</strong>
                                                @if($request->parameter->code)
                                                    <br><small class="text-muted">{{ $request->parameter->code }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $request->user->name ?? 'N/A' }}</strong>
                                                    <br><small class="text-muted">{{ $request->user->email ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $request->college->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($request->drive_file_id)
                                                    <small class="text-muted d-block">ID: {{ Str::limit($request->drive_file_id, 20) }}</small>
                                                @endif
                                                @if($request->share_link)
                                                    <a href="{{ $request->share_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fab fa-google-drive"></i> View
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match($request->permission_status) {
                                                        'granted' => 'success',
                                                        'denied' => 'danger',
                                                        'requested' => 'warning',
                                                        'expired' => 'secondary',
                                                        default => 'info'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">
                                                    {{ ucfirst($request->permission_status ?? 'unknown') }}
                                                </span>
                                                @if($request->requires_permission)
                                                    <br><small class="text-info">Requires Permission</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($request->permission_requested_at)
                                                    {{ $request->permission_requested_at->format('M d, Y H:i') }}
                                                    <br><small class="text-muted">{{ $request->permission_requested_at->diffForHumans() }}</small>
                                                @else
                                                    <span class="text-muted">Not requested</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.drive-permissions.show', $request) }}" 
                                                       class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($request->permission_status === 'requested')
                                                        <button type="button" class="btn btn-sm btn-success" 
                                                                onclick="grantPermission({{ $request->id }})" title="Grant">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="denyPermission({{ $request->id }})" title="Deny">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Showing {{ $permissionRequests->firstItem() }} to {{ $permissionRequests->lastItem() }} 
                                of {{ $permissionRequests->total() }} results
                            </div>
                            {{ $permissionRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No permission requests found</h5>
                            <p class="text-muted">There are no Google Drive permission requests matching your criteria.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Modal -->
<div class="modal fade" id="statsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Google Drive Statistics</h5>
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

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cleanup Old Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cleanupForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="daysOld" class="form-label">Delete requests older than (days):</label>
                        <input type="number" class="form-control" id="daysOld" name="days_old" 
                               value="30" min="1" max="365" required>
                        <div class="form-text">This will permanently delete old permission requests.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Cleanup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deny Reason Modal -->
<div class="modal fade" id="denyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deny Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="denyForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="denyReason" class="form-label">Reason for denial (optional):</label>
                        <textarea class="form-control" id="denyReason" name="reason" rows="3" 
                                  placeholder="Provide a reason for denying access..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Deny Access</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let currentRequestId = null;
let selectedIds = [];

// Initialize page
$(document).ready(function() {
    initializeCheckboxes();
    initializeModals();
});

// Checkbox management
function initializeCheckboxes() {
    $('#selectAll, #selectAllTable').change(function() {
        const isChecked = $(this).is(':checked');
        $('.request-checkbox').prop('checked', isChecked);
        updateBulkButtons();
    });

    $(document).on('change', '.request-checkbox', function() {
        updateBulkButtons();
        updateSelectAllState();
    });
}

function updateBulkButtons() {
    selectedIds = $('.request-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    const hasSelection = selectedIds.length > 0;
    $('#bulkGrantBtn, #bulkDenyBtn').prop('disabled', !hasSelection);
}

function updateSelectAllState() {
    const totalCheckboxes = $('.request-checkbox').length;
    const checkedCheckboxes = $('.request-checkbox:checked').length;
    
    $('#selectAll, #selectAllTable').prop('checked', totalCheckboxes === checkedCheckboxes);
}

// Permission actions
function grantPermission(requestId) {
    if (confirm('Are you sure you want to grant access to this Google Drive file?')) {
        $.post(`/admin/drive-permissions/${requestId}/grant`)
            .done(function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', response.error || 'Failed to grant permission');
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON;
                showAlert('error', response?.error || 'Failed to grant permission');
            });
    }
}

function denyPermission(requestId) {
    currentRequestId = requestId;
    $('#denyModal').modal('show');
}

function bulkAction(action) {
    if (selectedIds.length === 0) {
        showAlert('warning', 'Please select at least one request');
        return;
    }

    if (action === 'deny') {
        currentRequestId = 'bulk';
        $('#denyModal').modal('show');
        return;
    }

    if (confirm(`Are you sure you want to ${action} ${selectedIds.length} selected requests?`)) {
        $.post('/admin/drive-permissions/bulk-action', {
            action: action,
            ids: selectedIds,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                showAlert('success', response.message);
                location.reload();
            } else {
                showAlert('error', response.error || 'Bulk action failed');
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showAlert('error', response?.error || 'Bulk action failed');
        });
    }
}

// Modal handlers
function initializeModals() {
    $('#denyForm').submit(function(e) {
        e.preventDefault();
        
        const reason = $('#denyReason').val();
        
        if (currentRequestId === 'bulk') {
            $.post('/admin/drive-permissions/bulk-action', {
                action: 'deny',
                ids: selectedIds,
                reason: reason,
                _token: $('meta[name="csrf-token"]').attr('content')
            })
            .done(handleDenyResponse)
            .fail(handleDenyError);
        } else {
            $.post(`/admin/drive-permissions/${currentRequestId}/deny`, {
                reason: reason,
                _token: $('meta[name="csrf-token"]').attr('content')
            })
            .done(handleDenyResponse)
            .fail(handleDenyError);
        }
    });

    $('#cleanupForm').submit(function(e) {
        e.preventDefault();
        
        const daysOld = $('#daysOld').val();
        
        if (confirm(`Are you sure you want to delete all permission requests older than ${daysOld} days?`)) {
            $.post('/admin/drive-permissions/cleanup', {
                days_old: daysOld,
                _token: $('meta[name="csrf-token"]').attr('content')
            })
            .done(function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#cleanupModal').modal('hide');
                    location.reload();
                } else {
                    showAlert('error', response.error || 'Cleanup failed');
                }
            })
            .fail(function(xhr) {
                const response = xhr.responseJSON;
                showAlert('error', response?.error || 'Cleanup failed');
            });
        }
    });
}

function handleDenyResponse(response) {
    if (response.success) {
        showAlert('success', response.message);
        $('#denyModal').modal('hide');
        location.reload();
    } else {
        showAlert('error', response.error || 'Failed to deny permission');
    }
}

function handleDenyError(xhr) {
    const response = xhr.responseJSON;
    showAlert('error', response?.error || 'Failed to deny permission');
}

// Statistics
function loadStats() {
    $('#statsModal').modal('show');
    
    $.get('/admin/drive-permissions/api/stats')
        .done(function(stats) {
            const content = `
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h3>${stats.total_drive_files}</h3>
                                <p class="mb-0">Total Drive Files</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h3>${stats.pending_requests}</h3>
                                <p class="mb-0">Pending Requests</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h3>${stats.granted_permissions}</h3>
                                <p class="mb-0">Granted Permissions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h3>${stats.denied_permissions}</h3>
                                <p class="mb-0">Denied Permissions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h3>${stats.files_requiring_permission}</h3>
                                <p class="mb-0">Files Requiring Permission</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#statsContent').html(content);
        })
        .fail(function() {
            $('#statsContent').html('<div class="alert alert-danger">Failed to load statistics</div>');
        });
}

function showCleanupModal() {
    $('#cleanupModal').modal('show');
}

// Utility functions
function showAlert(type, message) {
    const alertClass = type === 'error' ? 'danger' : type;
    const alert = `
        <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at the top of the container
    $('.container-fluid').prepend(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endpush