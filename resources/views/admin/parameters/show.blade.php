@extends('layouts.admin')

@section('title', 'Parameter Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-sliders-h me-2"></i>Parameter: {{ $parameter->title }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.parameters.edit', $parameter) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('admin.parameters.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Left Column - Basic Information -->
                        <div class="col-md-8">
                            <!-- Basic Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Parameter Code</label>
                                                <div>
                                                    <code class="text-primary fs-6">{{ $parameter->code }}</code>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Title</label>
                                                <div class="fs-5">{{ $parameter->title }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($parameter->description)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Description</label>
                                            <div class="text-muted">{{ $parameter->description }}</div>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Area</label>
                                                <div>
                                                    <span class="badge bg-info me-2">{{ $parameter->area->code }}</span>
                                                    <a href="{{ route('admin.areas.show', $parameter->area) }}" class="text-decoration-none">
                                                        {{ $parameter->area->title }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Field Type</label>
                                                <div>
                                                    <span class="badge bg-secondary fs-6">{{ $parameter->type_display }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Display Order</label>
                                                <div>
                                                    <span class="badge bg-light text-dark fs-6">{{ $parameter->order }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Required</label>
                                                <div>
                                                    @if($parameter->required)
                                                        <span class="badge bg-danger">Required</span>
                                                    @else
                                                        <span class="badge bg-success">Optional</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Status</label>
                                                <div>
                                                    @if($parameter->active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-warning">Inactive</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Content Data</label>
                                                <div>
                                                    @if($parameter->hasContent())
                                                        <span class="badge bg-info">Has Data</span>
                                                    @else
                                                        <span class="badge bg-light text-dark">No Data</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Field Configuration -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Field Configuration</h5>
                                </div>
                                <div class="card-body">
                                    @if($parameter->options && count($parameter->options) > 0)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Options</label>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Label</th>
                                                            <th>Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($parameter->options as $option)
                                                            <tr>
                                                                <td>{{ $option['label'] ?? 'N/A' }}</td>
                                                                <td><code>{{ $option['value'] ?? 'N/A' }}</code></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    @if($parameter->validation_rules && count($parameter->validation_rules) > 0)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Validation Rules</label>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Rule</th>
                                                            <th>Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($parameter->formatted_validation_rules as $rule => $value)
                                                            <tr>
                                                                <td>{{ ucwords(str_replace('_', ' ', $rule)) }}</td>
                                                                <td><code>{{ $value }}</code></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!$parameter->options && !$parameter->validation_rules)
                                        <p class="text-muted mb-0">No additional configuration for this field type.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Field Preview -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Field Preview</h5>
                                </div>
                                <div class="card-body">
                                    <div class="border rounded p-3 bg-light">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                {{ $parameter->title }}
                                                @if($parameter->required)
                                                    <span class="text-danger">*</span>
                                                @endif
                                                @if(!$parameter->active)
                                                    <span class="badge bg-warning ms-2">Inactive</span>
                                                @endif
                                            </label>
                                            
                                            @switch($parameter->type)
                                                @case('text')
                                                    <input type="text" class="form-control" placeholder="Enter text..." disabled>
                                                    @break
                                                @case('textarea')
                                                    <textarea class="form-control" rows="3" placeholder="Enter text..." disabled></textarea>
                                                    @break
                                                @case('number')
                                                    <input type="number" class="form-control" placeholder="Enter number..." disabled>
                                                    @break
                                                @case('date')
                                                    <input type="date" class="form-control" disabled>
                                                    @break
                                                @case('file')
                                                    <input type="file" class="form-control" disabled>
                                                    @break
                                                @case('select')
                                                    <select class="form-select" disabled>
                                                        <option>Choose option...</option>
                                                        @if($parameter->options)
                                                            @foreach($parameter->options as $option)
                                                                <option>{{ $option['label'] ?? $option['value'] ?? 'Option' }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    @break
                                                @case('checkbox')
                                                    @if($parameter->options)
                                                        @foreach($parameter->options as $option)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" disabled>
                                                                <label class="form-check-label">{{ $option['label'] ?? $option['value'] ?? 'Option' }}</label>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                    @break
                                                @case('radio')
                                                    @if($parameter->options)
                                                        @foreach($parameter->options as $option)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="preview_radio" disabled>
                                                                <label class="form-check-label">{{ $option['label'] ?? $option['value'] ?? 'Option' }}</label>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                    @break
                                            @endswitch
                                            
                                            @if($parameter->description)
                                                <div class="form-text">{{ $parameter->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Actions & System Info -->
                        <div class="col-md-4">
                            <!-- Quick Actions -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.parameters.edit', $parameter) }}" class="btn btn-warning">
                                            <i class="fas fa-edit me-2"></i>Edit Parameter
                                        </a>
                                        
                                        @if($parameter->active)
                                            <form action="{{ route('admin.parameters.update', $parameter) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="active" value="0">
                                                <button type="submit" class="btn btn-outline-warning w-100">
                                                    <i class="fas fa-pause me-2"></i>Deactivate
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.parameters.update', $parameter) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="active" value="1">
                                                <button type="submit" class="btn btn-outline-success w-100">
                                                    <i class="fas fa-play me-2"></i>Activate
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="confirmDelete('{{ $parameter->id }}', '{{ $parameter->title }}')">
                                            <i class="fas fa-trash me-2"></i>Delete Parameter
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- System Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">System Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Created</label>
                                        <div class="text-muted">{{ $parameter->created_at->format('M d, Y \\a\\t g:i A') }}</div>
                                        <small class="text-muted">{{ $parameter->created_at->diffForHumans() }}</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Last Updated</label>
                                        <div class="text-muted">{{ $parameter->updated_at->format('M d, Y \\a\\t g:i A') }}</div>
                                        <small class="text-muted">{{ $parameter->updated_at->diffForHumans() }}</small>
                                    </div>
                                    
                                    @if($parameter->deleted_at)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Deleted</label>
                                            <div class="text-danger">{{ $parameter->deleted_at->format('M d, Y \\a\\t g:i A') }}</div>
                                            <small class="text-muted">{{ $parameter->deleted_at->diffForHumans() }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Statistics -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <div class="fs-4 fw-bold text-primary">{{ $parameter->area->parameters()->count() }}</div>
                                                <small class="text-muted">Parameters in Area</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="fs-4 fw-bold text-info">{{ $parameter->hasContent() ? 'Yes' : 'No' }}</div>
                                            <small class="text-muted">Has Content Data</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                
                @if($parameter->hasContent())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This parameter has existing content data. 
                        Deleting this parameter will also delete all associated content data.
                    </div>
                @endif
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action cannot be undone.
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

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-label.fw-bold {
    color: #495057;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

code {
    background-color: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.border-end {
    border-right: 1px solid #dee2e6 !important;
}

.sticky-top {
    top: 1rem;
}
</style>
@endpush