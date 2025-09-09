@extends('layouts.admin')

@section('title', 'Accreditation Details')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.accreditations.index') }}">Accreditations</a></li>
            <li class="breadcrumb-item active">{{ $accreditation->title }}</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $accreditation->title }}</h1>
            <p class="mb-0 text-muted">Accreditation Details</p>
        </div>
        <div>
            <a href="{{ route('admin.accreditations.edit', $accreditation) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.accreditations.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Title:</label>
                                <p class="mb-0">{{ $accreditation->title }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">College:</label>
                                <p class="mb-0">{{ $accreditation->college->name }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Academic Year:</label>
                                <p class="mb-0">{{ $accreditation->academicYear->name }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Type:</label>
                                <p class="mb-0">
                                    <span class="badge badge-info">{{ ucfirst($accreditation->type) }}</span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Status:</label>
                                <p class="mb-0">
                                    @php
                                        $statusColors = [
                                            'planning' => 'warning',
                                            'in_progress' => 'info',
                                            'under_review' => 'primary',
                                            'completed' => 'secondary',
                                            'accredited' => 'success',
                                            'denied' => 'danger',
                                            'suspended' => 'dark'
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $statusColors[$accreditation->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                                    </span>
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Accrediting Body:</label>
                                <p class="mb-0">{{ $accreditation->accrediting_body }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Assigned Lead:</label>
                                <p class="mb-0">{{ $accreditation->assignedLead->name ?? 'Not assigned' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Active:</label>
                                <p class="mb-0">
                                    <span class="badge badge-{{ $accreditation->is_active ? 'success' : 'danger' }}">
                                        {{ $accreditation->is_active ? 'Yes' : 'No' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    @if($accreditation->description)
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Description:</label>
                            <p class="mb-0">{{ $accreditation->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Start Date:</label>
                                <p class="mb-0">{{ $accreditation->start_date ? $accreditation->start_date->format('F d, Y') : 'Not set' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">End Date:</label>
                                <p class="mb-0">{{ $accreditation->end_date ? $accreditation->end_date->format('F d, Y') : 'Not set' }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Visit Date:</label>
                                <p class="mb-0">{{ $accreditation->visit_date ? $accreditation->visit_date->format('F d, Y') : 'Not scheduled' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Report Due Date:</label>
                                <p class="mb-0">{{ $accreditation->report_due_date ? $accreditation->report_due_date->format('F d, Y') : 'Not set' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            @if($accreditation->requirements || $accreditation->notes)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Additional Information</h6>
                    </div>
                    <div class="card-body">
                        @if($accreditation->requirements)
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Requirements:</label>
                                <div class="border p-3 bg-light">
                                    {!! nl2br(e($accreditation->requirements)) !!}
                                </div>
                            </div>
                        @endif
                        
                        @if($accreditation->notes)
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Notes:</label>
                                <div class="border p-3 bg-light">
                                    {!! nl2br(e($accreditation->notes)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.accreditations.edit', $accreditation) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Accreditation
                        </a>
                        
                        @if($accreditation->is_active)
                            <form method="POST" action="{{ route('admin.accreditations.bulk-update') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="accreditation_ids[]" value="{{ $accreditation->id }}">
                                <input type="hidden" name="action" value="deactivate">
                                <button type="submit" class="btn btn-secondary w-100" 
                                        onclick="return confirm('Are you sure you want to deactivate this accreditation?')">
                                    <i class="fas fa-pause"></i> Deactivate
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.accreditations.bulk-update') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="accreditation_ids[]" value="{{ $accreditation->id }}">
                                <input type="hidden" name="action" value="activate">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-play"></i> Activate
                                </button>
                            </form>
                        @endif
                        
                        <form method="POST" action="{{ route('admin.accreditations.destroy', $accreditation) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Are you sure you want to delete this accreditation? This action cannot be undone.')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Created:</small><br>
                        <span class="font-weight-bold">{{ $accreditation->created_at->format('F d, Y g:i A') }}</span>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted">Last Updated:</small><br>
                        <span class="font-weight-bold">{{ $accreditation->updated_at->format('F d, Y g:i A') }}</span>
                    </div>
                    
                    @if($accreditation->created_by)
                        <div class="mb-2">
                            <small class="text-muted">Created By:</small><br>
                            <span class="font-weight-bold">{{ $accreditation->creator->name ?? 'System' }}</span>
                        </div>
                    @endif
                    
                    @if($accreditation->updated_by)
                        <div class="mb-2">
                            <small class="text-muted">Last Updated By:</small><br>
                            <span class="font-weight-bold">{{ $accreditation->updater->name ?? 'System' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Progress Overview -->
            @if($accreditation->status !== 'planning')
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Progress Overview</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $progressSteps = [
                                'planning' => 'Planning Phase',
                                'in_progress' => 'In Progress',
                                'under_review' => 'Under Review',
                                'completed' => 'Completed',
                                'accredited' => 'Accredited'
                            ];
                            
                            $currentStep = array_search($accreditation->status, array_keys($progressSteps));
                        @endphp
                        
                        @foreach($progressSteps as $step => $label)
                            @php
                                $stepIndex = array_search($step, array_keys($progressSteps));
                                $isCompleted = $stepIndex <= $currentStep;
                                $isCurrent = $step === $accreditation->status;
                            @endphp
                            
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3">
                                    @if($isCompleted)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @elseif($isCurrent)
                                        <i class="fas fa-clock text-warning"></i>
                                    @else
                                        <i class="far fa-circle text-muted"></i>
                                    @endif
                                </div>
                                <div class="{{ $isCurrent ? 'font-weight-bold text-primary' : ($isCompleted ? 'text-success' : 'text-muted') }}">
                                    {{ $label }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    font-size: 0.75em;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.form-label.font-weight-bold {
    color: #5a5c69;
    margin-bottom: 0.25rem;
}

.border.p-3.bg-light {
    border-radius: 0.35rem;
    background-color: #f8f9fa !important;
}
</style>
@endpush