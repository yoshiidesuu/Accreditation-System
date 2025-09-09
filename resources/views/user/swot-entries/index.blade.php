@extends('layouts.user')

@section('title', 'SWOT Entries')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">SWOT Entries</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">SWOT Entries</li>
                </ol>
            </nav>
        </div>
        <div>
            @can('create', App\Models\SwotEntry::class)
                <a href="{{ route('user.swot-entries.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add SWOT Entry
                </a>
            @endcan
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('user.swot-entries.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Search entries...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="strength" {{ request('type') == 'strength' ? 'selected' : '' }}>Strength</option>
                                <option value="weakness" {{ request('type') == 'weakness' ? 'selected' : '' }}>Weakness</option>
                                <option value="opportunity" {{ request('type') == 'opportunity' ? 'selected' : '' }}>Opportunity</option>
                                <option value="threat" {{ request('type') == 'threat' ? 'selected' : '' }}>Threat</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                    @if(auth()->user()->hasRole(['admin', 'staff', 'coordinator']))
                        <div class="col-md-2">
                            <div class="mb-3">
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
                        </div>
                    @endif
                    <div class="col-md-2">
                        <div class="mb-3">
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
                    </div>
                    <div class="col-md-1">
                        <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('user.swot-entries.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- SWOT Entries Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">SWOT Entries List</h6>
            <div class="d-flex align-items-center">
                <span class="text-muted me-3">{{ $swotEntries->total() }} entries found</span>
                @if($swotEntries->hasPages())
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="per_page" id="per_page_10" value="10" 
                               {{ request('per_page', 15) == 10 ? 'checked' : '' }} onchange="changePerPage(10)">
                        <label class="btn btn-outline-secondary" for="per_page_10">10</label>
                        
                        <input type="radio" class="btn-check" name="per_page" id="per_page_15" value="15" 
                               {{ request('per_page', 15) == 15 ? 'checked' : '' }} onchange="changePerPage(15)">
                        <label class="btn btn-outline-secondary" for="per_page_15">15</label>
                        
                        <input type="radio" class="btn-check" name="per_page" id="per_page_25" value="25" 
                               {{ request('per_page', 15) == 25 ? 'checked' : '' }} onchange="changePerPage(25)">
                        <label class="btn btn-outline-secondary" for="per_page_25">25</label>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($swotEntries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'direction' => request('sort') == 'title' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Title
                                        @if(request('sort') == 'title')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="10%">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'type', 'direction' => request('sort') == 'type' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Type
                                        @if(request('sort') == 'type')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="20%">Description</th>
                                @if(auth()->user()->hasRole(['admin', 'staff', 'coordinator']))
                                    <th width="12%">College</th>
                                @endif
                                <th width="10%">Academic Year</th>
                                <th width="10%">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Status
                                        @if(request('sort') == 'status')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="12%">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Created
                                        @if(request('sort') == 'created_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($swotEntries as $index => $entry)
                                <tr>
                                    <td>{{ $swotEntries->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('user.swot-entries.show', $entry) }}" class="text-decoration-none fw-bold">
                                            {{ Str::limit($entry->title, 30) }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $entry->type === 'strength' ? 'success' : 
                                            ($entry->type === 'weakness' ? 'danger' : 
                                            ($entry->type === 'opportunity' ? 'info' : 'warning'))
                                        }}">
                                            {{ ucfirst($entry->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span title="{{ $entry->description }}" data-bs-toggle="tooltip">
                                            {{ Str::limit($entry->description, 50) }}
                                        </span>
                                    </td>
                                    @if(auth()->user()->hasRole(['admin', 'staff', 'coordinator']))
                                        <td>{{ $entry->college->name ?? 'N/A' }}</td>
                                    @endif
                                    <td>{{ $entry->academicYear->label ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $entry->status === 'approved' ? 'success' : 
                                            ($entry->status === 'rejected' ? 'danger' : 
                                            ($entry->status === 'under_review' ? 'info' : 'warning'))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $entry->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $entry->created_at->format('M d, Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('user.swot-entries.show', $entry) }}" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $entry)
                                                <a href="{{ route('user.swot-entries.edit', $entry) }}" 
                                                   class="btn btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @if(auth()->user()->hasRole(['coordinator', 'admin']) && $entry->status === 'submitted')
                                                <a href="{{ route('user.swot-entries.review', $entry) }}" 
                                                   class="btn btn-outline-success" title="Review">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </a>
                                            @endif
                                            @can('delete', $entry)
                                                <button type="button" class="btn btn-outline-danger" 
                                                        title="Delete" data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal" 
                                                        data-entry-id="{{ $entry->id }}" 
                                                        data-entry-title="{{ $entry->title }}">
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
                @if($swotEntries->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <p class="text-muted mb-0">
                                Showing {{ $swotEntries->firstItem() }} to {{ $swotEntries->lastItem() }} of {{ $swotEntries->total() }} entries
                            </p>
                        </div>
                        <div>
                            {{ $swotEntries->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-chart-line fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted mb-3">No SWOT Entries Found</h5>
                    @if(request()->hasAny(['search', 'type', 'status', 'college_id', 'academic_year_id']))
                        <p class="text-muted mb-4">No entries match your current filters. Try adjusting your search criteria.</p>
                        <a href="{{ route('user.swot-entries.index') }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    @else
                        <p class="text-muted mb-4">Get started by creating your first SWOT entry to analyze strengths, weaknesses, opportunities, and threats.</p>
                    @endif
                    @can('create', App\Models\SwotEntry::class)
                        <a href="{{ route('user.swot-entries.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add SWOT Entry
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
                <p>Are you sure you want to delete the SWOT entry "<span id="entryTitle"></span>"?</p>
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
.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fc;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75em;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
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
        border-radius: 0.25rem !important;
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
    $('#deleteModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const entryId = button.data('entry-id');
        const entryTitle = button.data('entry-title');
        
        const modal = $(this);
        modal.find('#entryTitle').text(entryTitle);
        modal.find('#deleteForm').attr('action', `/user/swot-entries/${entryId}`);
    });
    
    // Auto-submit form on filter change
    $('#type, #status, #college_id, #academic_year_id').on('change', function() {
        $('#filterForm').submit();
    });
    
    // Search with debounce
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            $('#filterForm').submit();
        }, 500);
    });
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});

function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}
</script>
@endpush