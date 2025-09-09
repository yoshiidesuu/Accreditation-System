@extends('layouts.user')

@section('title', 'View Report - ' . $report->title)

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $report->title }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($report->title, 30) }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <div class="btn-group" role="group">
                <a href="{{ route('user.reports.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
                @if($report->status === 'completed' && $report->file_path)
                    <a href="{{ route('user.reports.download', $report) }}" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download
                    </a>
                @endif
                @can('delete', $report)
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                @endcan
            </div>
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

    <div class="row">
        <!-- Report Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Report Information</h6>
                </div>
                <div class="card-body">
                    <div class="report-info">
                        <div class="info-item mb-3">
                            <div class="d-flex align-items-center mb-2">
                                @if($report->type === 'comprehensive')
                                    <i class="fas fa-file-alt text-primary fa-2x me-3"></i>
                                @elseif($report->type === 'college')
                                    <i class="fas fa-university text-info fa-2x me-3"></i>
                                @elseif($report->type === 'accreditation')
                                    <i class="fas fa-certificate text-warning fa-2x me-3"></i>
                                @elseif($report->type === 'swot')
                                    <i class="fas fa-chart-line text-success fa-2x me-3"></i>
                                @else
                                    <i class="fas fa-file text-secondary fa-2x me-3"></i>
                                @endif
                                <div>
                                    <h5 class="mb-0">{{ $report->title }}</h5>
                                    <span class="badge 
                                        @if($report->type === 'comprehensive') badge-primary
                                        @elseif($report->type === 'college') badge-info
                                        @elseif($report->type === 'accreditation') badge-warning
                                        @elseif($report->type === 'swot') badge-success
                                        @else badge-secondary
                                        @endif">
                                        {{ ucfirst($report->type) }} Report
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($report->description)
                            <div class="info-item mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <p class="text-muted">{{ $report->description }}</p>
                            </div>
                        @endif

                        <div class="info-item mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <div>
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
                                @if($report->status === 'generating')
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                                            onclick="refreshReportStatus()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if(auth()->user()->hasRole(['admin', 'staff']))
                            <div class="info-item mb-3">
                                <label class="form-label fw-bold">College</label>
                                <p class="mb-0">{{ $report->college->name ?? 'All Colleges' }}</p>
                            </div>
                        @endif

                        <div class="info-item mb-3">
                            <label class="form-label fw-bold">Academic Year</label>
                            <p class="mb-0">{{ $report->academicYear->label ?? 'All Years' }}</p>
                        </div>

                        <div class="info-item mb-3">
                            <label class="form-label fw-bold">Generated By</label>
                            <p class="mb-0">{{ $report->user->name }}</p>
                        </div>

                        <div class="info-item mb-3">
                            <label class="form-label fw-bold">Generated On</label>
                            <p class="mb-0">{{ $report->created_at->format('F d, Y g:i A') }}</p>
                        </div>

                        @if($report->file_size)
                            <div class="info-item mb-3">
                                <label class="form-label fw-bold">File Size</label>
                                <p class="mb-0">{{ number_format($report->file_size / 1024, 2) }} KB</p>
                            </div>
                        @endif

                        @if($report->format)
                            <div class="info-item mb-3">
                                <label class="form-label fw-bold">Format</label>
                                <p class="mb-0">
                                    <span class="badge badge-secondary">{{ strtoupper($report->format) }}</span>
                                </p>
                            </div>
                        @endif

                        @if($report->parameters && is_array($report->parameters))
                            <div class="info-item mb-3">
                                <label class="form-label fw-bold">Report Parameters</label>
                                <div class="small">
                                    @if(isset($report->parameters['include_charts']) && $report->parameters['include_charts'])
                                        <span class="badge badge-info me-1">Charts Included</span>
                                    @endif
                                    @if(isset($report->parameters['include_attachments']) && $report->parameters['include_attachments'])
                                        <span class="badge badge-info me-1">Attachments Included</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Report Actions -->
            @if($report->status === 'completed')
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($report->file_path)
                                <a href="{{ route('user.reports.download', $report) }}" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Download Report
                                </a>
                                <button type="button" class="btn btn-outline-primary" onclick="printReport()">
                                    <i class="fas fa-print"></i> Print Report
                                </button>
                            @endif
                            <button type="button" class="btn btn-outline-info" onclick="shareReport()">
                                <i class="fas fa-share"></i> Share Report
                            </button>
                            @if(auth()->user()->hasRole(['admin', 'staff']))
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#regenerateModal">
                                    <i class="fas fa-redo"></i> Regenerate Report
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Report Content -->
        <div class="col-lg-8">
            @if($report->status === 'completed')
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Report Preview</h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary" onclick="zoomOut()">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetZoom()">
                                <i class="fas fa-search"></i> 100%
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="zoomIn()">
                                <i class="fas fa-search-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($report->format === 'pdf')
                            <div id="pdfViewer" class="report-viewer">
                                <iframe src="{{ route('user.reports.download', $report) }}#toolbar=0" 
                                        width="100%" height="600px" 
                                        style="border: none;">
                                    <p>Your browser does not support PDFs. 
                                       <a href="{{ route('user.reports.download', $report) }}">Download the PDF</a>.
                                    </p>
                                </iframe>
                            </div>
                        @else
                            <div class="p-4 text-center">
                                <i class="fas fa-file-{{ $report->format === 'excel' ? 'excel' : 'csv' }} fa-4x text-muted mb-3"></i>
                                <h5>{{ strtoupper($report->format) }} Report</h5>
                                <p class="text-muted">This report format cannot be previewed in the browser.</p>
                                <a href="{{ route('user.reports.download', $report) }}" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Download to View
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($report->status === 'generating')
                <div class="card shadow mb-4">
                    <div class="card-body text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5>Generating Report...</h5>
                        <p class="text-muted">Please wait while your report is being generated. This may take a few minutes.</p>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 100%"></div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshReportStatus()">
                            <i class="fas fa-sync-alt"></i> Check Status
                        </button>
                    </div>
                </div>
            @elseif($report->status === 'failed')
                <div class="card shadow mb-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                        <h5 class="text-danger">Report Generation Failed</h5>
                        <p class="text-muted">There was an error generating this report. Please try again.</p>
                        @if($report->error_message)
                            <div class="alert alert-danger text-start">
                                <strong>Error Details:</strong><br>
                                {{ $report->error_message }}
                            </div>
                        @endif
                        @if(auth()->user()->hasRole(['admin', 'staff']))
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#regenerateModal">
                                <i class="fas fa-redo"></i> Try Again
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@can('delete', $report)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this report?</p>
                <p><strong>{{ $report->title }}</strong></p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('user.reports.destroy', $report) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Report</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- Regenerate Report Modal -->
@if(auth()->user()->hasRole(['admin', 'staff']))
<div class="modal fade" id="regenerateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('user.reports.regenerate', $report) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Regenerate Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to regenerate this report?</p>
                    <p><strong>{{ $report->title }}</strong></p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This will create a new version of the report with the latest data. The current report file will be replaced.
                    </div>
                    
                    <div class="mb-3">
                        <label for="regenerate_format" class="form-label">Export Format</label>
                        <select class="form-select" id="regenerate_format" name="format">
                            <option value="pdf" {{ $report->format === 'pdf' ? 'selected' : '' }}>PDF</option>
                            <option value="excel" {{ $report->format === 'excel' ? 'selected' : '' }}>Excel</option>
                            <option value="csv" {{ $report->format === 'csv' ? 'selected' : '' }}>CSV</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-redo"></i> Regenerate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Share Report Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Share this report with others:</p>
                
                <div class="mb-3">
                    <label for="shareUrl" class="form-label">Report URL</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="shareUrl" 
                               value="{{ request()->url() }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="mailto:?subject={{ urlencode('Report: ' . $report->title) }}&body={{ urlencode('Please find the report at: ' . request()->url()) }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-envelope"></i> Share via Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.report-viewer {
    background: #f8f9fa;
    min-height: 600px;
}

.info-item {
    border-bottom: 1px solid #e3e6f0;
    padding-bottom: 0.75rem;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.report-info .form-label {
    font-size: 0.875rem;
    color: #5a5c69;
    margin-bottom: 0.25rem;
}

@media (max-width: 768px) {
    .col-lg-4, .col-lg-8 {
        margin-bottom: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh if report is generating
    @if($report->status === 'generating')
        setTimeout(() => {
            location.reload();
        }, 30000); // Refresh every 30 seconds
    @endif
});

// Refresh report status
function refreshReportStatus() {
    $.ajax({
        url: `{{ route('user.reports.status', $report) }}`,
        method: 'GET',
        success: function(response) {
            if (response.status !== 'generating') {
                location.reload();
            } else {
                // Show updated progress if available
                console.log('Report still generating...');
            }
        },
        error: function() {
            console.log('Failed to refresh report status');
        }
    });
}

// PDF Viewer zoom functions
let currentZoom = 1;

function zoomIn() {
    currentZoom += 0.1;
    updateZoom();
}

function zoomOut() {
    if (currentZoom > 0.5) {
        currentZoom -= 0.1;
        updateZoom();
    }
}

function resetZoom() {
    currentZoom = 1;
    updateZoom();
}

function updateZoom() {
    const iframe = document.querySelector('#pdfViewer iframe');
    if (iframe) {
        iframe.style.transform = `scale(${currentZoom})`;
        iframe.style.transformOrigin = 'top left';
    }
}

// Print report
function printReport() {
    @if($report->format === 'pdf')
        const iframe = document.querySelector('#pdfViewer iframe');
        if (iframe) {
            iframe.contentWindow.print();
        } else {
            window.open('{{ route('user.reports.download', $report) }}', '_blank');
        }
    @else
        window.open('{{ route('user.reports.download', $report) }}', '_blank');
    @endif
}

// Share report
function shareReport() {
    $('#shareModal').modal('show');
}

// Copy to clipboard
function copyToClipboard() {
    const shareUrl = document.getElementById('shareUrl');
    shareUrl.select();
    shareUrl.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        
        // Show success feedback
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    } catch (err) {
        console.log('Failed to copy URL');
    }
}
</script>
@endpush