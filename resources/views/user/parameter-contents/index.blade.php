@extends('layouts.user')

@section('title', 'Parameter Contents')

@section('page-title')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">Parameter Contents</h1>
        @can('create', App\Models\ParameterContent::class)
            <a href="{{ route('user.parameter-contents.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Content
            </a>
        @endcan
    </div>
@endsection

@section('actions')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
            <i class="fas fa-filter me-2"></i>Filters
        </button>
        @if(auth()->user()->hasRole(['admin', 'coordinator']))
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
        @endif
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filters -->
    <div class="collapse mb-4" id="filterCollapse">
        <div class="card card-body">
            <form method="GET" action="{{ route('user.parameter-contents.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Search contents...">
                </div>
                
                @if(auth()->user()->hasRole('admin'))
                <div class="col-md-3">
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
                @endif
                
                <div class="col-md-3">
                    <label for="area_id" class="form-label">Area</label>
                    <select class="form-select" id="area_id" name="area_id">
                        <option value="">All Areas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                
                <div class="col-md-3">
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
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('user.parameter-contents.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Contents Table -->
    <div class="card">
        <div class="card-body">
            @if($parameterContents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Parameter</th>
                                <th>Area</th>
                                @if(auth()->user()->hasRole('admin'))
                                    <th>College</th>
                                @endif
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Created By</th>
                                <th>Last Updated</th>
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
                                        <span class="badge" style="background-color: {{ $content->area->color ?? '#6c757d' }}; color: white;">
                                            {{ $content->area->name }}
                                        </span>
                                    </td>
                                    @if(auth()->user()->hasRole('admin'))
                                        <td>{{ $content->college->name }}</td>
                                    @endif
                                    <td>
                                        @php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'pending_review' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$content->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $content->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $progress = 0;
                                            if ($content->status === 'draft') $progress = 25;
                                            elseif ($content->status === 'pending_review') $progress = 50;
                                            elseif ($content->status === 'approved') $progress = 100;
                                            elseif ($content->status === 'rejected') $progress = 75;
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $statusColors[$content->status] ?? 'secondary' }}" 
                                                 style="width: {{ $progress }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $progress }}%</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="avatar-title rounded-circle bg-light text-dark">
                                                    {{ substr($content->creator->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <small class="fw-medium">{{ $content->creator->name }}</small>
                                                <br>
                                                <small class="text-muted">{{ $content->creator->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $content->updated_at->format('M d, Y') }}<br>
                                            {{ $content->updated_at->format('h:i A') }}
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
                                                    <a class="dropdown-item" href="{{ route('user.parameter-contents.show', $content) }}">
                                                        <i class="fas fa-eye me-2"></i>View
                                                    </a>
                                                </li>
                                                @can('update', $content)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('user.parameter-contents.edit', $content) }}">
                                                            <i class="fas fa-edit me-2"></i>Edit
                                                        </a>
                                                    </li>
                                                @endcan
                                                @if($content->status === 'draft' && $content->created_by === auth()->id())
                                                    <li>
                                                        <form action="{{ route('user.parameter-contents.submit', $content) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="dropdown-item text-primary"
                                                                    onclick="return confirm('Submit this content for review?')">
                                                                <i class="fas fa-paper-plane me-2"></i>Submit for Review
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @can('delete', $content)
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item text-danger" 
                                                                onclick="confirmDelete('{{ $content->id }}', '{{ $content->parameter->title }}')">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </button>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $parameterContents->firstItem() }} to {{ $parameterContents->lastItem() }} 
                        of {{ $parameterContents->total() }} results
                    </div>
                    {{ $parameterContents->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-file-alt fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Parameter Contents Found</h5>
                    <p class="text-muted mb-4">There are no parameter contents matching your criteria.</p>
                    @can('create', App\Models\ParameterContent::class)
                        <a href="{{ route('user.parameter-contents.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add First Content
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the parameter content "<span id="deleteItemName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
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
    font-size: 0.875rem;
    font-weight: 500;
}

.parameter-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(13, 110, 253, 0.1);
    border-radius: 8px;
}

.table th {
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.progress {
    background-color: #e9ecef;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.btn-outline-secondary:hover {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(contentId, parameterTitle) {
    document.getElementById('deleteItemName').textContent = parameterTitle;
    document.getElementById('deleteForm').action = `/user/parameter-contents/${contentId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    window.location.href = `{{ route('user.reports.export') }}?type=parameter&format=${format}&${params.toString()}`;
}

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const filterSelects = document.querySelectorAll('#filterCollapse select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Optional: Auto-submit on change
            // this.form.submit();
        });
    });
    
    // Search input with debounce
    const searchInput = document.getElementById('search');
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Optional: Auto-submit on search
            // this.form.submit();
        }, 500);
    });
});
</script>
@endpush