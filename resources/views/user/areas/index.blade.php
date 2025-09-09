@extends('user.layout')

@section('title', 'Areas')

@section('page-header')
@endsection

@section('page-title', 'Areas')
@section('page-description', 'Manage accreditation areas and their structure')

@section('page-actions')
@can('create', App\Models\Area::class)
<a href="{{ route('user.areas.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i>Add Area
</a>
@endcan
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-layer-group me-2"></i>Areas List
                </h5>
            </div>
            <div class="card-body">
                <!-- Search and Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search areas..." 
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary ms-2">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request()->hasAny(['search', 'college_id', 'level']))
                            <a href="{{ route('user.areas.index') }}" class="btn btn-outline-secondary ms-1">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form method="GET">
                            <select name="college_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All Colleges</option>
                                @foreach($colleges as $college)
                                <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                    {{ $college->name }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form method="GET">
                            <select name="level" class="form-select" onchange="this.form.submit()">
                                <option value="">All Levels</option>
                                @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ request('level') == $i ? 'selected' : '' }}>
                                    Level {{ $i }}
                                </option>
                                @endfor
                            </select>
                        </form>
                    </div>
                </div>

                @if($areas->count() > 0)
                <!-- Areas Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Area</th>
                                <th>College</th>
                                <th>Level</th>
                                <th>Parent</th>
                                <th>Parameters</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($areas as $area)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="level-indicator level-{{ $area->level }} me-2"></div>
                                        <div>
                                            <h6 class="mb-0">{{ $area->name }}</h6>
                                            <small class="text-muted">{{ $area->code }}</small>
                                            @if($area->description)
                                            <br><small class="text-muted">{{ Str::limit($area->description, 60) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-university me-1 text-primary"></i>
                                        <span>{{ $area->college->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">Level {{ $area->level }}</span>
                                </td>
                                <td>
                                    @if($area->parent)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-arrow-up me-1 text-muted"></i>
                                        <span class="text-muted">{{ Str::limit($area->parent->name, 20) }}</span>
                                    </div>
                                    @else
                                    <span class="text-muted">Root Area</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $area->parameters->count() }}</span>
                                    @if($area->children->count() > 0)
                                    <small class="text-muted d-block">{{ $area->children->count() }} sub-areas</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $area->active ? 'bg-success' : 'bg-warning' }}">
                                        {{ $area->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @can('view', $area)
                                        <a href="{{ route('user.areas.show', $area) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('update', $area)
                                        <a href="{{ route('user.areas.edit', $area) }}" 
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('delete', $area)
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="confirmDelete('{{ $area->id }}', '{{ $area->name }}')" title="Delete">
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
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $areas->firstItem() ?? 0 }} to {{ $areas->lastItem() ?? 0 }} 
                        of {{ $areas->total() }} results
                    </div>
                    {{ $areas->appends(request()->query())->links() }}
                </div>
                @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Areas Found</h5>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['search', 'college_id', 'level']))
                            No areas match your search criteria.
                        @else
                            No areas are available for your role.
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'college_id', 'level']))
                    <a href="{{ route('user.areas.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Clear Filters
                    </a>
                    @else
                    @can('create', App\Models\Area::class)
                    <a href="{{ route('user.areas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Create First Area
                    </a>
                    @endcan
                    @endif
                </div>
                @endif
            </div>
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
                <p>Are you sure you want to delete the area <strong id="deleteAreaName"></strong>?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Area</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(areaId, areaName) {
    document.getElementById('deleteAreaName').textContent = areaName;
    document.getElementById('deleteForm').action = `/user/areas/${areaId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush

@push('styles')
<style>
.level-indicator {
    width: 4px;
    height: 40px;
    border-radius: 2px;
}

.level-1 { background-color: #dc3545; }
.level-2 { background-color: #fd7e14; }
.level-3 { background-color: #ffc107; }
.level-4 { background-color: #20c997; }
.level-5 { background-color: #0d6efd; }

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>
@endpush