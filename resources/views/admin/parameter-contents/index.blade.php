@extends('layouts.admin')

@section('title', 'Parameter Contents Management')

@section('page-title')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">Parameter Contents Management</h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fas fa-filter me-2"></i>Filters
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportData('excel')">Excel</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData('csv')">CSV</a></li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filters -->
    <div class="collapse mb-4" id="filterCollapse">
        <div class="card card-body">
            <form method="GET" action="{{ route('admin.parameter-contents.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="parameter_id" class="form-label">Parameter</label>
                        <select name="parameter_id" id="parameter_id" class="form-select">
                            <option value="">All Parameters</option>
                            @foreach($parameters as $parameter)
                                <option value="{{ $parameter->id }}" {{ request('parameter_id') == $parameter->id ? 'selected' : '' }}>
                                    {{ $parameter->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="revision_needed" {{ request('status') == 'revision_needed' ? 'selected' : '' }}>Revision Needed</option>
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
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Search content..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.parameter-contents.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Content Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Parameter Contents ({{ $parameterContents->total() }})</h5>
        </div>
        <div class="card-body">
            @if($parameterContents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>College</th>
                                <th>Status</th>
                                <th>Submitted By</th>
                                <th>Submitted Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parameterContents as $content)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="parameter-icon me-2">
                                                <i class="fas fa-file-alt text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $content->parameter->title }}</h6>
                                                <small class="text-muted">{{ $content->parameter->code }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $content->college->name }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'revision_needed' => 'info'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$content->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $content->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="avatar-title rounded-circle bg-light text-dark">
                                                    {{ strtoupper(substr($content->user->first_name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $content->user->first_name }} {{ $content->user->last_name }}</h6>
                                                <small class="text-muted">{{ $content->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $content->created_at->format('M d, Y') }}<br>
                                            {{ $content->created_at->format('h:i A') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.parameter-contents.show', $content) }}">
                                                        <i class="fas fa-eye me-2"></i>View Details
                                                    </a>
                                                </li>
                                                @if($content->status === 'pending')
                                                    <li>
                                                        <form action="{{ route('admin.parameter-contents.approve', $content) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="dropdown-item text-success"
                                                                    onclick="return confirm('Approve this content?')">
                                                                <i class="fas fa-check me-2"></i>Approve
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('admin.parameter-contents.reject', $content) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                    onclick="return confirm('Reject this content?')">
                                                                <i class="fas fa-times me-2"></i>Reject
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.parameter-contents.edit', $content) }}">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $parameterContents->firstItem() ?? 0 }} to {{ $parameterContents->lastItem() ?? 0 }} 
                        of {{ $parameterContents->total() }} results
                    </div>
                    {{ $parameterContents->appends(request()->query())->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Parameter Contents Found</h5>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['search', 'parameter_id', 'status', 'college_id']))
                            No parameter contents match your search criteria.
                        @else
                            No parameter contents have been submitted yet.
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'parameter_id', 'status', 'college_id']))
                        <a href="{{ route('admin.parameter-contents.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    window.location.href = '{{ route("admin.parameter-contents.index") }}?' + params.toString();
}
</script>
@endpush