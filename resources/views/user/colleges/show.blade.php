@extends('user.layout')

@section('title', $college->name)

@section('page-header')
@endsection

@section('page-title', $college->name)
@section('page-description', 'College Details and Information')

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('user.colleges.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to List
    </a>
    @can('update', $college)
    <a href="{{ route('user.colleges.edit', $college) }}" class="btn btn-primary">
        <i class="fas fa-edit me-1"></i>Edit College
    </a>
    @endcan
</div>
@endsection

@section('content')
<div class="row">
    <!-- College Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>College Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">College Name</label>
                            <p class="fw-bold">{{ $college->name }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">College Code</label>
                            <p><span class="badge bg-secondary fs-6">{{ $college->code }}</span></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Contact Information</label>
                            <p>{{ $college->contact ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Coordinator</label>
                            @if($college->coordinator)
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                    {{ strtoupper(substr($college->coordinator->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="mb-0 fw-bold">{{ $college->coordinator->first_name }} {{ $college->coordinator->last_name }}</p>
                                    <small class="text-muted">{{ $college->coordinator->email }}</small>
                                </div>
                            </div>
                            @else
                            <p class="text-muted">Not assigned</p>
                            @endif
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Academic Year</label>
                            @if($college->academicYear)
                            <p>
                                <span class="badge {{ $college->academicYear->active ? 'bg-success' : 'bg-warning' }} fs-6">
                                    {{ $college->academicYear->label }}
                                </span>
                            </p>
                            <small class="text-muted">
                                {{ $college->academicYear->start_date->format('M d, Y') }} - 
                                {{ $college->academicYear->end_date->format('M d, Y') }}
                            </small>
                            @else
                            <p class="text-muted">Not set</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($college->address)
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label text-muted">Address</label>
                            <p>{{ $college->address }}</p>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($college->meta && count($college->meta) > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label text-muted">Additional Information</label>
                            <div class="bg-light p-3 rounded">
                                @foreach($college->meta as $key => $value)
                                <div class="row mb-2">
                                    <div class="col-sm-4">
                                        <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ is_array($value) ? implode(', ', $value) : $value }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Areas and Statistics -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Quick Stats
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1">{{ $college->areas->count() }}</h4>
                            <small class="text-muted">Areas</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1">{{ $college->areas->sum(function($area) { return $area->parameters->count(); }) }}</h4>
                        <small class="text-muted">Parameters</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Areas List -->
        @if($college->areas->count() > 0)
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-layer-group me-2"></i>Areas ({{ $college->areas->count() }})
                </h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($college->areas->take(10) as $area)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <h6 class="mb-1">{{ $area->name }}</h6>
                            <small class="text-muted">{{ $area->code }} • Level {{ $area->level }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark">{{ $area->parameters->count() }}</span>
                            @can('view', $area)
                            <a href="{{ route('user.areas.show', $area) }}" class="btn btn-sm btn-outline-primary ms-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($college->areas->count() > 10)
                <div class="text-center mt-3">
                    <a href="{{ route('user.areas.index', ['college_id' => $college->id]) }}" class="btn btn-sm btn-outline-primary">
                        View All Areas ({{ $college->areas->count() }})
                    </a>
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-layer-group fa-2x text-muted mb-3"></i>
                <h6 class="text-muted">No Areas</h6>
                <p class="text-muted mb-3">This college doesn't have any areas yet.</p>
                @can('create', [App\Models\Area::class, $college])
                <a href="{{ route('user.areas.create', ['college_id' => $college->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Area
                </a>
                @endcan
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 16px;
    font-weight: 600;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>
@endpush