@extends('admin.layout')

@section('title', 'Create New User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Create New User</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Users
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    
                    <!-- Basic Information -->
                    <div class="mb-4">
                        <h5 class="card-title mb-3">Basic Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">Employee ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                       id="employee_id" name="employee_id" value="{{ old('employee_id') }}" required>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-4">
                        <h5 class="card-title mb-3">Password</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Role Assignment -->
                    <div class="mb-4">
                        <h5 class="card-title mb-3">Role Assignment <span class="text-danger">*</span></h5>
                        <p class="text-muted mb-3">Select one or more roles for this user. Users can have multiple roles.</p>
                        
                        @error('roles')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-6 mb-3">
                                    <div class="card border">
                                        <div class="card-body p-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="role_{{ $role->name }}" name="roles[]" 
                                                       value="{{ $role->name }}"
                                                       {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="role_{{ $role->name }}">
                                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                                </label>
                                            </div>
                                            <small class="text-muted">
                                                @switch($role->name)
                                                    @case('admin')
                                                        Full system access and user management
                                                        @break
                                                    @case('dean')
                                                        View access to most features for oversight
                                                        @break
                                                    @case('overall_coordinator')
                                                        Management and approval capabilities
                                                        @break
                                                    @case('chairperson')
                                                        College-specific management and area oversight
                                                        @break
                                                    @case('faculty')
                                                        Content creation and management
                                                        @break
                                                    @case('accreditor_lead')
                                                        Lead accreditation team with enhanced access
                                                        @break
                                                    @case('accreditor_member')
                                                        Accreditation team member access
                                                        @break
                                                    @default
                                                        Standard user access
                                                @endswitch
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Help Sidebar -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Role Descriptions</h6>
                <div class="accordion" id="roleAccordion">
                    @foreach($roles as $index => $role)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse" 
                                 data-bs-parent="#roleAccordion">
                                <div class="accordion-body">
                                    @switch($role->name)
                                        @case('admin')
                                            <strong>Administrator:</strong> Complete system access including user management, system settings, and all administrative functions.
                                            @break
                                        @case('dean')
                                            <strong>Dean:</strong> View access to most system features for administrative oversight and reporting.
                                            @break
                                        @case('overall_coordinator')
                                            <strong>Overall Coordinator:</strong> Management capabilities including college assignments, access request approvals, and system coordination.
                                            @break
                                        @case('chairperson')
                                            <strong>Chairperson:</strong> College-specific management including area creation, content management, and departmental oversight.
                                            @break
                                        @case('faculty')
                                            <strong>Faculty:</strong> Content creation and management capabilities including parameter content, SWOT analysis, and area rankings.
                                            @break
                                        @case('accreditor_lead')
                                            <strong>Accreditor Lead:</strong> Enhanced access for accreditation team leaders including tagged content access and request creation.
                                            @break
                                        @case('accreditor_member')
                                            <strong>Accreditor Member:</strong> Standard accreditation team member access with content viewing and request capabilities.
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const roleCheckboxes = document.querySelectorAll('input[name="roles[]"]');
    
    form.addEventListener('submit', function(e) {
        const checkedRoles = document.querySelectorAll('input[name="roles[]"]:checked');
        if (checkedRoles.length === 0) {
            e.preventDefault();
            alert('Please select at least one role for the user.');
            return false;
        }
    });
});
</script>
@endpush