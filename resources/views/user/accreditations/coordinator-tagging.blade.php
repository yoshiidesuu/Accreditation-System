@extends('layouts.user')

@section('title', 'Coordinator Tagging')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Coordinator Tagging</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Coordinator Tagging</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.accreditations.coordinatorTagging') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="college_id" class="form-label">College</label>
                    <select class="form-select" id="college_id" name="college_id">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                {{ $college->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="academic_year_id" class="form-label">Academic Year</label>
                    <select class="form-select" id="academic_year_id" name="academic_year_id">
                        <option value="">All Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('user.accreditations.coordinatorTagging') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Accreditations List -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Accreditations for Tagging</h6>
        </div>
        <div class="card-body">
            @if($accreditations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>College</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>Assigned Lead</th>
                                <th>Assigned Members</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accreditations as $accreditation)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $accreditation->title }}</div>
                                        @if($accreditation->description)
                                            <small class="text-muted">{{ Str::limit($accreditation->description, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $accreditation->college->name ?? 'N/A' }}</td>
                                    <td>{{ $accreditation->academicYear->label ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $accreditation->status === 'approved' ? 'success' : 
                                            ($accreditation->status === 'rejected' ? 'danger' : 
                                            ($accreditation->status === 'under_review' ? 'info' : 'warning'))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($accreditation->assigned_lead_id)
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="fw-bold">{{ $accreditation->assignedLead->name }}</div>
                                                    <small class="text-muted">{{ $accreditation->assignedLead->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($accreditation->assigned_members && count($accreditation->assigned_members) > 0)
                                            @php
                                                $memberIds = is_string($accreditation->assigned_members) ? json_decode($accreditation->assigned_members, true) : $accreditation->assigned_members;
                                                $members = $users->whereIn('id', $memberIds ?? []);
                                            @endphp
                                            @foreach($members as $member)
                                                <span class="badge bg-secondary me-1">{{ $member->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No members assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('user.accreditations.assignAccreditors', $accreditation) }}" 
                                               class="btn btn-sm btn-primary" title="Assign Accreditors">
                                                <i class="fas fa-user-plus"></i> Assign
                                            </a>
                                            <a href="{{ route('user.accreditations.showTagging', $accreditation) }}" 
                                               class="btn btn-sm btn-info" title="View Tagging">
                                                <i class="fas fa-tags"></i> Tags
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $accreditations->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No accreditations found</h5>
                    <p class="text-muted">Try adjusting your filters or check back later.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable if needed
    if ($('#dataTable').length && $('#dataTable tbody tr').length > 0) {
        $('#dataTable').DataTable({
            "pageLength": 25,
            "order": [[ 0, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [6] } // Actions column
            ]
        });
    }
});
</script>
@endpush