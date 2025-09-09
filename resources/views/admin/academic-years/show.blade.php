@extends('layouts.admin')

@section('title', 'Academic Year Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Academic Year Details: {{ $academicYear->label }}</h3>
                    <div>
                        <a href="{{ route('admin.academic-years.edit', $academicYear) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.academic-years.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Academic Years
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">Basic Information</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Label:</strong></div>
                                            <div class="col-sm-8">
                                                <span class="h5">{{ $academicYear->label }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Status:</strong></div>
                                            <div class="col-sm-8">
                                                <span class="badge {{ $academicYear->status_badge }} fs-6">{{ $academicYear->status_text }}</span>
                                                @if($academicYear->isCurrent() && !$academicYear->active)
                                                    <br><small class="text-warning mt-1"><i class="fas fa-info-circle"></i> Date-based current but not set as active</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Start Date:</strong></div>
                                            <div class="col-sm-8">{{ $academicYear->start_date->format('F d, Y') }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>End Date:</strong></div>
                                            <div class="col-sm-8">{{ $academicYear->end_date->format('F d, Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">Duration & Timeline</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Duration:</strong></div>
                                            <div class="col-sm-8">
                                                {{ $academicYear->start_date->diffInDays($academicYear->end_date) }} days
                                                <small class="text-muted">({{ $academicYear->start_date->diffInMonths($academicYear->end_date) }} months)</small>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Progress:</strong></div>
                                            <div class="col-sm-8">
                                                @php
                                                    $today = \Carbon\Carbon::today();
                                                    $totalDays = $academicYear->start_date->diffInDays($academicYear->end_date);
                                                    $elapsedDays = 0;
                                                    $progress = 0;
                                                    
                                                    if ($today->gte($academicYear->start_date)) {
                                                        if ($today->lte($academicYear->end_date)) {
                                                            $elapsedDays = $academicYear->start_date->diffInDays($today);
                                                            $progress = ($elapsedDays / $totalDays) * 100;
                                                        } else {
                                                            $elapsedDays = $totalDays;
                                                            $progress = 100;
                                                        }
                                                    }
                                                @endphp
                                                
                                                <div class="progress mb-2" style="height: 20px;">
                                                    <div class="progress-bar {{ $progress >= 100 ? 'bg-success' : ($progress > 75 ? 'bg-warning' : 'bg-primary') }}" 
                                                         role="progressbar" style="width: {{ $progress }}%" 
                                                         aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                                        {{ number_format($progress, 1) }}%
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    {{ $elapsedDays }} of {{ $totalDays }} days
                                                    @if($progress < 100 && $today->gte($academicYear->start_date))
                                                        ({{ $academicYear->end_date->diffInDays($today) }} days remaining)
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Current Status:</strong></div>
                                            <div class="col-sm-8">
                                                @if($today->lt($academicYear->start_date))
                                                    <span class="badge bg-info">Upcoming</span>
                                                    <small class="text-muted d-block">Starts in {{ $today->diffInDays($academicYear->start_date) }} days</small>
                                                @elseif($today->gt($academicYear->end_date))
                                                    <span class="badge bg-secondary">Completed</span>
                                                    <small class="text-muted d-block">Ended {{ $academicYear->end_date->diffForHumans() }}</small>
                                                @else
                                                    <span class="badge bg-success">In Progress</span>
                                                    <small class="text-muted d-block">{{ $academicYear->end_date->diffForHumans() }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">System Information</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Created:</strong></div>
                                            <div class="col-sm-8">
                                                {{ $academicYear->created_at->format('M d, Y h:i A') }}
                                                <small class="text-muted d-block">{{ $academicYear->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Last Updated:</strong></div>
                                            <div class="col-sm-8">
                                                {{ $academicYear->updated_at->format('M d, Y h:i A') }}
                                                <small class="text-muted d-block">{{ $academicYear->updated_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>ID:</strong></div>
                                            <div class="col-sm-8"><code>{{ $academicYear->id }}</code></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">Quick Actions</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <!-- Toggle Active Status -->
                                            <form action="{{ route('admin.academic-years.toggle-active', $academicYear) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn {{ $academicYear->active ? 'btn-outline-secondary' : 'btn-outline-success' }} w-100">
                                                    <i class="fas {{ $academicYear->active ? 'fa-pause' : 'fa-play' }}"></i>
                                                    {{ $academicYear->active ? 'Deactivate' : 'Activate' }} Academic Year
                                                </button>
                                            </form>
                                            
                                            <!-- Edit Button -->
                                            <a href="{{ route('admin.academic-years.edit', $academicYear) }}" class="btn btn-outline-warning w-100">
                                                <i class="fas fa-edit"></i> Edit Academic Year
                                            </a>
                                            
                                            <!-- Delete Button (only if not active) -->
                                            @unless($academicYear->active)
                                                <button type="button" class="btn btn-outline-danger w-100" 
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                                                    <i class="fas fa-trash"></i> Delete Academic Year
                                                </button>
                                            @else
                                                <div class="alert alert-warning mb-0">
                                                    <small><i class="fas fa-info-circle"></i> Cannot delete active academic year</small>
                                                </div>
                                            @endunless
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
@unless($academicYear->active)
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the academic year <strong>{{ $academicYear->label }}</strong>?</p>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Warning</h6>
                    <ul class="mb-0">
                        <li>This action cannot be undone</li>
                        <li>All associated data may be affected</li>
                        <li>Consider deactivating instead of deleting</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.academic-years.destroy', $academicYear) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Academic Year</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endunless

<style>
.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
    font-weight: 600;
}

.badge.fs-6 {
    font-size: 1rem !important;
}
</style>
@endsection