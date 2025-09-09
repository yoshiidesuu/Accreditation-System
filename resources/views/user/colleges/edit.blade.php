@extends('user.layout')

@section('title', 'Edit College')

@section('page-header')
@endsection

@section('page-title', 'Edit College')
@section('page-description', 'Update college information and settings')

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('user.colleges.show', $college) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to College
    </a>
    <a href="{{ route('user.colleges.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-list me-1"></i>All Colleges
    </a>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>Edit College: {{ $college->name }}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('user.colleges.update', $college) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">College Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $college->name) }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">College Code</label>
                                <input type="text" class="form-control" id="code" name="code" 
                                       value="{{ $college->code }}" readonly>
                                <div class="form-text">College code cannot be changed</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact" class="form-label">Contact Information</label>
                                <input type="text" class="form-control @error('contact') is-invalid @enderror" 
                                       id="contact" name="contact" value="{{ old('contact', $college->contact) }}">
                                @error('contact')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Phone, email, or other contact details</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="coordinator" class="form-label">Coordinator</label>
                                <input type="text" class="form-control" id="coordinator" 
                                       value="{{ $college->coordinator ? $college->coordinator->first_name . ' ' . $college->coordinator->last_name : 'Not assigned' }}" 
                                       readonly>
                                <div class="form-text">Coordinator assignment is managed by administrators</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3">{{ old('address', $college->address) }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="mb-4">
                        <label class="form-label">Additional Information</label>
                        <div id="meta-fields">
                            @if($college->meta && count($college->meta) > 0)
                                @foreach($college->meta as $key => $value)
                                <div class="row mb-2 meta-field">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="meta_keys[]" 
                                               value="{{ $key }}" placeholder="Field name">
                                    </div>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control" name="meta_values[]" 
                                               value="{{ is_array($value) ? implode(', ', $value) : $value }}" 
                                               placeholder="Field value">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-meta">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="add-meta">
                            <i class="fas fa-plus me-1"></i>Add Field
                        </button>
                        <div class="form-text">Add custom fields for additional college information</div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('user.colleges.show', $college) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update College
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add meta field functionality
    document.getElementById('add-meta').addEventListener('click', function() {
        const metaFields = document.getElementById('meta-fields');
        const newField = document.createElement('div');
        newField.className = 'row mb-2 meta-field';
        newField.innerHTML = `
            <div class="col-md-4">
                <input type="text" class="form-control" name="meta_keys[]" placeholder="Field name">
            </div>
            <div class="col-md-7">
                <input type="text" class="form-control" name="meta_values[]" placeholder="Field value">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger btn-sm remove-meta">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        metaFields.appendChild(newField);
    });
    
    // Remove meta field functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-meta')) {
            e.target.closest('.meta-field').remove();
        }
    });
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        if (!name) {
            e.preventDefault();
            alert('College name is required.');
            document.getElementById('name').focus();
            return false;
        }
        
        // Process meta fields
        const metaKeys = document.querySelectorAll('input[name="meta_keys[]"]');
        const metaValues = document.querySelectorAll('input[name="meta_values[]"]');
        const metaData = {};
        
        metaKeys.forEach((keyInput, index) => {
            const key = keyInput.value.trim();
            const value = metaValues[index].value.trim();
            if (key && value) {
                metaData[key] = value;
            }
        });
        
        // Add hidden input for meta data
        const metaInput = document.createElement('input');
        metaInput.type = 'hidden';
        metaInput.name = 'meta';
        metaInput.value = JSON.stringify(metaData);
        form.appendChild(metaInput);
    });
});
</script>
@endpush

@push('styles')
<style>
.meta-field {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.remove-meta {
    padding: 0.375rem 0.5rem;
}
</style>
@endpush