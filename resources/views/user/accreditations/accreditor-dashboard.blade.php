@extends('layouts.user')

@section('title', 'Accreditor Dashboard')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Accreditor Dashboard</h1>
            <p class="text-muted mb-0">View and manage your assigned accreditations</p>
        </div>
        <div class="d-flex gap-2">
            @hasrole('accreditor_lead')
                <span class="badge bg-primary fs-6">Lead Accreditor</span>
            @endhasrole
            @hasrole('accreditor_member')
                <span class="badge bg-info fs-6">Member Accreditor</span>
            @endhasrole
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Accreditations</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('user.accreditations.accreditor-dashboard') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Search accreditations...">
                </div>
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
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="planning" {{ request('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="accredited" {{ request('status') == 'accredited' ? 'selected' : '' }}>Accredited</option>
                        <option value="denied" {{ request('status') == 'denied' ? 'selected' : '' }}>Denied</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('user.accreditations.accreditor-dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Accreditations List -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">My Assigned Accreditations</h6>
            <span class="badge bg-secondary">{{ $accreditations->total() }} Total</span>
        </div>
        <div class="card-body">
            @if($accreditations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="accreditationsTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>College</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>My Role</th>
                                <th>Start Date</th>
                                <th>Visit Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accreditations as $accreditation)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $accreditation->title }}</div>
                                        <small class="text-muted">{{ $accreditation->accrediting_body }}</small>
                                    </td>
                                    <td>{{ $accreditation->college->name ?? 'N/A' }}</td>
                                    <td>{{ $accreditation->academicYear->name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'planning' => 'secondary',
                                                'in_progress' => 'warning',
                                                'under_review' => 'info',
                                                'completed' => 'success',
                                                'accredited' => 'success',
                                                'denied' => 'danger',
                                                'suspended' => 'warning'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$accreditation->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $accreditation->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($accreditation->assigned_lead_id == auth()->id())
                                            <span class="badge bg-primary">Lead</span>
                                        @elseif(in_array(auth()->id(), $accreditation->assigned_members ?? []))
                                            <span class="badge bg-info">Member</span>
                                        @endif
                                    </td>
                                    <td>{{ $accreditation->start_date ? $accreditation->start_date->format('M d, Y') : 'Not set' }}</td>
                                    <td>{{ $accreditation->visit_date ? $accreditation->visit_date->format('M d, Y') : 'Not set' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('user.accreditations.show', $accreditation) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('user.accreditations.show-tagging-accreditor', $accreditation) }}" 
                                               class="btn btn-sm btn-outline-success" title="Manage Tags">
                                                <i class="fas fa-tags"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $accreditations->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Assigned Accreditations</h5>
                    <p class="text-muted">You haven't been assigned to any accreditations yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable for better sorting and searching
    if ($('#accreditationsTable tbody tr').length > 0) {
        $('#accreditationsTable').DataTable({
            "paging": false,
            "info": false,
            "searching": false,
            "ordering": true,
            "order": [[5, "desc"]], // Sort by start date descending
            "columnDefs": [
                { "orderable": false, "targets": [7] } // Disable sorting for actions column
            ],
            "language": {
                "emptyTable": "No assigned accreditations found"
            }
        });
    }

    // Auto-submit form on filter change
    $('#college_id, #status').on('change', function() {
        $(this).closest('form').submit();
    });

    // Search on Enter key
    $('#search').on('keypress', function(e) {
        if (e.which === 13) {
            $(this).closest('form').submit();
        }
    });
});
</script>
@endpush