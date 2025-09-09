@extends('layouts.app')

@section('title', 'Reports & Exports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reports & Exports Dashboard</h3>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4" id="stats-cards">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 id="total-rankings">-</h4>
                                            <p class="mb-0">Total Rankings</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-chart-line fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 id="avg-completion">-</h4>
                                            <p class="mb-0">Avg Completion</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-percentage fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 id="total-swot">-</h4>
                                            <p class="mb-0">Total SWOT Entries</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-list-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 id="pending-swot">-</h4>
                                            <p class="mb-0">Pending SWOT</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="college-filter" class="form-label">Filter by College</label>
                            <select class="form-select" id="college-filter">
                                <option value="">All Colleges</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="area-filter" class="form-label">Filter by Area</label>
                            <select class="form-select" id="area-filter">
                                <option value="">All Areas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Report Generation Tabs -->
                    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="rankings-tab" data-bs-toggle="tab" data-bs-target="#rankings" type="button" role="tab">
                                <i class="fas fa-trophy"></i> Rankings Reports
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="swot-tab" data-bs-toggle="tab" data-bs-target="#swot" type="button" role="tab">
                                <i class="fas fa-list-alt"></i> SWOT Reports
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="reportTabsContent">
                        <!-- Rankings Reports Tab -->
                        <div class="tab-pane fade show active" id="rankings" role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">Generate Rankings Report</h5>
                                </div>
                                <div class="card-body">
                                    <form id="rankings-form">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="rankings-period" class="form-label">Period</label>
                                                <select class="form-select" id="rankings-period" name="period" required>
                                                    <option value="current">Current Month</option>
                                                    <option value="previous">Previous Month</option>
                                                    <option value="all">All Time</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="rankings-college" class="form-label">College (Optional)</label>
                                                <select class="form-select" id="rankings-college" name="college_id">
                                                    <option value="">All Colleges</option>
                                                    @foreach($colleges as $college)
                                                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="rankings-area" class="form-label">Area (Optional)</label>
                                                <select class="form-select" id="rankings-area" name="area_id">
                                                    <option value="">All Areas</option>
                                                    @foreach($areas as $area)
                                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <label for="rankings-format" class="form-label">Report Format</label>
                                                <select class="form-select" id="rankings-format" name="format" required>
                                                    <option value="summary">Summary</option>
                                                    <option value="detailed">Detailed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button type="button" class="btn btn-danger me-2" onclick="generateReport('rankings', 'pdf')">
                                                    <i class="fas fa-file-pdf"></i> Generate PDF
                                                </button>
                                                <button type="button" class="btn btn-success" onclick="generateReport('rankings', 'csv')">
                                                    <i class="fas fa-file-csv"></i> Generate CSV
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- SWOT Reports Tab -->
                        <div class="tab-pane fade" id="swot" role="tabpanel">
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">Generate SWOT Report</h5>
                                </div>
                                <div class="card-body">
                                    <form id="swot-form">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="swot-college" class="form-label">College (Optional)</label>
                                                <select class="form-select" id="swot-college" name="college_id">
                                                    <option value="">All Colleges</option>
                                                    @foreach($colleges as $college)
                                                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="swot-area" class="form-label">Area (Optional)</label>
                                                <select class="form-select" id="swot-area" name="area_id">
                                                    <option value="">All Areas</option>
                                                    @foreach($areas as $area)
                                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="swot-type" class="form-label">SWOT Type (Optional)</label>
                                                <select class="form-select" id="swot-type" name="type">
                                                    <option value="">All Types</option>
                                                    <option value="S">Strengths</option>
                                                    <option value="W">Weaknesses</option>
                                                    <option value="O">Opportunities</option>
                                                    <option value="T">Threats</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <label for="swot-status" class="form-label">Status (Optional)</label>
                                                <select class="form-select" id="swot-status" name="status">
                                                    <option value="">All Statuses</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="approved">Approved</option>
                                                    <option value="rejected">Rejected</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="swot-date-from" class="form-label">Date From (Optional)</label>
                                                <input type="date" class="form-control" id="swot-date-from" name="date_from">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="swot-date-to" class="form-label">Date To (Optional)</label>
                                                <input type="date" class="form-control" id="swot-date-to" name="date_to">
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <label for="swot-format" class="form-label">Report Format</label>
                                                <select class="form-select" id="swot-format" name="format" required>
                                                    <option value="summary">Summary</option>
                                                    <option value="detailed">Detailed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button type="button" class="btn btn-danger me-2" onclick="generateReport('swot', 'pdf')">
                                                    <i class="fas fa-file-pdf"></i> Generate PDF
                                                </button>
                                                <button type="button" class="btn btn-success" onclick="generateReport('swot', 'csv')">
                                                    <i class="fas fa-file-csv"></i> Generate CSV
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Generating report...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load initial stats
    loadStats();
    
    // Update stats when filters change
    $('#college-filter, #area-filter').on('change', function() {
        loadStats();
    });
});

function loadStats() {
    const collegeId = $('#college-filter').val();
    const areaId = $('#area-filter').val();
    
    $.ajax({
        url: '{{ route("admin.exports.stats") }}',
        method: 'GET',
        data: {
            college_id: collegeId,
            area_id: areaId
        },
        success: function(data) {
            $('#total-rankings').text(data.rankings.total_rankings || 0);
            $('#avg-completion').text(data.rankings.avg_completion ? 
                Math.round(data.rankings.avg_completion) + '%' : '0%');
            $('#total-swot').text(data.swot.total_entries || 0);
            $('#pending-swot').text(data.swot.pending || 0);
        },
        error: function() {
            console.error('Failed to load statistics');
        }
    });
}

function generateReport(type, format) {
    const formId = type + '-form';
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    // Show loading modal
    $('#loadingModal').modal('show');
    
    // Build URL
    const baseUrl = '{{ route("admin.exports.index") }}'.replace('/exports', '/exports/');
    const url = baseUrl + type + '/' + format;
    
    // Convert FormData to URLSearchParams for GET request
    const params = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    // Create download link
    const downloadUrl = url + '?' + params.toString();
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Hide loading modal after a short delay
    setTimeout(function() {
        $('#loadingModal').modal('hide');
    }, 1000);
}
</script>
@endpush