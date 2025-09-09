@extends('admin.layout')

@section('title', 'Edit User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit User: {{ $user->name }}</h1>
    <div>
        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info me-2">
            <i class="fas fa-eye me-2"></i>View User
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information -->
                    <div class="mb-4">
                        <h5 class="card-title mb-3">Basic Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">Employee ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                       id="employee_id" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" required>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-4">
                        <h5 class="card-title mb-3">Password</h5>
                        <p class="text-muted mb-3">Leave password fields empty to keep current password.</p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation">
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
                        
                        <!-- Current Roles Alert -->
                        @if($user->roles->count() > 0)
                            <div class="alert alert-info">
                                <strong>Current Roles:</strong>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary me-1">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
                                @endforeach
                            </div>
                        @endif
                        
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-6 mb-3">
                                    <div class="card border {{ $user->hasRole($role->name) ? 'border-primary' : '' }}">
                                        <div class="card-body p-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="role_{{ $role->name }}" name="roles[]" 
                                                       value="{{ $role->name }}"
                                                       {{ in_array($role->name, old('roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="role_{{ $role->name }}">
                                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                                    @if($user->hasRole($role->name))
                                                        <span class="badge bg-success ms-1">Current</span>
                                                    @endif
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
                            <i class="fas fa-save me-2"></i>Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- User Info Sidebar -->
    <div class="col-lg-4">
        <!-- User Summary -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-2">{{ $user->email }}</p>
                <p class="text-muted mb-3">ID: {{ $user->employee_id }}</p>
                <div class="d-flex justify-content-center gap-2">
                    @if($user->is_active ?? true)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                    <span class="badge bg-info">{{ $user->roles->count() }} Role(s)</span>
                </div>
            </div>
        </div>
        
        <!-- Account Information -->
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Account Information</h6>
                <div class="mb-2">
                    <small class="text-muted">Created:</small>
                    <div>{{ $user->created_at->format('M d, Y \a\t g:i A') }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Last Updated:</small>
                    <div>{{ $user->updated_at->format('M d, Y \a\t g:i A') }}</div>
                </div>
                @if($user->email_verified_at)
                    <div class="mb-2">
                        <small class="text-muted">Email Verified:</small>
                        <div>{{ $user->email_verified_at->format('M d, Y') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-lg {
    width: 64px;
    height: 64px;
    font-size: 24px;
}
</style>
@endpush

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