@extends('admin.layout')

@section('title', 'User Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">User Details: {{ $user->name }}</h1>
    <div>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary me-2">
            <i class="fas fa-edit me-2"></i>Edit User
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Users
        </a>
    </div>
</div>

<div class="row">
    <!-- User Information -->
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Basic Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Full Name</label>
                            <div class="fw-semibold">{{ $user->name }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email Address</label>
                            <div class="fw-semibold">{{ $user->email }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Employee ID</label>
                            <div class="fw-semibold">{{ $user->employee_id }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Account Status</label>
                            <div>
                                @if($user->is_active ?? true)
                                    <span class="badge bg-success fs-6">Active</span>
                                @else
                                    <span class="badge bg-danger fs-6">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email Verification</label>
                            <div>
                                @if($user->email_verified_at)
                                    <span class="badge bg-success fs-6">Verified</span>
                                    <small class="text-muted d-block">{{ $user->email_verified_at->format('M d, Y') }}</small>
                                @else
                                    <span class="badge bg-warning fs-6">Unverified</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Member Since</label>
                            <div class="fw-semibold">{{ $user->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Roles and Permissions -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Roles and Permissions</h5>
                
                @if($user->roles->count() > 0)
                    <div class="row">
                        @foreach($user->roles as $role)
                            <div class="col-md-6 mb-4">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user-tag me-2"></i>
                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($role->permissions->count() > 0)
                                            <div class="mb-2">
                                                <small class="text-muted">Permissions ({{ $role->permissions->count() }}):</small>
                                            </div>
                                            <div class="permission-list" style="max-height: 200px; overflow-y: auto;">
                                                @foreach($role->permissions->sortBy('name') as $permission)
                                                    <span class="badge bg-light text-dark me-1 mb-1" title="{{ $permission->name }}">
                                                        {{ str_replace(['_', '.'], [' ', ' → '], $permission->name) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No specific permissions assigned to this role.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- All Permissions Summary -->
                    <div class="mt-4">
                        <h6 class="mb-3">All User Permissions Summary</h6>
                        @php
                            $allPermissions = $user->getAllPermissions()->sortBy('name');
                        @endphp
                        @if($allPermissions->count() > 0)
                            <div class="alert alert-info">
                                <strong>Total Permissions:</strong> {{ $allPermissions->count() }}
                            </div>
                            <div class="permission-summary" style="max-height: 300px; overflow-y: auto;">
                                @foreach($allPermissions->groupBy(function($permission) {
                                    return explode('.', $permission->name)[0];
                                }) as $module => $permissions)
                                    <div class="mb-3">
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-folder me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $module)) }}
                                        </h6>
                                        <div class="ms-3">
                                            @foreach($permissions as $permission)
                                                <span class="badge bg-secondary me-1 mb-1">
                                                    {{ str_replace($module . '.', '', $permission->name) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning">
                                This user has no permissions assigned.
                            </div>
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This user has no roles assigned. Consider assigning appropriate roles to grant system access.
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- User Avatar and Quick Actions -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="avatar-xl bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                
                <!-- Quick Actions -->
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit User
                    </a>
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-{{ ($user->is_active ?? true) ? 'warning' : 'success' }} w-100">
                                <i class="fas fa-{{ ($user->is_active ?? true) ? 'pause' : 'play' }} me-2"></i>
                                {{ ($user->is_active ?? true) ? 'Deactivate' : 'Activate' }} User
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Account Statistics -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="card-title">Account Statistics</h6>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="h4 text-primary mb-1">{{ $user->roles->count() }}</div>
                            <div class="small text-muted">Roles</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success mb-1">{{ $user->getAllPermissions()->count() }}</div>
                        <div class="small text-muted">Permissions</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Account Timeline -->
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Account Timeline</h6>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <div class="fw-semibold">Account Created</div>
                            <div class="small text-muted">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                    @if($user->email_verified_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <div class="fw-semibold">Email Verified</div>
                                <div class="small text-muted">{{ $user->email_verified_at->format('M d, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                    @endif
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <div class="fw-semibold">Last Updated</div>
                            <div class="small text-muted">{{ $user->updated_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-xl {
    width: 80px;
    height: 80px;
    font-size: 32px;
}

.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -15px;
    top: 20px;
    height: calc(100% - 10px);
    width: 2px;
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -20px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.permission-list .badge,
.permission-summary .badge {
    font-size: 0.75em;
}
</style>
@endpush