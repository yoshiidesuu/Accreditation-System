@extends('layouts.admin')

@section('title', 'Reports Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Reports Dashboard</h1>
            <p class="mb-0 text-muted">Comprehensive system reports and analytics</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download me-2"></i>Export Reports
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportReport('system', 'pdf')">System Report (PDF)</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportReport('comprehensive', 'excel')">Comprehensive Report (Excel)</a></li>
                <li><a class="dropdown-item" href="#" onclick="exportReport('analytics', 'csv')">Analytics Report (CSV)</a></li>
            </ul>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Colleges</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_colleges']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-university fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Contents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_contents']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Accreditations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_accreditations']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-certificate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Report Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('admin.reports.analytics') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-chart-line me-2"></i>System Analytics
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button type="button" class="btn btn-outline-success btn-block" data-bs-toggle="modal" data-bs-target="#collegeReportModal">
                                <i class="fas fa-university me-2"></i>College Reports
                            </button>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button type="button" class="btn btn-outline-info btn-block" data-bs-toggle="modal" data-bs-target="#customReportModal">
                                <i class="fas fa-cog me-2"></i>Custom Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Role Distribution -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Role Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="roleDistributionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Activity</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-file-upload text-primary me-2"></i>Total Parameters</td>
                                    <td class="text-end">{{ number_format($stats['total_parameters']) }}</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-clock text-warning me-2"></i>Pending Contents</td>
                                    <td class="text-end">{{ number_format($stats['pending_contents']) }}</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-comments text-info me-2"></i>SWOT Entries</td>
                                    <td class="text-end">{{ number_format($stats['total_swot_entries']) }}</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-award text-success me-2"></i>Total Accreditations</td>
                                    <td class="text-end">{{ number_format($stats['total_accreditations']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent System Activities</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>College</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity['description'] ?? 'System Activity' }}</td>
                                    <td>{{ $activity['user'] ?? 'System' }}</td>
                                    <td>{{ $activity['college'] ?? 'N/A' }}</td>
                                    <td>{{ $activity['date'] ?? now()->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $activity['status_color'] ?? 'secondary' }}">
                                            {{ $activity['status'] ?? 'Completed' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>No recent activities found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- College Report Modal -->
<div class="modal fade" id="collegeReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate College Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="collegeReportForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="college_select" class="form-label">Select College</label>
                        <select class="form-select" id="college_select" name="college_id" required>
                            <option value="">Choose a college...</option>
                            <!-- Colleges will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="academic_year_select" class="form-label">Academic Year</label>
                        <select class="form-select" id="academic_year_select" name="academic_year_id">
                            <option value="">All Academic Years</option>
                            <!-- Academic years will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="report_format" class="form-label">Format</label>
                        <select class="form-select" id="report_format" name="format" required>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Report Modal -->
<div class="modal fade" id="customReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Custom Report Builder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="customReportForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="report_type" class="form-label">Report Type</label>
                                <select class="form-select" id="report_type" name="type" required>
                                    <option value="system">System Overview</option>
                                    <option value="user">User Activity</option>
                                    <option value="accreditation">Accreditation Status</option>
                                    <option value="comprehensive">Comprehensive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="custom_format" class="form-label">Format</label>
                                <select class="form-select" id="custom_format" name="format" required>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
.btn-block {
    width: 100%;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Role Distribution Chart
const roleData = @json($userRoleDistribution);
const ctx = document.getElementById('roleDistributionChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(roleData),
        datasets: [{
            data: Object.values(roleData),
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e',
                '#e74a3b',
                '#858796',
                '#5a5c69'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Export functions
function exportReport(type, format) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.reports.export") }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = type;
    
    const formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = format;
    
    form.appendChild(csrfToken);
    form.appendChild(typeInput);
    form.appendChild(formatInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// College Report Form
document.getElementById('collegeReportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('type', 'college');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.reports.export") }}';
    
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    bootstrap.Modal.getInstance(document.getElementById('collegeReportModal')).hide();
});

// Custom Report Form
document.getElementById('customReportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.reports.export") }}';
    
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    bootstrap.Modal.getInstance(document.getElementById('customReportModal')).hide();
});
</script>
@endpush