@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Activity Logs</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="exportLogs()">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.activity-logs.index') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" 
                                           placeholder="Search description, IP, user...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user_id">User</label>
                                    <select class="form-control" id="user_id" name="user_id">
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" 
                                                {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="action">Action</label>
                                    <select class="form-control" id="action" name="action">
                                        <option value="">All Actions</option>
                                        @foreach($actions as $action)
                                            <option value="{{ $action }}" 
                                                {{ request('action') == $action ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select class="form-control" id="category" name="category">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" 
                                                {{ request('category') == $category ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $category)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="date_from">From</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" 
                                           value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="date_to">To</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" 
                                           value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results -->
                <div class="card-body p-0">
                    @if($logs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                        <th>Category</th>
                                        <th>IP Address</th>
                                        <th>Date/Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td>{{ $log->id }}</td>
                                            <td>
                                                @if($log->user)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <span class="avatar-initial rounded-circle bg-primary">
                                                                {{ strtoupper(substr($log->user->first_name, 0, 1)) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $log->user->name }}</div>
                                                            <small class="text-muted">{{ $log->user->email }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 300px;" title="{{ $log->description }}">
                                                    {{ $log->description }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($log->action)
                                                    <span class="badge badge-{{ $log->getActionBadgeColor() }}">
                                                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->category)
                                                    <span class="badge badge-outline-secondary">
                                                        {{ ucfirst(str_replace('_', ' ', $log->category)) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <code class="small">{{ $log->ip_address }}</code>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    {{ $log->created_at->format('M d, Y') }}<br>
                                                    <span class="text-muted">{{ $log->created_at->format('H:i:s') }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.activity-logs.show', $log) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
                                </div>
                                {{ $logs->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No activity logs found</h5>
                            <p class="text-muted">Try adjusting your search criteria or filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportLogs() {
    // Get current filter parameters
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Create export URL with current filters
    const exportUrl = '{{ route("admin.activity-logs.export") }}?' + params.toString();
    
    // Trigger download
    window.location.href = exportUrl;
}

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const filterInputs = document.querySelectorAll('#filterForm select, #filterForm input[type="date"]');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // Debounce search input
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
});
</script>
@endpush

@push('styles')
<style>
.avatar {
    width: 32px;
    height: 32px;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

.badge-outline-secondary {
    color: #6c757d;
    border: 1px solid #6c757d;
    background: transparent;
}

.table td {
    vertical-align: middle;
}
</style>
@endpush