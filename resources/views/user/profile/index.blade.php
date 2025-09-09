@extends('user.layout')

@section('title', 'My Profile')

@section('page-header')
@endsection

@section('page-title')
<div class="d-flex align-items-center">
    <i class="fas fa-user me-2 text-primary"></i>
    My Profile
</div>
@endsection

@section('page-description', 'Manage your account information and preferences')

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('user.profile.edit') }}" class="btn btn-warning">
        <i class="fas fa-edit me-1"></i>Edit Profile
    </a>
    <a href="{{ route('user.profile.settings') }}" class="btn btn-outline-primary">
        <i class="fas fa-cog me-1"></i>Settings
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-id-card me-2"></i>Personal Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Full Name</label>
                            <p class="fw-bold">{{ $user->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Email Address</label>
                            <p class="fw-bold">{{ $user->email }}</p>
                            @if($user->email_verified_at)
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>Verified
                            </small>
                            @else
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>Not verified
                            </small>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($user->phone)
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Phone Number</label>
                            <p>{{ $user->phone }}</p>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($user->address)
                <div class="mb-3">
                    <label class="form-label text-muted">Address</label>
                    <p>{{ $user->address }}</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Role & Permissions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Roles & Permissions
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Current Roles</label>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($user->getRoleNames() as $role)
                        <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                        @empty
                        <span class="text-muted">No roles assigned</span>
                        @endforelse
                    </div>
                </div>
                
                @if($user->getAllPermissions()->count() > 0)
                <div class="mb-3">
                    <label class="form-label text-muted">Permissions</label>
                    <div class="row">
                        @foreach($user->getAllPermissions()->groupBy('guard_name') as $guard => $permissions)
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">{{ ucfirst($guard) }} Permissions</h6>
                            @foreach($permissions as $permission)
                            <div class="mb-1">
                                <small class="badge bg-light text-dark">{{ $permission->name }}</small>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Associated Data -->
        @if($colleges->count() > 0 || $areas->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-building me-2"></i>Associated Data
                </h5>
            </div>
            <div class="card-body">
                @if($colleges->count() > 0)
                <div class="mb-3">
                    <label class="form-label text-muted">Colleges</label>
                    <div class="row">
                        @foreach($colleges as $college)
                        <div class="col-md-6 mb-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-university text-primary me-2"></i>
                                <div>
                                    <strong>{{ $college->name }}</strong>
                                    <br><small class="text-muted">{{ $college->code }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($areas->count() > 0)
                <div class="mb-0">
                    <label class="form-label text-muted">Areas</label>
                    <div class="row">
                        @foreach($areas as $area)
                        <div class="col-md-6 mb-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-layer-group text-success me-2"></i>
                                <div>
                                    <strong>{{ $area->name }}</strong>
                                    <br><small class="text-muted">{{ $area->college->name }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Profile Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Profile Stats
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1">{{ $colleges->count() }}</h4>
                            <small class="text-muted">Colleges</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1">{{ $areas->count() }}</h4>
                        <small class="text-muted">Areas</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-warning mb-1">{{ $user->getAllPermissions()->count() }}</h4>
                            <small class="text-muted">Permissions</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info mb-1">{{ $user->getRoleNames()->count() }}</h4>
                        <small class="text-muted">Roles</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Account Status -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Account Status
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Account Status:</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Email Verified:</span>
                        @if($user->email_verified_at)
                        <span class="badge bg-success">Yes</span>
                        @else
                        <span class="badge bg-warning">No</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Member Since:</span>
                        <span class="small">{{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>
                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Last Updated:</span>
                        <span class="small">{{ $user->updated_at->diffForHumans() }}</span>
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
                <div class="d-grid gap-2">
                    <a href="{{ route('user.profile.edit') }}" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit Profile
                    </a>
                    <a href="{{ route('user.profile.settings') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-cog me-1"></i>Account Settings
                    </a>
                    @if(!$user->email_verified_at)
                    <button class="btn btn-outline-success btn-sm" onclick="resendVerification()">
                        <i class="fas fa-envelope me-1"></i>Resend Verification
                    </button>
                    @endif
                    <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resendVerification() {
    if (confirm('Send email verification link to {{ $user->email }}?')) {
        // Create a form to submit the verification request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("verification.send") }}';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

@push('styles')
<style>
.card-title {
    font-size: 1.1rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
}

.border-end {
    border-right: 1px solid #dee2e6 !important;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endpush