@extends('layouts.admin')

@section('title', 'Parameter Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-sliders-h me-2"></i>Parameter Management
                    </h3>
                    <a href="{{ route('admin.parameters.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Parameter
                    </a>
                </div>

                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.parameters.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Search by code or title...">
                            </div>
                            <div class="col-md-3">
                                <label for="area_id" class="form-label">Area</label>
                                <select class="form-select" id="area_id" name="area_id">
                                    <option value="">All Areas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                            {{ $area->code }} - {{ $area->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">All Types</option>
                                    <option value="text" {{ request('type') == 'text' ? 'selected' : '' }}>Text</option>
                                    <option value="textarea" {{ request('type') == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                    <option value="number" {{ request('type') == 'number' ? 'selected' : '' }}>Number</option>
                                    <option value="date" {{ request('type') == 'date' ? 'selected' : '' }}>Date</option>
                                    <option value="file" {{ request('type') == 'file' ? 'selected' : '' }}>File</option>
                                    <option value="select" {{ request('type') == 'select' ? 'selected' : '' }}>Select</option>
                                    <option value="checkbox" {{ request('type') == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                    <option value="radio" {{ request('type') == 'radio' ? 'selected' : '' }}>Radio</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="required" class="form-label">Required</label>
                                <select class="form-select" id="required" name="required">
                                    <option value="">All</option>
                                    <option value="1" {{ request('required') == '1' ? 'selected' : '' }}>Required</option>
                                    <option value="0" {{ request('required') == '0' ? 'selected' : '' }}>Optional</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('admin.parameters.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Parameters Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Area</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parameters as $parameter)
                                    <tr>
                                        <td>
                                            <code class="text-primary">{{ $parameter->code }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ $parameter->title }}</strong>
                                            @if($parameter->description)
                                                <br><small class="text-muted">{{ Str::limit($parameter->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $parameter->area->code }}
                                            </span>
                                            <br><small>{{ $parameter->area->title }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $parameter->type_display }}</span>
                                        </td>
                                        <td>
                                            @if($parameter->required)
                                                <span class="badge bg-danger">Required</span>
                                            @else
                                                <span class="badge bg-success">Optional</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $parameter->order }}</span>
                                        </td>
                                        <td>
                                            @if($parameter->active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-warning">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.parameters.show', $parameter) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.parameters.edit', $parameter) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDelete('{{ $parameter->id }}', '{{ $parameter->title }}')" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p>No parameters found.</p>
                                                <a href="{{ route('admin.parameters.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-1"></i>Create First Parameter
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($parameters->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $parameters->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
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
                <p>Are you sure you want to delete the parameter <strong id="parameterName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action cannot be undone. All associated parameter content will also be deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete Parameter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(parameterId, parameterName) {
    document.getElementById('parameterName').textContent = parameterName;
    document.getElementById('deleteForm').action = `/admin/parameters/${parameterId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush

@push('styles')
<style>
.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 2px;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
}

code {
    background-color: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}
</style>
@endpush