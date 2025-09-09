@extends('layouts.user')

@section('title', 'Assign Accreditors')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Assign Accreditors</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.accreditations.coordinatorTagging') }}">Coordinator Tagging</a></li>
                    <li class="breadcrumb-item active">Assign Accreditors</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('user.accreditations.coordinatorTagging') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tagging
        </a>
    </div>

    <!-- Accreditation Info -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Accreditation Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title:</label>
                        <p class="mb-0">{{ $accreditation->title }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">College:</label>
                        <p class="mb-0">{{ $accreditation->college->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Academic Year:</label>
                        <p class="mb-0">{{ $accreditation->academicYear->label ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status:</label>
                        <span class="badge bg-{{ 
                            $accreditation->status === 'approved' ? 'success' : 
                            ($accreditation->status === 'rejected' ? 'danger' : 
                            ($accreditation->status === 'under_review' ? 'info' : 'warning'))
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                        </span>
                    </div>
                </div>
            </div>
            @if($accreditation->description)
                <div class="mb-3">
                    <label class="form-label fw-bold">Description:</label>
                    <p class="mb-0">{{ $accreditation->description }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Assignment Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Assign Accreditors</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('user.accreditations.assignAccreditors', $accreditation) }}">
                @csrf
                @method('PUT')

                <!-- Lead Accreditor -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="assigned_lead_id" class="form-label fw-bold">Lead Accreditor <span class="text-danger">*</span></label>
                        <select class="form-select @error('assigned_lead_id') is-invalid @enderror" 
                                id="assigned_lead_id" name="assigned_lead_id" required>
                            <option value="">Select Lead Accreditor</option>
                            @foreach($accreditorLeads as $lead)
                                <option value="{{ $lead->id }}" 
                                        {{ old('assigned_lead_id', $accreditation->assigned_lead_id) == $lead->id ? 'selected' : '' }}>
                                    {{ $lead->name }} ({{ $lead->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_lead_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">The lead accreditor will have enhanced access and coordination responsibilities.</small>
                    </div>
                </div>

                <!-- Member Accreditors -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="assigned_members" class="form-label fw-bold">Member Accreditors</label>
                        <select class="form-select @error('assigned_members') is-invalid @enderror" 
                                id="assigned_members" name="assigned_members[]" multiple>
                            @foreach($accreditorMembers as $member)
                                @php
                                    $currentMembers = old('assigned_members', 
                                        is_string($accreditation->assigned_members) ? 
                                        json_decode($accreditation->assigned_members, true) : 
                                        $accreditation->assigned_members
                                    ) ?? [];
                                @endphp
                                <option value="{{ $member->id }}" 
                                        {{ in_array($member->id, $currentMembers) ? 'selected' : '' }}>
                                    {{ $member->name }} ({{ $member->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_members')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Hold Ctrl (Cmd on Mac) to select multiple members. Members will have standard accreditation access.</small>
                    </div>
                </div>

                <!-- Current Assignments Display -->
                @if($accreditation->assigned_lead_id || ($accreditation->assigned_members && count(json_decode($accreditation->assigned_members, true) ?? []) > 0))
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Current Assignments</h6>
                                
                                @if($accreditation->assigned_lead_id)
                                    <div class="mb-2">
                                        <strong>Lead Accreditor:</strong> 
                                        {{ $accreditation->assignedLead->name ?? 'Unknown' }} 
                                        ({{ $accreditation->assignedLead->email ?? 'N/A' }})
                                    </div>
                                @endif
                                
                                @if($accreditation->assigned_members)
                                    @php
                                        $memberIds = is_string($accreditation->assigned_members) ? 
                                            json_decode($accreditation->assigned_members, true) : 
                                            $accreditation->assigned_members;
                                        $currentMembers = $accreditorMembers->whereIn('id', $memberIds ?? []);
                                    @endphp
                                    @if($currentMembers->count() > 0)
                                        <div>
                                            <strong>Member Accreditors:</strong>
                                            <ul class="mb-0 mt-1">
                                                @foreach($currentMembers as $member)
                                                    <li>{{ $member->name }} ({{ $member->email }})</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('user.accreditations.coordinatorTagging') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Assignments
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for better multi-select experience
    if (typeof $.fn.select2 !== 'undefined') {
        $('#assigned_members').select2({
            placeholder: 'Select member accreditors...',
            allowClear: true,
            width: '100%'
        });
        
        $('#assigned_lead_id').select2({
            placeholder: 'Select lead accreditor...',
            allowClear: true,
            width: '100%'
        });
    }
    
    // Form validation
    $('form').on('submit', function(e) {
        const leadId = $('#assigned_lead_id').val();
        const memberIds = $('#assigned_members').val() || [];
        
        // Check if lead is also selected as member
        if (leadId && memberIds.includes(leadId)) {
            e.preventDefault();
            alert('The lead accreditor cannot also be selected as a member accreditor. Please choose different users.');
            return false;
        }
        
        // Confirm assignment changes
        if (!confirm('Are you sure you want to update the accreditor assignments? This will affect access permissions for the selected users.')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush