@extends('user.layout')

@section('title', $parameter->title)

@section('page-header')
@endsection

@section('page-title')
<div class="d-flex align-items-center">
    <i class="fas fa-cog me-2 text-primary"></i>
    {{ $parameter->title }}
</div>
@endsection

@section('page-description')
Parameter details and configuration
@endsection

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('user.parameters.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Parameters
    </a>
    
    @can('update', $parameter)
    <a href="{{ route('user.parameters.edit', $parameter) }}" class="btn btn-warning">
        <i class="fas fa-edit me-1"></i>Edit Parameter
    </a>
    @endcan
    
    @can('delete', $parameter)
    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
        <i class="fas fa-trash me-1"></i>Delete
    </button>
    @endcan
</div>
@endsection

@section('content')
<div class="row">
    <!-- Parameter Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Parameter Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Parameter Title</label>
                            <p class="fw-bold">{{ $parameter->title }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Parameter Code</label>
                            <p class="fw-bold font-monospace">{{ $parameter->code }}</p>
                        </div>
                    </div>
                </div>
                
                @if($parameter->description)
                <div class="mb-3">
                    <label class="form-label text-muted">Description</label>
                    <p>{{ $parameter->description }}</p>
                </div>
                @endif
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted">Field Type</label>
                            <div class="d-flex align-items-center">
                                @switch($parameter->type)
                                    @case('text')
                                    @case('textarea')
                                        <i class="fas fa-font text-primary me-2"></i>
                                        @break
                                    @case('number')
                                        <i class="fas fa-hashtag text-success me-2"></i>
                                        @break
                                    @case('email')
                                        <i class="fas fa-envelope text-info me-2"></i>
                                        @break
                                    @case('url')
                                        <i class="fas fa-link text-warning me-2"></i>
                                        @break
                                    @case('date')
                                        <i class="fas fa-calendar text-secondary me-2"></i>
                                        @break
                                    @case('select')
                                    @case('radio')
                                    @case('checkbox')
                                        <i class="fas fa-list text-purple me-2"></i>
                                        @break
                                    @case('file')
                                        <i class="fas fa-file text-danger me-2"></i>
                                        @break
                                    @default
                                        <i class="fas fa-cog text-muted me-2"></i>
                                @endswitch
                                <span class="badge bg-light text-dark">{{ ucfirst($parameter->type) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted">Required</label>
                            <div>
                                @if($parameter->required)
                                <span class="badge bg-danger">Required</span>
                                @else
                                <span class="badge bg-secondary">Optional</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted">Display Order</label>
                            <p class="fw-bold">{{ $parameter->order ?? 'Not set' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                <span class="badge {{ $parameter->active ? 'bg-success' : 'bg-warning' }}">
                                    {{ $parameter->active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @if($parameter->placeholder)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Placeholder Text</label>
                            <p class="text-muted fst-italic">{{ $parameter->placeholder }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Validation Rules -->
        @if($parameter->validation_rules)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Validation Rules
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($parameter->validation_rules as $rule => $value)
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $rule)) }}:</span>
                            <span class="fw-bold">{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <!-- Options (for select, radio, checkbox) -->
        @if(in_array($parameter->type, ['select', 'checkbox', 'radio']) && $parameter->options)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Available Options
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($parameter->options as $index => $option)
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-dark me-2">{{ $index + 1 }}</span>
                            <span>{{ is_array($option) ? $option['label'] ?? $option['value'] : $option }}</span>
                            @if(is_array($option) && isset($option['value']) && $option['value'] !== ($option['label'] ?? $option['value']))
                            <small class="text-muted ms-2">({{ $option['value'] }})</small>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Area Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-layer-group me-2"></i>Area Information
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Area Name</label>
                    <p class="fw-bold">{{ $parameter->area->name }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">College</label>
                    <p>{{ $parameter->area->college->name }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Academic Year</label>
                    <p>{{ $parameter->area->academicYear->label }}</p>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted">Area Level</label>
                    <span class="badge bg-info">Level {{ $parameter->area->level }}</span>
                </div>
            </div>
        </div>
        
        <!-- Meta Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>Meta Information
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Created</label>
                    <p class="small">
                        {{ $parameter->created_at->format('M d, Y \\a\\t g:i A') }}
                        <br><span class="text-muted">{{ $parameter->created_at->diffForHumans() }}</span>
                    </p>
                </div>
                @if($parameter->updated_at && $parameter->updated_at != $parameter->created_at)
                <div class="mb-3">
                    <label class="form-label text-muted">Last Updated</label>
                    <p class="small">
                        {{ $parameter->updated_at->format('M d, Y \\a\\t g:i A') }}
                        <br><span class="text-muted">{{ $parameter->updated_at->diffForHumans() }}</span>
                    </p>
                </div>
                @endif
                <div class="mb-0">
                    <label class="form-label text-muted">Parameter ID</label>
                    <p class="font-monospace small">{{ $parameter->id }}</p>
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
                <div class="d-grid gap-2">
                    <a href="{{ route('user.areas.show', $parameter->area) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-layer-group me-1"></i>View Area
                    </a>
                    @can('create', App\Models\Parameter::class)
                    <a href="{{ route('user.parameters.create', ['area_id' => $parameter->area_id]) }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-plus me-1"></i>Add Parameter to Area
                    </a>
                    @endcan
                    @if($parameter->active)
                    <button class="btn btn-outline-warning btn-sm" onclick="toggleStatus(false)">
                        <i class="fas fa-pause me-1"></i>Deactivate
                    </button>
                    @else
                    <button class="btn btn-outline-success btn-sm" onclick="toggleStatus(true)">
                        <i class="fas fa-play me-1"></i>Activate
                    </button>
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
                <p>Are you sure you want to delete the parameter <strong>{{ $parameter->title }}</strong>?</p>
                <p class="text-muted">This action cannot be undone and will remove all associated content.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('user.parameters.destroy', $parameter) }}" style="display: inline;">
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
function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function toggleStatus(active) {
    if (confirm(`Are you sure you want to ${active ? 'activate' : 'deactivate'} this parameter?`)) {
        // Create a form to submit the status change
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("user.parameters.update", $parameter) }}';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Add method override
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PATCH';
        form.appendChild(methodInput);
        
        // Add active status
        const activeInput = document.createElement('input');
        activeInput.type = 'hidden';
        activeInput.name = 'active';
        activeInput.value = active ? '1' : '0';
        form.appendChild(activeInput);
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

@push('styles')
<style>
.text-purple {
    color: #6f42c1 !important;
}

.font-monospace {
    font-family: 'Courier New', Courier, monospace;
}

.card-title {
    font-size: 1.1rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
}
</style>
@endpush