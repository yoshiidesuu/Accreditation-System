@extends('admin.layout')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">System Audit Logs</h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.audit.export') }}" class="btn btn-outline-primary">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.audit.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search by user or description..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="event" class="form-select">
                                    <option value="">All Events</option>
                                    <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                                    <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                                    <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Audit Logs Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date/Time</th>
                                    <th>User</th>
                                    <th>Event</th>
                                    <th>Subject</th>
                                    <th>Changes</th>
                                    <th>IP Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            {{ $activity->created_at->format('M d, Y') }}<br>
                                            {{ $activity->created_at->format('h:i A') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($activity->causer)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-primary rounded-circle">
                                                        {{ substr($activity->causer->first_name, 0, 1) }}{{ substr($activity->causer->last_name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong>{{ $activity->causer->name }}</strong><br>
                                                    <small class="text-muted">{{ $activity->causer->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $activity->event == 'created' ? 'success' : ($activity->event == 'updated' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($activity->event) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ class_basename($activity->subject_type) }}</strong>
                                            @if($activity->subject)
                                                <br><small class="text-muted">{{ $activity->subject->name ?? $activity->subject->email ?? 'ID: ' . $activity->subject_id }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($activity->properties && $activity->properties->has('attributes'))
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#changesModal{{ $activity->id }}">
                                                <i class="fas fa-eye"></i> View Changes
                                            </button>
                                        @else
                                            <span class="text-muted">No changes recorded</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $activity->properties['ip'] ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $activity->id }}">
                                            <i class="fas fa-info-circle"></i> Details
                                        </button>
                                    </td>
                                </tr>

                                <!-- Changes Modal -->
                                @if($activity->properties && $activity->properties->has('attributes'))
                                <div class="modal fade" id="changesModal{{ $activity->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Changes Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    @if($activity->properties->has('old'))
                                                    <div class="col-md-6">
                                                        <h6 class="text-danger">Before</h6>
                                                        <div class="bg-light p-3 rounded">
                                                            @foreach($activity->properties['old'] as $key => $value)
                                                            <div class="mb-2">
                                                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                                <span class="text-danger">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <div class="col-md-6">
                                                        <h6 class="text-success">After</h6>
                                                        <div class="bg-light p-3 rounded">
                                                            @foreach($activity->properties['attributes'] as $key => $value)
                                                            <div class="mb-2">
                                                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                                <span class="text-success">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Detail Modal -->
                                <div class="modal fade" id="detailModal{{ $activity->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Activity Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <dl class="row">
                                                    <dt class="col-sm-4">ID:</dt>
                                                    <dd class="col-sm-8">{{ $activity->id }}</dd>
                                                    
                                                    <dt class="col-sm-4">Description:</dt>
                                                    <dd class="col-sm-8">{{ $activity->description }}</dd>
                                                    
                                                    <dt class="col-sm-4">Subject Type:</dt>
                                                    <dd class="col-sm-8">{{ $activity->subject_type }}</dd>
                                                    
                                                    <dt class="col-sm-4">Subject ID:</dt>
                                                    <dd class="col-sm-8">{{ $activity->subject_id }}</dd>
                                                    
                                                    <dt class="col-sm-4">Causer:</dt>
                                                    <dd class="col-sm-8">{{ $activity->causer ? $activity->causer->name . ' (' . $activity->causer->email . ')' : 'System' }}</dd>
                                                    
                                                    <dt class="col-sm-4">Created:</dt>
                                                    <dd class="col-sm-8">{{ $activity->created_at->format('M d, Y h:i A') }}</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-3x mb-3"></i>
                                            <h5>No audit logs found</h5>
                                            <p>No activities match your current filters.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($activities->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $activities->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
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
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

.modal-body dl {
    margin-bottom: 0;
}

.modal-body dt {
    font-weight: 600;
}
</style>
@endsection

@section('scripts')
<script>
// Auto-refresh every 30 seconds
setInterval(function() {
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 30000);
</script>
@endsection