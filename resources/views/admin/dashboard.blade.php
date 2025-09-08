@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('page-title', 'Dashboard')
@section('page-description', 'System overview and statistics')

@section('page-actions')
    <x-button variant="primary" icon="fas fa-plus" href="{{ route('admin.users.create') }}">
        Add User
    </x-button>
@endsection

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <x-card class="bg-primary text-white">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="h4 mb-0">{{ number_format($stats['total_users']) }}</div>
                    <div class="small">Total Users</div>
                </div>
                <div class="ms-3">
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </x-card>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <x-card class="bg-success text-white">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="h4 mb-0">{{ number_format($stats['total_colleges']) }}</div>
                    <div class="small">Total Colleges</div>
                </div>
                <div class="ms-3">
                    <i class="fas fa-university fa-2x opacity-75"></i>
                </div>
            </div>
        </x-card>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <x-card class="bg-warning text-white">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="h4 mb-0">{{ number_format($stats['pending_contents']) }}</div>
                    <div class="small">Pending Reviews</div>
                </div>
                <div class="ms-3">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </x-card>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <x-card class="bg-info text-white">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="h4 mb-0">{{ number_format($stats['active_accreditations']) }}</div>
                    <div class="small">Active Accreditations</div>
                </div>
                <div class="ms-3">
                    <i class="fas fa-certificate fa-2x opacity-75"></i>
                </div>
            </div>
        </x-card>
    </div>
</div>

<div class="row">
    <!-- Recent Users -->
    <div class="col-lg-6 mb-4">
        <x-card title="Recent Users">
            <x-slot name="header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Users</h5>
                    <x-button variant="outline-primary" size="sm" href="{{ route('admin.users.index') }}">
                        View All
                    </x-button>
                </div>
            </x-slot>
            
            @if($recentUsers->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentUsers as $user)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $user->first_name }} {{ $user->last_name }}</h6>
                                <p class="mb-1 text-muted small">{{ $user->email }}</p>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ ucfirst($user->role ?? 'user') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">No users found.</p>
            @endif
        </x-card>
    </div>
    
    <!-- Recent Content Submissions -->
    <div class="col-lg-6 mb-4">
        <x-card title="Recent Submissions">
            <x-slot name="header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Submissions</h5>
                    <x-button variant="outline-primary" size="sm" href="{{ route('admin.parameter-contents.index') }}">
                        View All
                    </x-button>
                </div>
            </x-slot>
            
            @if($recentContents->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentContents as $content)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $content->parameter->name ?? 'Unknown Parameter' }}</h6>
                                    <p class="mb-1 text-muted small">by {{ $content->user->first_name ?? 'Unknown' }} {{ $content->user->last_name ?? '' }}</p>
                                    <small class="text-muted">{{ $content->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-{{ $content->status === 'approved' ? 'success' : ($content->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($content->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">No submissions found.</p>
            @endif
        </x-card>
    </div>
</div>

<div class="row">
    <!-- User Role Distribution -->
    <div class="col-lg-6 mb-4">
        <x-card title="User Role Distribution">
            @if(!empty($userRoles))
                <div class="row">
                    @foreach($userRoles as $role => $count)
                        <div class="col-6 mb-3">
                            <div class="text-center">
                                <div class="h4 text-primary mb-1">{{ $count }}</div>
                                <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $role)) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">No role data available.</p>
            @endif
        </x-card>
    </div>
    
    <!-- System Information -->
    <div class="col-lg-6 mb-4">
        <x-card title="System Information">
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="text-center">
                        <div class="h5 text-info mb-1">{{ number_format($stats['total_areas']) }}</div>
                        <div class="small text-muted">Total Areas</div>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="text-center">
                        <div class="h5 text-info mb-1">{{ number_format($stats['total_parameters']) }}</div>
                        <div class="small text-muted">Total Parameters</div>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="small text-muted">
                <div class="d-flex justify-content-between">
                    <span>Laravel Version:</span>
                    <span>{{ app()->version() }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>PHP Version:</span>
                    <span>{{ PHP_VERSION }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Server Time:</span>
                    <span>{{ now()->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add any dashboard-specific JavaScript here
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh statistics every 5 minutes
        setInterval(function() {
            // You can implement AJAX refresh here if needed
        }, 300000);
    });
</script>
@endpush