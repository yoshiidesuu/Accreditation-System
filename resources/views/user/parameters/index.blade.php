@extends('user.layout')

@section('title', 'Parameters')

@section('page-header')
@endsection

@section('page-title', 'Parameters')
@section('page-description', 'Manage form parameters and field configurations')

@section('page-actions')
@can('create', App\Models\Parameter::class)
<a href="{{ route('user.parameters.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i>Add Parameter
</a>
@endcan
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cogs me-2"></i>Parameters List
                </h5>
            </div>
            <div class="card-body">
                <!-- Search and Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search parameters..." 
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary ms-2">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request()->hasAny(['search', 'area_id', 'type', 'required']))
                            <a href="{{ route('user.parameters.index') }}" class="btn btn-outline-secondary ms-1">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="GET">
                            <select name="area_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All Areas</option>
                                @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }} ({{ $area->college->name }})
                                </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="GET">
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="text" {{ request('type') == 'text' ? 'selected' : '' }}>Text</option>
                                <option value="textarea" {{ request('type') == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                <option value="number" {{ request('type') == 'number' ? 'selected' : '' }}>Number</option>
                                <option value="email" {{ request('type') == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="url" {{ request('type') == 'url' ? 'selected' : '' }}>URL</option>
                                <option value="date" {{ request('type') == 'date' ? 'selected' : '' }}>Date</option>
                                <option value="select" {{ request('type') == 'select' ? 'selected' : '' }}>Select</option>
                                <option value="checkbox" {{ request('type') == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                <option value="radio" {{ request('type') == 'radio' ? 'selected' : '' }}>Radio</option>
                                <option value="file" {{ request('type') == 'file' ? 'selected' : '' }}>File</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form method="GET">
                            <select name="required" class="form-select" onchange="this.form.submit()">
                                <option value="">All Fields</option>
                                <option value="true" {{ request('required') == 'true' ? 'selected' : '' }}>Required Only</option>
                                <option value="false" {{ request('required') == 'false' ? 'selected' : '' }}>Optional Only</option>
                            </select>
                        </form>
                    </div>
                </div>

                @if($parameters->count() > 0)
                <!-- Parameters Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Parameter</th>
                                <th>Area</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parameters as $parameter)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="type-icon me-2">
                                            @switch($parameter->type)
                                                @case('text')
                                                @case('textarea')
                                                    <i class="fas fa-font text-primary"></i>
                                                    @break
                                                @case('number')
                                                    <i class="fas fa-hashtag text-success"></i>
                                                    @break
                                                @case('email')
                                                    <i class="fas fa-envelope text-info"></i>
                                                    @break
                                                @case('url')
                                                    <i class="fas fa-link text-warning"></i>
                                                    @break
                                                @case('date')
                                                    <i class="fas fa-calendar text-secondary"></i>
                                                    @break
                                                @case('select')
                                                @case('radio')
                                                @case('checkbox')
                                                    <i class="fas fa-list text-purple"></i>
                                                    @break
                                                @case('file')
                                                    <i class="fas fa-file text-danger"></i>
                                                    @break
                                                @default
                                                    <i class="fas fa-cog text-muted"></i>
                                            @endswitch
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $parameter->title }}</h6>
                                            <small class="text-muted">{{ $parameter->code }}</small>
                                            @if($parameter->description)
                                            <br><small class="text-muted">{{ Str::limit($parameter->description, 60) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-layer-group me-1 text-primary"></i>
                                        <div>
                                            <span>{{ $parameter->area->name }}</span>
                                            <br><small class="text-muted">{{ $parameter->area->college->name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ ucfirst($parameter->type) }}</span>
                                    @if(in_array($parameter->type, ['select', 'checkbox', 'radio']) && $parameter->options)
                                    <br><small class="text-muted">{{ count($parameter->options) }} options</small>
                                    @endif
                                </td>
                                <td>
                                    @if($parameter->required)
                                    <span class="badge bg-danger">Required</span>
                                    @else
                                    <span class="badge bg-secondary">Optional</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $parameter->order ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $parameter->active ? 'bg-success' : 'bg-warning' }}">
                                        {{ $parameter->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @can('view', $parameter)
                                        <a href="{{ route('user.parameters.show', $parameter) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('update', $parameter)
                                        <a href="{{ route('user.parameters.edit', $parameter) }}" 
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('delete', $parameter)
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="confirmDelete('{{ $parameter->id }}', '{{ $parameter->title }}')" title="Delete">
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
                        Showing {{ $parameters->firstItem() ?? 0 }} to {{ $parameters->lastItem() ?? 0 }} 
                        of {{ $parameters->total() }} results
                    </div>
                    {{ $parameters->appends(request()->query())->links() }}
                </div>
                @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Parameters Found</h5>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['search', 'area_id', 'type', 'required']))
                            No parameters match your search criteria.
                        @else
                            No parameters are available for your role.
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'area_id', 'type', 'required']))
                    <a href="{{ route('user.parameters.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Clear Filters
                    </a>
                    @else
                    @can('create', App\Models\Parameter::class)
                    <a href="{{ route('user.parameters.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Create First Parameter
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
                <p>Are you sure you want to delete the parameter <strong id="deleteParameterName"></strong>?</p>
                <p class="text-muted">This action cannot be undone and will remove all associated content.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Parameter</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(parameterId, parameterTitle) {
    document.getElementById('deleteParameterName').textContent = parameterTitle;
    document.getElementById('deleteForm').action = `/user/parameters/${parameterId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush

@push('styles')
<style>
.type-icon {
    width: 24px;
    text-align: center;
}

.text-purple {
    color: #6f42c1 !important;
}

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