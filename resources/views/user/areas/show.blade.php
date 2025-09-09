@extends('user.layout')

@section('title', $area->name)

@section('page-header')
@endsection

@section('page-title')
<div class="d-flex align-items-center">
    <div class="area-color-indicator me-3" style="background-color: {{ $area->color ?? '#007bff' }};"></div>
    <div>
        <h4 class="mb-0">{{ $area->name }}</h4>
        <small class="text-muted">{{ $area->code }}</small>
    </div>
</div>
@endsection

@section('page-description')
{{ $area->description ?? 'Area details and information' }}
@endsection

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('user.areas.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Areas
    </a>
    @can('update', $area)
        <a href="{{ route('user.areas.edit', $area) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i>Edit Area
        </a>
    @endcan
    @if(auth()->user()->hasRole(['admin', 'coordinator']) && $area->level < 3)
        <a href="{{ route('user.areas.create', ['parent_id' => $area->id]) }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i>Add Sub-Area
        </a>
    @endif
</div>
@endsection

@section('content')
<div class="row">
    <!-- Area Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Area Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="info-label">Name:</label>
                            <div class="info-value">{{ $area->name }}</div>
                        </div>
                        
                        <div class="info-item mb-3">
                            <label class="info-label">Code:</label>
                            <div class="info-value">
                                <code>{{ $area->code }}</code>
                            </div>
                        </div>
                        
                        <div class="info-item mb-3">
                            <label class="info-label">Level:</label>
                            <div class="info-value">
                                <span class="badge bg-info">Level {{ $area->level }}</span>
                            </div>
                        </div>
                        
                        <div class="info-item mb-3">
                            <label class="info-label">Display Order:</label>
                            <div class="info-value">{{ $area->display_order ?? 'Not set' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="info-label">Status:</label>
                            <div class="info-value">
                                @if($area->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item mb-3">
                            <label class="info-label">Approval Required:</label>
                            <div class="info-value">
                                @if($area->requires_approval)
                                    <span class="badge bg-warning">Yes</span>
                                @else
                                    <span class="badge bg-success">No</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item mb-3">
                            <label class="info-label">Color:</label>
                            <div class="info-value">
                                <div class="d-flex align-items-center">
                                    <div class="color-preview me-2" style="background-color: {{ $area->color ?? '#007bff' }};"></div>
                                    <code>{{ $area->color ?? '#007bff' }}</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-item mb-3">
                            <label class="info-label">College:</label>
                            <div class="info-value">
                                @if($area->college)
                                    <a href="{{ route('user.colleges.show', $area->college) }}" class="text-decoration-none">
                                        {{ $area->college->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($area->description)
                    <div class="info-item mt-4">
                        <label class="info-label">Description:</label>
                        <div class="info-value">
                            <p class="mb-0">{{ $area->description }}</p>
                        </div>
                    </div>
                @endif
                
                <!-- Parent Area -->
                @if($area->parent)
                    <div class="info-item mt-4">
                        <label class="info-label">Parent Area:</label>
                        <div class="info-value">
                            <a href="{{ route('user.areas.show', $area->parent) }}" class="text-decoration-none">
                                <i class="fas fa-arrow-up me-1"></i>{{ $area->parent->name }}
                            </a>
                        </div>
                    </div>
                @endif
                
                <!-- Meta Information -->
                @if($area->meta && count($area->meta) > 0)
                    <div class="info-item mt-4">
                        <label class="info-label">Additional Information:</label>
                        <div class="info-value">
                            <div class="row">
                                @foreach($area->meta as $key => $value)
                                    <div class="col-md-6 mb-2">
                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                        <span class="ms-1">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Sub-Areas -->
        @if($area->children && $area->children->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sitemap me-2"></i>Sub-Areas ({{ $area->children->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($area->children as $child)
                            <div class="col-md-6 mb-3">
                                <div class="sub-area-card">
                                    <div class="d-flex align-items-center">
                                        <div class="area-color-indicator me-2" style="background-color: {{ $child->color ?? '#007bff' }};"></div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="{{ route('user.areas.show', $child) }}" class="text-decoration-none">
                                                    {{ $child->name }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">{{ $child->code }}</small>
                                            @if(!$child->is_active)
                                                <span class="badge bg-secondary ms-2">Inactive</span>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-light text-dark">Level {{ $child->level }}</span>
                                        </div>
                                    </div>
                                    @if($child->description)
                                        <p class="small text-muted mt-2 mb-0">{{ Str::limit($child->description, 100) }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Parameters -->
        @if($area->parameters && $area->parameters->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sliders-h me-2"></i>Parameters ({{ $area->parameters->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($area->parameters as $parameter)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $parameter->title }}</strong>
                                                <br><small class="text-muted">{{ $parameter->code }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst($parameter->type) }}</span>
                                        </td>
                                        <td>
                                            @if($parameter->is_required)
                                                <span class="badge bg-warning">Required</span>
                                            @else
                                                <span class="badge bg-secondary">Optional</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($parameter->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('user.parameters.show', $parameter) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Quick Stats
                </h5>
            </div>
            <div class="card-body">
                <div class="stat-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="stat-label">Sub-Areas:</span>
                        <span class="stat-value badge bg-primary">{{ $area->children->count() }}</span>
                    </div>
                </div>
                
                <div class="stat-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="stat-label">Parameters:</span>
                        <span class="stat-value badge bg-info">{{ $area->parameters->count() }}</span>
                    </div>
                </div>
                
                <div class="stat-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="stat-label">Active Parameters:</span>
                        <span class="stat-value badge bg-success">{{ $area->parameters->where('is_active', true)->count() }}</span>
                    </div>
                </div>
                
                <div class="stat-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="stat-label">Required Parameters:</span>
                        <span class="stat-value badge bg-warning">{{ $area->parameters->where('is_required', true)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                @can('update', $area)
                    <a href="{{ route('user.areas.edit', $area) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fas fa-edit me-1"></i>Edit Area
                    </a>
                @endcan
                
                @if(auth()->user()->hasRole(['admin', 'coordinator']))
                    @if($area->level < 3)
                        <a href="{{ route('user.areas.create', ['parent_id' => $area->id]) }}" class="btn btn-outline-success btn-sm w-100 mb-2">
                            <i class="fas fa-plus me-1"></i>Add Sub-Area
                        </a>
                    @endif
                    
                    <a href="{{ route('user.parameters.create', ['area_id' => $area->id]) }}" class="btn btn-outline-info btn-sm w-100 mb-2">
                        <i class="fas fa-sliders-h me-1"></i>Add Parameter
                    </a>
                @endif
                
                @can('delete', $area)
                    @if($area->children->count() == 0 && $area->parameters->count() == 0)
                        <button class="btn btn-outline-danger btn-sm w-100" onclick="confirmDelete()">
                            <i class="fas fa-trash me-1"></i>Delete Area
                        </button>
                    @endif
                @endcan
            </div>
        </div>
        
        <!-- Meta Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info me-2"></i>Meta Information
                </h5>
            </div>
            <div class="card-body">
                <div class="meta-item mb-2">
                    <small class="text-muted">Created:</small>
                    <div>{{ $area->created_at->format('M d, Y \\a\\t g:i A') }}</div>
                </div>
                
                <div class="meta-item mb-2">
                    <small class="text-muted">Last Updated:</small>
                    <div>{{ $area->updated_at->format('M d, Y \\a\\t g:i A') }}</div>
                </div>
                
                @if($area->created_by)
                    <div class="meta-item mb-2">
                        <small class="text-muted">Created By:</small>
                        <div>{{ $area->creator->name ?? 'Unknown' }}</div>
                    </div>
                @endif
                
                @if($area->updated_by)
                    <div class="meta-item mb-2">
                        <small class="text-muted">Last Updated By:</small>
                        <div>{{ $area->updater->name ?? 'Unknown' }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@can('delete', $area)
    @if($area->children->count() == 0 && $area->parameters->count() == 0)
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the area <strong>{{ $area->name }}</strong>?</p>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="{{ route('user.areas.destroy', $area) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Area</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endcan
@endsection

@push('scripts')
<script>
function confirmDelete() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endpush

@push('styles')
<style>
.area-color-indicator {
    width: 4px;
    height: 40px;
    border-radius: 2px;
}

.color-preview {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 1px solid #dee2e6;
}

.info-item {
    border-bottom: 1px solid #f8f9fa;
    padding-bottom: 0.5rem;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    display: block;
}

.info-value {
    color: #212529;
}

.sub-area-card {
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
    transition: all 0.2s ease;
}

.sub-area-card:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.stat-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-weight: 500;
    color: #6c757d;
}

.stat-value {
    font-weight: 600;
}

.meta-item {
    padding: 0.25rem 0;
}

.card-title {
    font-size: 1.1rem;
}

.btn {
    font-weight: 500;
}

code {
    background-color: #f8f9fa;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}
</style>
@endpush