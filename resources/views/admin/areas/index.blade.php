@extends('layouts.admin')

@section('title', 'Areas Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Areas Management</h3>
                    <a href="{{ route('admin.areas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Area
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.areas.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by code, title, description..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="college_id" class="form-select">
                                    <option value="">All Colleges</option>
                                    @foreach($colleges as $college)
                                        <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                            {{ $college->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="academic_year_id" class="form-select">
                                    <option value="">All Academic Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-outline-primary flex-fill">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <a href="{{ route('admin.areas.index') }}" class="btn btn-outline-secondary flex-fill">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="root_only" value="1" 
                                           id="rootOnly" {{ request('root_only') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="rootOnly">
                                        Show root areas only (no parent)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    @if($areas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Title</th>
                                        <th>College</th>
                                        <th>Academic Year</th>
                                        <th>Parent Area</th>
                                        <th>Children</th>
                                        <th>Parameters</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($areas as $area)
                                        <tr>
                                            <td>
                                                <code class="bg-light px-2 py-1 rounded">{{ $area->code }}</code>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($area->depth_level > 0)
                                                        <span class="text-muted me-2">
                                                            {{ str_repeat('└─ ', $area->depth_level) }}
                                                        </span>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $area->title }}</strong>
                                                        @if($area->description)
                                                            <br><small class="text-muted">{{ Str::limit($area->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $area->college->name }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $area->academicYear->active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $area->academicYear->label }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($area->parentArea)
                                                    <a href="{{ route('admin.areas.show', $area->parentArea) }}" 
                                                       class="text-decoration-none">
                                                        {{ $area->parentArea->title }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Root Area</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($area->childAreas->count() > 0)
                                                    <span class="badge bg-primary">{{ $area->childAreas->count() }}</span>
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($area->parameters->count() > 0)
                                                    <span class="badge bg-warning">{{ $area->parameters->count() }}</span>
                                                @else
                                                    <span class="text-muted">None</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.areas.show', $area) }}" 
                                                       class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.areas.edit', $area) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal{{ $area->id }}" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
                                Showing {{ $areas->firstItem() }} to {{ $areas->lastItem() }} of {{ $areas->total() }} results
                            </div>
                            {{ $areas->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No areas found</h5>
                            <p class="text-muted">Start by creating your first area.</p>
                            <a href="{{ route('admin.areas.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Area
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modals -->
@foreach($areas as $area)
<div class="modal fade" id="deleteModal{{ $area->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $area->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel{{ $area->id }}">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the area <strong>{{ $area->title }}</strong> ({{ $area->code }})?</p>
                
                @if($area->hasChildren() || $area->parameters->count() > 0)
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle"></i> Cannot Delete</h6>
                        <ul class="mb-0">
                            @if($area->hasChildren())
                                <li>This area has {{ $area->childAreas->count() }} child area(s)</li>
                            @endif
                            @if($area->parameters->count() > 0)
                                <li>This area has {{ $area->parameters->count() }} parameter(s)</li>
                            @endif
                        </ul>
                        <small class="text-muted">Please delete or reassign child areas and parameters first.</small>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Warning</h6>
                        <ul class="mb-0">
                            <li>This action cannot be undone</li>
                            <li>All associated data will be permanently deleted</li>
                        </ul>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                @if(!$area->hasChildren() && $area->parameters->count() == 0)
                    <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Area</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach

<style>
.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

code {
    font-size: 0.8rem;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endsection