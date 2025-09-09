@extends('layouts.user')

@section('title', 'Reports')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Reports</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(auth()->user()->hasRole(['admin', 'staff']))
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-plus"></i> Generate Report
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#generateReportModal" data-type="comprehensive">
                            <i class="fas fa-file-alt"></i> Comprehensive Report
                        </a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#generateReportModal" data-type="college">
                            <i class="fas fa-university"></i> College Report
                        </a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#generateReportModal" data-type="accreditation">
                            <i class="fas fa-certificate"></i> Accreditation Report
                        </a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#generateReportModal" data-type="swot">
                            <i class="fas fa-chart-line"></i> SWOT Analysis Report
                        </a></li>
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Reports</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('user.reports.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Search reports...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="type" class="form-label">Report Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="comprehensive" {{ request('type') == 'comprehensive' ? 'selected' : '' }}>Comprehensive</option>
                                <option value="college" {{ request('type') == 'college' ? 'selected' : '' }}>College</option>
                                <option value="accreditation" {{ request('type') == 'accreditation' ? 'selected' : '' }}>Accreditation</option>
                                <option value="swot" {{ request('type') == 'swot' ? 'selected' : '' }}>SWOT Analysis</option>
                                <option value="parameter" {{ request('type') == 'parameter' ? 'selected' : '' }}>Parameter Content</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="generating" {{ request('status') == 'generating' ? 'selected' : '' }}>Generating</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                    </div>
                    @if(auth()->user()->hasRole(['admin', 'staff']))
                        <div class="col-md-2">
                            <div class="mb-3">
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
                        </div>
                    @endif
                    <div class="col-md-2">
                        <div class="mb-3">
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
                    </div>
                    <div class="col-md-1">
                        <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Reports List</h6>
            <div class="d-flex align-items-center">
                <span class="me-3">{{ $reports->total() }} total reports</span>
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="view" id="listView" autocomplete="off" checked>
                    <label class="btn btn-outline-secondary" for="listView"><i class="fas fa-list"></i></label>
                    
                    <input type="radio" class="btn-check" name="view" id="gridView" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="gridView"><i class="fas fa-th"></i></label>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($reports->count() > 0)
                <!-- List View -->
                <div id="listViewContent">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Report</th>
                                    <th>Type</th>
                                    @if(auth()->user()->hasRole(['admin', 'staff']))
                                        <th>College</th>
                                    @endif
                                    <th>Academic Year</th>
                                    <th>Status</th>
                                    <th>Generated</th>
                                    <th>Size</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $report)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="report-icon me-3">
                                                    @if($report->type === 'comprehensive')
                                                        <i class="fas fa-file-alt text-primary fa-2x"></i>
                                                    @elseif($report->type === 'college')
                                                        <i class="fas fa-university text-info fa-2x"></i>
                                                    @elseif($report->type === 'accreditation')
                                                        <i class="fas fa-certificate text-warning fa-2x"></i>
                                                    @elseif($report->type === 'swot')
                                                        <i class="fas fa-chart-line text-success fa-2x"></i>
                                                    @else
                                                        <i class="fas fa-file text-secondary fa-2x"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $report->title }}</h6>
                                                    @if($report->description)
                                                        <small class="text-muted">{{ Str::limit($report->description, 60) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($report->type === 'comprehensive') badge-primary
                                                @elseif($report->type === 'college') badge-info
                                                @elseif($report->type === 'accreditation') badge-warning
                                                @elseif($report->type === 'swot') badge-success
                                                @else badge-secondary
                                                @endif">
                                                {{ ucfirst($report->type) }}
                                            </span>
                                        </td>
                                        @if(auth()->user()->hasRole(['admin', 'staff']))
                                            <td>{{ $report->college->name ?? 'All Colleges' }}</td>
                                        @endif
                                        <td>{{ $report->academicYear->label ?? 'All Years' }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($report->status === 'generating') badge-warning
                                                @elseif($report->status === 'completed') badge-success
                                                @elseif($report->status === 'failed') badge-danger
                                                @else badge-secondary
                                                @endif">
                                                @if($report->status === 'generating')
                                                    <i class="fas fa-spinner fa-spin"></i> Generating
                                                @else
                                                    {{ ucfirst($report->status) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $report->created_at->format('M d, Y') }}<br>
                                                {{ $report->created_at->format('g:i A') }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($report->file_size)
                                                <small class="text-muted">{{ number_format($report->file_size / 1024, 2) }} KB</small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if($report->status === 'completed' && $report->file_path)
                                                    <a href="{{ route('user.reports.download', $report) }}" 
                                                       class="btn btn-outline-primary" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <a href="{{ route('user.reports.view', $report) }}" 
                                                       class="btn btn-outline-info" title="View" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                                @if($report->status === 'generating')
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            onclick="refreshReportStatus({{ $report->id }})" title="Refresh Status">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                @endif
                                                @can('delete', $report)
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                                            data-report-id="{{ $report->id }}" 
                                                            data-report-title="{{ $report->title }}" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grid View -->
                <div id="gridViewContent" style="display: none;">
                    <div class="row p-3">
                        @foreach($reports as $report)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 report-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="report-icon me-3">
                                                @if($report->type === 'comprehensive')
                                                    <i class="fas fa-file-alt text-primary fa-2x"></i>
                                                @elseif($report->type === 'college')
                                                    <i class="fas fa-university text-info fa-2x"></i>
                                                @elseif($report->type === 'accreditation')
                                                    <i class="fas fa-certificate text-warning fa-2x"></i>
                                                @elseif($report->type === 'swot')
                                                    <i class="fas fa-chart-line text-success fa-2x"></i>
                                                @else
                                                    <i class="fas fa-file text-secondary fa-2x"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <span class="badge 
                                                    @if($report->status === 'generating') badge-warning
                                                    @elseif($report->status === 'completed') badge-success
                                                    @elseif($report->status === 'failed') badge-danger
                                                    @else badge-secondary
                                                    @endif float-end">
                                                    @if($report->status === 'generating')
                                                        <i class="fas fa-spinner fa-spin"></i>
                                                    @endif
                                                    {{ ucfirst($report->status) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <h6 class="card-title">{{ $report->title }}</h6>
                                        @if($report->description)
                                            <p class="card-text text-muted small">{{ Str::limit($report->description, 80) }}</p>
                                        @endif
                                        
                                        <div class="mb-2">
                                            <span class="badge 
                                                @if($report->type === 'comprehensive') badge-primary
                                                @elseif($report->type === 'college') badge-info
                                                @elseif($report->type === 'accreditation') badge-warning
                                                @elseif($report->type === 'swot') badge-success
                                                @else badge-secondary
                                                @endif me-1">
                                                {{ ucfirst($report->type) }}
                                            </span>
                                        </div>
                                        
                                        <div class="small text-muted mb-3">
                                            @if(auth()->user()->hasRole(['admin', 'staff']))
                                                <div><strong>College:</strong> {{ $report->college->name ?? 'All Colleges' }}</div>
                                            @endif
                                            <div><strong>Academic Year:</strong> {{ $report->academicYear->label ?? 'All Years' }}</div>
                                            <div><strong>Generated:</strong> {{ $report->created_at->format('M d, Y g:i A') }}</div>
                                            @if($report->file_size)
                                                <div><strong>Size:</strong> {{ number_format($report->file_size / 1024, 2) }} KB</div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex justify-content-between">
                                            @if($report->status === 'completed' && $report->file_path)
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('user.reports.download', $report) }}" 
                                                       class="btn btn-outline-primary">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                    <a href="{{ route('user.reports.view', $report) }}" 
                                                       class="btn btn-outline-info" target="_blank">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </div>
                                            @elseif($report->status === 'generating')
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="refreshReportStatus({{ $report->id }})">
                                                    <i class="fas fa-sync-alt"></i> Refresh
                                                </button>
                                            @endif
                                            
                                            @can('delete', $report)
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                                        data-report-id="{{ $report->id }}" 
                                                        data-report-title="{{ $report->title }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Reports Found</h5>
                    <p class="text-muted">No reports match your current filters.</p>
                    @if(auth()->user()->hasRole(['admin', 'staff']))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                            <i class="fas fa-plus"></i> Generate Your First Report
                        </button>
                    @endif
                </div>
            @endif
        </div>
        
        @if($reports->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            Showing {{ $reports->firstItem() }} to {{ $reports->lastItem() }} of {{ $reports->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Generate Report Modal -->
@if(auth()->user()->hasRole(['admin', 'staff']))
<div class="modal fade" id="generateReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('user.reports.generate') }}" method="POST" id="generateReportForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="report_type" name="type">
                    
                    <div class="mb-3">
                        <label for="report_title" class="form-label">Report Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="report_title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="report_description" class="form-label">Description</label>
                        <textarea class="form-control" id="report_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="report_college_id" class="form-label">College</label>
                                <select class="form-select" id="report_college_id" name="college_id">
                                    <option value="">All Colleges</option>
                                    @foreach($colleges as $college)
                                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="report_academic_year_id" class="form-label">Academic Year</label>
                                <select class="form-select" id="report_academic_year_id" name="academic_year_id">
                                    <option value="">All Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ $year->active ? 'selected' : '' }}>{{ $year->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="report_format" class="form-label">Export Format</label>
                        <select class="form-select" id="report_format" name="format">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_charts" name="include_charts" value="1" checked>
                            <label class="form-check-label" for="include_charts">
                                Include Charts and Graphs
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_attachments" name="include_attachments" value="1">
                            <label class="form-check-label" for="include_attachments">
                                Include Attachments
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cog"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this report?</p>
                <p><strong id="deleteReportTitle"></strong></p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Report</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.report-card {
    transition: all 0.2s ease;
    border: 1px solid #e3e6f0;
}

.report-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.report-icon {
    min-width: 50px;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle view toggle
    $('input[name="view"]').on('change', function() {
        if ($(this).attr('id') === 'listView') {
            $('#listViewContent').show();
            $('#gridViewContent').hide();
        } else {
            $('#listViewContent').hide();
            $('#gridViewContent').show();
        }
    });
    
    // Handle generate report modal
    $('#generateReportModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const type = button.data('type');
        
        $('#report_type').val(type);
        
        // Set default title based on type
        let defaultTitle = '';
        switch(type) {
            case 'comprehensive':
                defaultTitle = 'Comprehensive Report - ' + new Date().toLocaleDateString();
                break;
            case 'college':
                defaultTitle = 'College Report - ' + new Date().toLocaleDateString();
                break;
            case 'accreditation':
                defaultTitle = 'Accreditation Report - ' + new Date().toLocaleDateString();
                break;
            case 'swot':
                defaultTitle = 'SWOT Analysis Report - ' + new Date().toLocaleDateString();
                break;
        }
        
        $('#report_title').val(defaultTitle);
    });
    
    // Handle delete modal
    $('#deleteModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const reportId = button.data('report-id');
        const reportTitle = button.data('report-title');
        
        $('#deleteReportTitle').text(reportTitle);
        $('#deleteForm').attr('action', `/user/reports/${reportId}`);
    });
    
    // Handle form submission
    $('#generateReportForm').on('submit', function() {
        $(this).find('button[type="submit"]')
            .html('<i class="fas fa-spinner fa-spin"></i> Generating...')
            .prop('disabled', true);
    });
    
    // Auto-refresh generating reports
    const generatingReports = $('.badge:contains("Generating")');
    if (generatingReports.length > 0) {
        setTimeout(() => {
            location.reload();
        }, 30000); // Refresh every 30 seconds
    }
    
    // Search debounce
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            $('#filterForm').submit();
        }, 500);
    });
    
    // Filter change handlers
    $('#type, #status, #college_id, #academic_year_id').on('change', function() {
        $('#filterForm').submit();
    });
});

// Refresh report status function
function refreshReportStatus(reportId) {
    $.ajax({
        url: `/user/reports/${reportId}/status`,
        method: 'GET',
        success: function(response) {
            if (response.status !== 'generating') {
                location.reload();
            }
        },
        error: function() {
            console.log('Failed to refresh report status');
        }
    });
}
</script>
@endpush