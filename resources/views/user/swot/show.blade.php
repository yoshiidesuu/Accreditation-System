@extends('layouts.user')

@section('title', 'SWOT Entry Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="mb-4">
        <h1 class="h3 mb-0 text-gray-800">SWOT Entry Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.swot.index') }}">SWOT Analysis</a></li>
                <li class="breadcrumb-item active" aria-current="page">Entry Details</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">SWOT Entry Information</h6>
                    <span class="badge bg-{{ $swot->status === 'approved' ? 'success' : ($swot->status === 'rejected' ? 'danger' : 'warning') }} fs-6">
                        {{ ucfirst($swot->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>College:</strong>
                            <p class="mb-0">{{ $swot->college->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Area:</strong>
                            <p class="mb-0">{{ $swot->area->name }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>SWOT Type:</strong>
                        @php
                            $typeColors = [
                                'S' => 'success',
                                'W' => 'warning', 
                                'O' => 'info',
                                'T' => 'danger'
                            ];
                            $typeNames = [
                                'S' => 'Strength',
                                'W' => 'Weakness',
                                'O' => 'Opportunity', 
                                'T' => 'Threat'
                            ];
                            $typeIcons = [
                                'S' => 'fas fa-plus-circle',
                                'W' => 'fas fa-minus-circle',
                                'O' => 'fas fa-lightbulb',
                                'T' => 'fas fa-exclamation-triangle'
                            ];
                        @endphp
                        <div class="mt-2">
                            <span class="badge bg-{{ $typeColors[$swot->type] }} fs-6">
                                <i class="{{ $typeIcons[$swot->type] }}"></i> {{ $typeNames[$swot->type] }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <strong>Description:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            {{ $swot->description }}
                        </div>
                    </div>

                    @if($swot->reviewed_at && $swot->notes)
                        <div class="mb-4">
                            <strong>Reviewer Feedback:</strong>
                            <div class="alert alert-{{ $swot->status === 'approved' ? 'success' : 'danger' }} mt-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>Reviewed by:</strong> {{ $swot->reviewer->name ?? 'N/A' }}
                                    </div>
                                    <small class="text-muted">{{ $swot->reviewed_at->format('M d, Y H:i') }}</small>
                                </div>
                                <div>
                                    <strong>Notes:</strong><br>
                                    {{ $swot->notes }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('user.swot.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <div>
                            @can('update', $swot)
                                <a href="{{ route('user.swot.edit', $swot) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Entry
                                </a>
                            @endcan
                            @can('delete', $swot)
                                <form method="POST" action="{{ route('user.swot.destroy', $swot) }}" class="d-inline ms-2" 
                                      onsubmit="return confirm('Are you sure you want to delete this SWOT entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete Entry
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">Entry Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Created</h6>
                                <p class="mb-0 text-muted small">{{ $swot->created_at->format('M d, Y H:i') }}</p>
                                <p class="mb-0 text-muted small">by {{ $swot->creator->name }}</p>
                            </div>
                        </div>
                        
                        @if($swot->updated_at != $swot->created_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Last Updated</h6>
                                    <p class="mb-0 text-muted small">{{ $swot->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($swot->reviewed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $swot->status === 'approved' ? 'success' : 'danger' }}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ ucfirst($swot->status) }}</h6>
                                    <p class="mb-0 text-muted small">{{ $swot->reviewed_at->format('M d, Y H:i') }}</p>
                                    <p class="mb-0 text-muted small">by {{ $swot->reviewer->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">SWOT Type Guide</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 {{ $swot->type === 'S' ? 'border-start border-success border-3 ps-2' : '' }}">
                        <h6 class="text-success"><i class="fas fa-plus-circle"></i> Strengths</h6>
                        <p class="small text-muted mb-0">Internal positive factors that give advantages over competitors.</p>
                    </div>
                    <div class="mb-3 {{ $swot->type === 'W' ? 'border-start border-warning border-3 ps-2' : '' }}">
                        <h6 class="text-warning"><i class="fas fa-minus-circle"></i> Weaknesses</h6>
                        <p class="small text-muted mb-0">Internal negative factors that place the organization at a disadvantage.</p>
                    </div>
                    <div class="mb-3 {{ $swot->type === 'O' ? 'border-start border-info border-3 ps-2' : '' }}">
                        <h6 class="text-info"><i class="fas fa-lightbulb"></i> Opportunities</h6>
                        <p class="small text-muted mb-0">External positive factors that could provide competitive advantages.</p>
                    </div>
                    <div class="mb-0 {{ $swot->type === 'T' ? 'border-start border-danger border-3 ps-2' : '' }}">
                        <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Threats</h6>
                        <p class="small text-muted mb-0">External negative factors that could harm the organization.</p>
                    </div>
                </div>
            </div>

            @if($swot->status === 'pending')
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-warning">Pending Review</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0">
                            <i class="fas fa-clock"></i> This entry is waiting for accreditor review. 
                            You can still edit it until it's approved or rejected.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -12px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    margin-left: 20px;
}
</style>
@endpush
@endsection