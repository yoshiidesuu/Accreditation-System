@extends('layouts.user')

@section('title', 'Create SWOT Entry')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create SWOT Entry</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.swot.index') }}">SWOT Analysis</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Entry</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">SWOT Entry Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('user.swot.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="college_id" class="form-label">College <span class="text-danger">*</span></label>
                                    <select name="college_id" id="college_id" class="form-select @error('college_id') is-invalid @enderror" required>
                                        <option value="">Select College</option>
                                        @foreach($colleges as $college)
                                            <option value="{{ $college->id }}" {{ old('college_id') == $college->id ? 'selected' : '' }}>
                                                {{ $college->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('college_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="area_id" class="form-label">Area <span class="text-danger">*</span></label>
                                    <select name="area_id" id="area_id" class="form-select @error('area_id') is-invalid @enderror" required>
                                        <option value="">Select Area</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                                {{ $area->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('area_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">SWOT Type <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Select SWOT Type</option>
                                @foreach($types as $key => $value)
                                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" rows="6" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Provide a detailed description of this SWOT item..." 
                                      maxlength="2000" required>{{ old('description') }}</textarea>
                            <div class="form-text">
                                <span id="char-count">0</span>/2000 characters
                            </div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('user.swot.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create SWOT Entry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">SWOT Analysis Guide</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-success"><i class="fas fa-plus-circle"></i> Strengths</h6>
                        <p class="small text-muted">Internal positive factors that give advantages over competitors.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-warning"><i class="fas fa-minus-circle"></i> Weaknesses</h6>
                        <p class="small text-muted">Internal negative factors that place the organization at a disadvantage.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-info"><i class="fas fa-lightbulb"></i> Opportunities</h6>
                        <p class="small text-muted">External positive factors that could provide competitive advantages.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Threats</h6>
                        <p class="small text-muted">External negative factors that could harm the organization.</p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-warning">Important Notes</h6>
                </div>
                <div class="card-body">
                    <ul class="small text-muted mb-0">
                        <li>All entries require accreditor approval</li>
                        <li>Be specific and provide evidence-based descriptions</li>
                        <li>You can edit entries until they are approved</li>
                        <li>Approved entries contribute to area rankings</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Character counter
    $('#description').on('input', function() {
        const length = $(this).val().length;
        $('#char-count').text(length);
        
        if (length > 1800) {
            $('#char-count').addClass('text-warning');
        } else {
            $('#char-count').removeClass('text-warning');
        }
        
        if (length >= 2000) {
            $('#char-count').addClass('text-danger').removeClass('text-warning');
        } else {
            $('#char-count').removeClass('text-danger');
        }
    });
    
    // Trigger character count on page load
    $('#description').trigger('input');
});
</script>
@endpush
@endsection