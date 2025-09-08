@extends('user.layout')

@section('title', 'Dashboard')

@section('page-header')
@endsection

@section('page-title', 'Dashboard')
@section('page-description', 'Welcome back, {{ auth()->user()->first_name }}!')

@section('page-actions')
    @can('create', App\Models\ParameterContent::class)
        <x-button variant="primary" icon="fas fa-plus" href="{{ route('user.parameter-contents.create') }}">
            Add Content
        </x-button>
    @endcan
@endsection

@section('content')
<!-- Role-specific Welcome Message -->
<div class="row mb-4">
    <div class="col-12">
        <x-alert type="info" :dismissible="false">
            <strong>Welcome, {{ ucfirst(str_replace('_', ' ', $userRole)) }}!</strong>
            You have access to {{ $userRole }}-specific features and content management.
        </x-alert>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <x-card class="bg-primary text-white feature-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="h4 mb-0">{{ number_format($stats['my_contents']) }}</div>
                    <div class="small">My Content</div>
                </div>
                <div class="ms-3">
                    <i class="fas fa-file-alt fa-2x opacity-75"></i>
                </div>
            </div>
        </x-card>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <x-card class="bg-warning text-white feature-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="h4 mb-0">{{ number_format($stats['pending_contents']) }}</div>
                    <div class="small">Pending Review</div>
                </div>
                <div class="ms-3">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </x-card>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <x-card class="bg-success text-white feature-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="h4 mb-0">{{ number_format($stats['approved_contents']) }}</div>
                    <div class="small">Approved</div>
                </div>
                <div class="ms-3">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </x-card>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        @if(isset($stats['my_colleges']))
            <x-card class="bg-info text-white feature-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="h4 mb-0">{{ number_format($stats['my_colleges']) }}</div>
                        <div class="small">My Colleges</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-university fa-2x opacity-75"></i>
                    </div>
                </div>
            </x-card>
        @elseif(isset($stats['my_accreditations']))
            <x-card class="bg-info text-white feature-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="h4 mb-0">{{ number_format($stats['my_accreditations']) }}</div>
                        <div class="small">My Accreditations</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-certificate fa-2x opacity-75"></i>
                    </div>
                </div>
            </x-card>
        @else
            <x-card class="bg-secondary text-white feature-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="h4 mb-0">{{ auth()->user()->created_at->diffInDays() }}</div>
                        <div class="small">Days Active</div>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-calendar fa-2x opacity-75"></i>
                    </div>
                </div>
            </x-card>
        @endif
    </div>
</div>

<div class="row">
    <!-- Recent Content Submissions -->
    <div class="col-lg-8 mb-4">
        <x-card title="Recent Content Submissions">
            <x-slot name="header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Content Submissions</h5>
                    <x-button variant="outline-primary" size="sm" href="{{ route('user.parameter-contents.index') }}">
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
                                    <h6 class="mb-1">
                                        <a href="{{ route('user.parameter-contents.show', $content) }}" class="text-decoration-none">
                                            {{ $content->parameter->name ?? 'Unknown Parameter' }}
                                        </a>
                                    </h6>
                                    <p class="mb-1 text-muted small">{{ Str::limit($content->content ?? 'No content', 100) }}</p>
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
                <div class="text-center py-4">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-3">No content submissions yet.</p>
                    @can('create', App\Models\ParameterContent::class)
                        <x-button variant="primary" href="{{ route('user.parameter-contents.create') }}">
                            Create Your First Content
                        </x-button>
                    @endcan
                </div>
            @endif
        </x-card>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4 mb-4">
        <x-card title="Quick Actions">
            <div class="d-grid gap-2">
                @can('view', App\Models\College::class)
                    <x-button variant="outline-primary" icon="fas fa-university" href="{{ route('user.colleges.index') }}">
                        Manage Colleges
                    </x-button>
                @endcan
                
                @can('view', App\Models\Parameter::class)
                    <x-button variant="outline-primary" icon="fas fa-cogs" href="{{ route('user.parameters.index') }}">
                        View Parameters
                    </x-button>
                @endcan
                
                @can('create', App\Models\ParameterContent::class)
                    <x-button variant="outline-primary" icon="fas fa-plus" href="{{ route('user.parameter-contents.create') }}">
                        Add Content
                    </x-button>
                @endcan
                
                @hasanyrole('staff')
                    <x-button variant="outline-primary" icon="fas fa-certificate" href="{{ route('user.accreditations.index') }}">
                        My Accreditations
                    </x-button>
                @endhasanyrole
                
                @can('view', App\Models\SwotEntry::class)
                    <x-button variant="outline-primary" icon="fas fa-chart-line" href="{{ route('user.swot.index') }}">
                        SWOT Analysis
                    </x-button>
                @endcan
                
                <hr>
                
                <x-button variant="outline-secondary" icon="fas fa-user" href="{{ route('user.profile') }}">
                    Edit Profile
                </x-button>
                
                <x-button variant="outline-secondary" icon="fas fa-cog" href="{{ route('user.settings') }}">
                    Settings
                </x-button>
            </div>
        </x-card>
    </div>
</div>

<!-- Role-specific Content -->
@if($userRole === 'coordinator' && $userColleges->count() > 0)
<div class="row">
    <div class="col-12 mb-4">
        <x-card title="My Colleges">
            <div class="row">
                @foreach($userColleges as $college)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="card-title">{{ $college->name }}</h6>
                                <p class="card-text text-muted small">{{ $college->description ?? 'No description available' }}</p>
                                <x-button variant="primary" size="sm" href="{{ route('user.colleges.show', $college) }}">
                                    View Details
                                </x-button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>
@endif

@if($userRole === 'staff' && $userAccreditations->count() > 0)
<div class="row">
    <div class="col-12 mb-4">
        <x-card title="My Accreditations">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>College</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($userAccreditations as $accreditation)
                            <tr>
                                <td>{{ $accreditation->college->name ?? 'Unknown' }}</td>
                                <td>
                                    <span class="badge bg-{{ $accreditation->status === 'completed' ? 'success' : ($accreditation->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                                    </span>
                                </td>
                                <td>{{ $accreditation->start_date ? $accreditation->start_date->format('M d, Y') : 'Not set' }}</td>
                                <td>
                                    <x-button variant="outline-primary" size="sm" href="{{ route('user.accreditations.show', $accreditation) }}">
                                        View
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to feature cards
        const featureCards = document.querySelectorAll('.feature-card');
        featureCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Auto-refresh dashboard every 10 minutes
        setInterval(function() {
            // You can implement AJAX refresh here if needed
        }, 600000);
    });
</script>
@endpush