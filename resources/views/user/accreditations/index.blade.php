@extends('layouts.user')

@section('title', 'Accreditations')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Accreditations</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Accreditations</li>
                </ol>
            </nav>
        </div>
        @can('create', App\Models\Accreditation::class)
            <a href="{{ route('user.accreditations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Accreditation
            </a>
        @endcan
    </div>

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.accreditations.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Search accreditations...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
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
                                {{ $year->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sort" class="form-label">Sort By</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="created_at_desc" {{ request('sort') === 'created_at_desc' ? 'selected' : '' }}>Newest First</option>
                        <option value="created_at_asc" {{ request('sort') === 'created_at_asc' ? 'selected' : '' }}>Oldest First</option>
                        <option value="title_asc" {{ request('sort') === 'title_asc' ? 'selected' : '' }}>Title A-Z</option>
                        <option value="title_desc" {{ request('sort') === 'title_desc' ? 'selected' : '' }}>Title Z-A</option>
                        <option value="status_asc" {{ request('sort') === 'status_asc' ? 'selected' : '' }}>Status A-Z</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('user.accreditations.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Accreditations Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Accreditations List</h6>
            <span class="badge badge-info">{{ $accreditations->total() }} total</span>
        </div>
        <div class="card-body">
            @if($accreditations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>College</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>Submitted By</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accreditations as $accreditation)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-bold">{{ $accreditation->title }}</div>
                                                @if($accreditation->description)
                                                    <small class="text-muted">{{ Str::limit($accreditation->description, 60) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $accreditation->college->name ?? 'N/A' }}</td>
                                    <td>{{ $accreditation->academicYear->label ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $accreditation->status === 'approved' ? 'success' : 
                                            ($accreditation->status === 'rejected' ? 'danger' : 
                                            ($accreditation->status === 'under_review' ? 'info' : 'warning'))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div>{{ $accreditation->user->name ?? 'Unknown' }}</div>
                                                <small class="text-muted">{{ $accreditation->user->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $accreditation->created_at->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ $accreditation->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @can('view', $accreditation)
                                                <a href="{{ route('user.accreditations.show', $accreditation) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan
                                            @can('update', $accreditation)
                                                <a href="{{ route('user.accreditations.edit', $accreditation) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @if(auth()->user()->hasRole(['admin', 'staff']) && $accreditation->status === 'submitted')
                                                <a href="{{ route('user.accreditations.evaluate', $accreditation) }}" 
                                                   class="btn btn-sm btn-outline-success" title="Evaluate">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </a>
                                            @endif
                                            @can('delete', $accreditation)
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        title="Delete" data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal" 
                                                        data-id="{{ $accreditation->id }}"
                                                        data-title="{{ $accreditation->title }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-muted mb-0">
                            Showing {{ $accreditations->firstItem() }} to {{ $accreditations->lastItem() }} 
                            of {{ $accreditations->total() }} results
                        </p>
                    </div>
                    <div>
                        {{ $accreditations->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Accreditations Found</h5>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['search', 'status', 'college_id', 'academic_year_id']))
                            No accreditations match your current filters. Try adjusting your search criteria.
                        @else
                            No accreditations have been created yet.
                        @endif
                    </p>
                    @can('create', App\Models\Accreditation::class)
                        <a href="{{ route('user.accreditations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create First Accreditation
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the accreditation "<span id="deleteItemTitle"></span>"?</p>
                <p class="text-danger"><strong>This action cannot be undone.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fc;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 0.25rem;
        margin-right: 0;
    }
    
    .btn-group .btn:last-child {
        margin-bottom: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle delete modal
    $('#deleteModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const accreditationId = button.data('id');
        const accreditationTitle = button.data('title');
        
        const modal = $(this);
        modal.find('#deleteItemTitle').text(accreditationTitle);
        modal.find('#deleteForm').attr('action', `/user/accreditations/${accreditationId}`);
    });
    
    // Auto-submit form on filter change
    $('#status, #college_id, #academic_year_id, #sort').on('change', function() {
        $(this).closest('form').submit();
    });
    
    // Handle search input with debounce
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        const form = $(this).closest('form');
        searchTimeout = setTimeout(function() {
            form.submit();
        }, 500);
    });
    
    // Clear search
    $('.btn-outline-secondary').on('click', function(e) {
        e.preventDefault();
        window.location.href = $(this).attr('href');
    });
    
    // Tooltip initialization
    $('[title]').tooltip();
});
</script>
@endpush