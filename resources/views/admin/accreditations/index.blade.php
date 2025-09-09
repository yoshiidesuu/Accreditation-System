@extends('layouts.admin')

@section('title', 'Accreditations Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Accreditations Management</h1>
            <p class="mb-0 text-muted">Manage and monitor all accreditation processes</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Accreditations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-certificate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Planning</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['planning'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Progress</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['in_progress'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Accredited</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['accredited'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accreditations.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Search accreditations...">
                </div>
                
                <div class="col-md-2">
                    <label for="college_id" class="form-label">College</label>
                    <select class="form-select" id="college_id" name="college_id">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                {{ $college->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="academic_year_id" class="form-label">Academic Year</label>
                    <select class="form-select" id="academic_year_id" name="academic_year_id">
                        <option value="">All Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="planning" {{ request('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="accredited" {{ request('status') == 'accredited' ? 'selected' : '' }}>Accredited</option>
                        <option value="denied" {{ request('status') == 'denied' ? 'selected' : '' }}>Denied</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All Types</option>
                        <option value="institutional" {{ request('type') == 'institutional' ? 'selected' : '' }}>Institutional</option>
                        <option value="program" {{ request('type') == 'program' ? 'selected' : '' }}>Program</option>
                        <option value="specialized" {{ request('type') == 'specialized' ? 'selected' : '' }}>Specialized</option>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Accreditations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Accreditations List</h6>
            <div>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                    <i class="fas fa-tasks"></i> Bulk Actions
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($accreditations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Title</th>
                                <th>College</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Accrediting Body</th>
                                <th>Start Date</th>
                                <th>Visit Date</th>
                                <th>Assigned Lead</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accreditations as $accreditation)
                                <tr>
                                    <td><input type="checkbox" class="accreditation-checkbox" value="{{ $accreditation->id }}"></td>
                                    <td>
                                        <div class="font-weight-bold">{{ $accreditation->title }}</div>
                                        @if($accreditation->description)
                                            <small class="text-muted">{{ Str::limit($accreditation->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $accreditation->college->name }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($accreditation->type) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'planning' => 'warning',
                                                'in_progress' => 'info',
                                                'under_review' => 'primary',
                                                'completed' => 'secondary',
                                                'accredited' => 'success',
                                                'denied' => 'danger',
                                                'suspended' => 'dark'
                                            ];
                                        @endphp
                                        <span class="badge badge-{{ $statusColors[$accreditation->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $accreditation->accrediting_body }}</td>
                                    <td>{{ $accreditation->start_date ? $accreditation->start_date->format('M d, Y') : '-' }}</td>
                                    <td>{{ $accreditation->visit_date ? $accreditation->visit_date->format('M d, Y') : '-' }}</td>
                                    <td>{{ $accreditation->assignedLead->name ?? 'Not assigned' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.accreditations.show', $accreditation) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.accreditations.edit', $accreditation) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.accreditations.destroy', $accreditation) }}" 
                                                  class="d-inline" onsubmit="return confirm('Are you sure you want to delete this accreditation?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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
                        Showing {{ $accreditations->firstItem() }} to {{ $accreditations->lastItem() }} of {{ $accreditations->total() }} results
                    </div>
                    {{ $accreditations->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-certificate fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No accreditations found</h5>
                    <p class="text-muted">No accreditations match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkActionForm" method="POST" action="{{ route('admin.accreditations.bulk-update') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulkAction" class="form-label">Select Action</label>
                        <select class="form-select" id="bulkAction" name="action" required>
                            <option value="">Choose action...</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="change_status">Change Status</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="statusField" style="display: none;">
                        <label for="bulkStatus" class="form-label">New Status</label>
                        <select class="form-select" id="bulkStatus" name="status">
                            <option value="planning">Planning</option>
                            <option value="in_progress">In Progress</option>
                            <option value="under_review">Under Review</option>
                            <option value="completed">Completed</option>
                            <option value="accredited">Accredited</option>
                            <option value="denied">Denied</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="selectedCount">0</span> accreditation(s) selected. This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Action</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#selectAll').change(function() {
        $('.accreditation-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });
    
    // Individual checkbox change
    $('.accreditation-checkbox').change(function() {
        updateSelectedCount();
        
        // Update select all checkbox
        const totalCheckboxes = $('.accreditation-checkbox').length;
        const checkedCheckboxes = $('.accreditation-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Show/hide status field based on action
    $('#bulkAction').change(function() {
        if ($(this).val() === 'change_status') {
            $('#statusField').show();
            $('#bulkStatus').prop('required', true);
        } else {
            $('#statusField').hide();
            $('#bulkStatus').prop('required', false);
        }
    });
    
    // Handle bulk action form submission
    $('#bulkActionForm').submit(function(e) {
        const selectedIds = $('.accreditation-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedIds.length === 0) {
            e.preventDefault();
            alert('Please select at least one accreditation.');
            return false;
        }
        
        // Add selected IDs to form
        selectedIds.forEach(function(id) {
            $('<input>').attr({
                type: 'hidden',
                name: 'accreditation_ids[]',
                value: id
            }).appendTo('#bulkActionForm');
        });
        
        const action = $('#bulkAction').val();
        if (action === 'delete') {
            return confirm('Are you sure you want to delete the selected accreditations? This action cannot be undone.');
        }
        
        return confirm('Are you sure you want to apply this action to the selected accreditations?');
    });
    
    function updateSelectedCount() {
        const count = $('.accreditation-checkbox:checked').length;
        $('#selectedCount').text(count);
    }
});
</script>
@endpush

@push('styles')
<style>
.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.table td {
    vertical-align: middle;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}
</style>
@endpush