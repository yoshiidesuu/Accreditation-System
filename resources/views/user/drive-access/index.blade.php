@extends('layouts.user')

@section('title', 'My Google Drive Files')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">My Google Drive Files</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fab fa-google-drive"></i> Add Drive File
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('user.drive-access.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="college_id" class="form-label">College</label>
                            <select name="college_id" id="college_id" class="form-select">
                                <option value="">All Colleges</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                        {{ $college->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Search by file ID, link, or parameter..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Drive Files Table -->
                <div class="card-body">
                    @if($driveFiles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>College</th>
                                        <th>File Info</th>
                                        <th>Access Status</th>
                                        <th>Created At</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($driveFiles as $file)
                                        <tr>
                                            <td>
                                                <strong>{{ $file->parameter->title ?? 'N/A' }}</strong>
                                                @if($file->parameter->code)
                                                    <br><small class="text-muted">{{ $file->parameter->code }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $file->college->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($file->drive_file_id)
                                                    <small class="text-muted d-block">ID: {{ Str::limit($file->drive_file_id, 20) }}</small>
                                                @endif
                                                @if($file->share_link)
                                                    <a href="{{ $file->share_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fab fa-google-drive"></i> View Original
                                                    </a>
                                                @endif
                                                @if($file->file_metadata && isset($file->file_metadata['name']))
                                                    <br><small class="text-info">{{ $file->file_metadata['name'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match($file->permission_status) {
                                                        'granted' => 'success',
                                                        'denied' => 'danger',
                                                        'requested' => 'warning',
                                                        'expired' => 'secondary',
                                                        default => 'info'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">
                                                    {{ ucfirst($file->permission_status ?? 'unknown') }}
                                                </span>
                                                @if($file->requires_permission)
                                                    <br><small class="text-info">Permission Required</small>
                                                @endif
                                                @if($file->permission_requested_at)
                                                    <br><small class="text-muted">Requested: {{ $file->permission_requested_at->format('M d, Y') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $file->created_at->format('M d, Y H:i') }}
                                                <br><small class="text-muted">{{ $file->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('user.drive-access.show', $file) }}" 
                                                       class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($file->permission_status === 'granted')
                                                        <button type="button" class="btn btn-sm btn-success" 
                                                                onclick="getAccessUrl({{ $file->id }})" title="Get Access URL">
                                                            <i class="fas fa-link"></i>
                                                        </button>
                                                    @elseif($file->requires_permission && $file->permission_status !== 'requested')
                                                        <button type="button" class="btn btn-sm btn-warning" 
                                                                onclick="requestAccess({{ $file->id }})" title="Request Access">
                                                            <i class="fas fa-key"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            onclick="editFile({{ $file->id }})" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteFile({{ $file->id }})" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Showing {{ $driveFiles->firstItem() }} to {{ $driveFiles->lastItem() }} 
                                of {{ $driveFiles->total() }} results
                            </div>
                            {{ $driveFiles->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fab fa-google-drive fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Google Drive files found</h5>
                            <p class="text-muted">You haven't added any Google Drive files yet.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fab fa-google-drive"></i> Add Your First Drive File
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Google Drive File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parameter_id" class="form-label">Parameter *</label>
                                <select name="parameter_id" id="parameter_id" class="form-select" required>
                                    <option value="">Select Parameter</option>
                                    <!-- Parameters will be loaded via AJAX -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="college_id" class="form-label">College *</label>
                                <select name="college_id" id="college_id_upload" class="form-select" required>
                                    <option value="">Select College</option>
                                    @foreach($colleges as $college)
                                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="academic_year_id" class="form-label">Academic Year *</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select" required>
                            <option value="">Select Academic Year</option>
                            <!-- Academic years will be loaded via AJAX -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="share_link" class="form-label">Google Drive Share Link *</label>
                        <input type="url" name="share_link" id="share_link" class="form-control" 
                               placeholder="https://drive.google.com/file/d/..." required>
                        <div class="form-text">
                            Paste the shareable link from Google Drive. Make sure the file is set to "Anyone with the link can view".
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content Description</label>
                        <textarea name="content" id="content" class="form-control" rows="3" 
                                  placeholder="Describe the content of this file..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2" 
                                  placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fab fa-google-drive"></i> Add File
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Google Drive File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_share_link" class="form-label">Google Drive Share Link</label>
                        <input type="url" name="share_link" id="edit_share_link" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_content" class="form-label">Content Description</label>
                        <textarea name="content" id="edit_content" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notes</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Access Modal -->
<div class="modal fade" id="requestAccessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request File Access</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="requestAccessForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="access_message" class="form-label">Message (optional)</label>
                        <textarea name="message" id="access_message" class="form-control" rows="3" 
                                  placeholder="Explain why you need access to this file..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Request Access</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let currentFileId = null;

// Initialize page
$(document).ready(function() {
    initializeForms();
    loadSelectOptions();
});

// Form initialization
function initializeForms() {
    $('#uploadForm').submit(function(e) {
        e.preventDefault();
        uploadFile();
    });

    $('#editForm').submit(function(e) {
        e.preventDefault();
        updateFile();
    });

    $('#requestAccessForm').submit(function(e) {
        e.preventDefault();
        submitAccessRequest();
    });
}

// Load select options
function loadSelectOptions() {
    // Load parameters (you might want to implement this based on your API)
    // loadParameters();
    
    // Load academic years (you might want to implement this based on your API)
    // loadAcademicYears();
}

// File operations
function uploadFile() {
    const formData = new FormData($('#uploadForm')[0]);
    
    $.ajax({
        url: '{{ route("user.drive-access.upload") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#uploadModal').modal('hide');
                location.reload();
            } else {
                showAlert('error', response.error || 'Upload failed');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            showAlert('error', response?.error || 'Upload failed');
        }
    });
}

function editFile(fileId) {
    currentFileId = fileId;
    
    // Load current file data
    $.get(`/user/drive-access/${fileId}`)
        .done(function(response) {
            // Populate edit form (you'll need to implement this based on your response structure)
            $('#editModal').modal('show');
        })
        .fail(function() {
            showAlert('error', 'Failed to load file data');
        });
}

function updateFile() {
    const formData = $('#editForm').serialize();
    
    $.ajax({
        url: `/user/drive-access/${currentFileId}`,
        type: 'PUT',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#editModal').modal('hide');
                location.reload();
            } else {
                showAlert('error', response.error || 'Update failed');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            showAlert('error', response?.error || 'Update failed');
        }
    });
}

function deleteFile(fileId) {
    if (confirm('Are you sure you want to delete this Google Drive file reference?')) {
        $.ajax({
            url: `/user/drive-access/${fileId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', response.error || 'Delete failed');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showAlert('error', response?.error || 'Delete failed');
            }
        });
    }
}

// Access management
function requestAccess(fileId) {
    currentFileId = fileId;
    $('#requestAccessModal').modal('show');
}

function submitAccessRequest() {
    const message = $('#access_message').val();
    
    $.post(`/user/drive-access/${currentFileId}/request-access`, {
        message: message,
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(response) {
        if (response.success) {
            showAlert('success', response.message);
            $('#requestAccessModal').modal('hide');
            location.reload();
        } else {
            showAlert('error', response.error || 'Request failed');
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        showAlert('error', response?.error || 'Request failed');
    });
}

function getAccessUrl(fileId) {
    $.get(`/user/drive-access/${fileId}/access-url`)
        .done(function(response) {
            if (response.success && response.access_url) {
                window.open(response.access_url, '_blank');
            } else {
                showAlert('error', 'Unable to generate access URL');
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showAlert('error', response?.error || 'Failed to get access URL');
        });
}

// Utility functions
function showAlert(type, message) {
    const alertClass = type === 'error' ? 'danger' : type;
    const alert = `
        <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at the top of the container
    $('.container-fluid').prepend(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endpush