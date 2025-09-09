@extends('layouts.admin')

@section('title', 'Role Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users-cog me-2"></i>
                        Role Management
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.roles.stats') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-bar me-1"></i>
                            Statistics
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('admin.roles.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search Users</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Name, email, employee ID...">
                                </div>
                                <div class="col-md-3">
                                    <label for="role" class="form-label">Filter by Role</label>
                                    <select class="form-select" id="role" name="role">
                                        <option value="">All Roles</option>
                                        @foreach($availableRoles as $roleKey => $roleLabel)
                                            <option value="{{ $roleKey }}" {{ request('role') == $roleKey ? 'selected' : '' }}>
                                                {{ $roleLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Filter by Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>
                                        Filter
                                    </button>
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Clear
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Role Statistics -->
                    @if(count($roleStats) > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Role Distribution</h6>
                                    <div class="row">
                                        @foreach($roleStats as $role => $count)
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <div class="d-flex justify-content-between">
                                                    <span class="badge bg-primary">{{ $availableRoles[$role] ?? ucfirst($role) }}</span>
                                                    <span class="fw-bold">{{ $count }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Bulk Actions -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <form id="bulkForm" method="POST" action="{{ route('admin.roles.bulk-update') }}">
                                @csrf
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll">
                                            Select All
                                        </label>
                                    </div>
                                    <select class="form-select" name="bulk_role" style="width: auto;" required>
                                        <option value="">Bulk Change Role To...</option>
                                        @foreach($availableRoles as $roleKey => $roleLabel)
                                            <option value="{{ $roleKey }}">{{ $roleLabel }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-warning" id="bulkUpdateBtn" disabled>
                                        <i class="fas fa-edit me-1"></i>
                                        Update Selected
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="masterCheckbox">
                                    </th>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Current Role</th>
                                    <th>College</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="user-checkbox" name="user_ids[]" value="{{ $user->id }}">
                                        </td>
                                        <td>
                                            <code>{{ $user->employee_id }}</code>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-primary rounded-circle">
                                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $user->first_name }} {{ $user->last_name }}</div>
                                                    <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'dean' ? 'warning' : 'info') }}">
                                                {{ $availableRoles[$user->role] ?? ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->college)
                                                <span class="badge bg-secondary">{{ $user->college->name }}</span>
                                            @else
                                                <span class="text-muted">No College</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'inactive' ? 'secondary' : 'danger') }}">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.roles.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                    Edit Role
                                                </a>
                                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i>
                                                    View
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>No users found matching your criteria.</p>
                                                <a href="{{ route('admin.roles.index') }}" class="btn btn-primary">
                                                    <i class="fas fa-refresh me-1"></i>
                                                    Reset Filters
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                            </div>
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Master checkbox functionality
    $('#masterCheckbox, #selectAll').change(function() {
        const isChecked = $(this).is(':checked');
        $('.user-checkbox').prop('checked', isChecked);
        $('#masterCheckbox, #selectAll').prop('checked', isChecked);
        toggleBulkActions();
    });

    // Individual checkbox functionality
    $('.user-checkbox').change(function() {
        const totalCheckboxes = $('.user-checkbox').length;
        const checkedCheckboxes = $('.user-checkbox:checked').length;
        
        $('#masterCheckbox, #selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        toggleBulkActions();
    });

    // Toggle bulk actions
    function toggleBulkActions() {
        const checkedCount = $('.user-checkbox:checked').length;
        $('#bulkUpdateBtn').prop('disabled', checkedCount === 0);
    }

    // Bulk form submission
    $('#bulkForm').submit(function(e) {
        const checkedUsers = $('.user-checkbox:checked');
        if (checkedUsers.length === 0) {
            e.preventDefault();
            alert('Please select at least one user.');
            return false;
        }

        const bulkRole = $('select[name="bulk_role"]').val();
        if (!bulkRole) {
            e.preventDefault();
            alert('Please select a role for bulk update.');
            return false;
        }

        if (!confirm(`Are you sure you want to update the role for ${checkedUsers.length} selected user(s)?`)) {
            e.preventDefault();
            return false;
        }

        // Add selected user IDs to form
        checkedUsers.each(function() {
            $('#bulkForm').append(`<input type="hidden" name="user_ids[]" value="${$(this).val()}">`)
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endpush
@endsection