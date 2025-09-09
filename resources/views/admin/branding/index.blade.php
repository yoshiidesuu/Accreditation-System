@extends('admin.layout')

@section('title', 'Branding Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-image me-2"></i>Branding Management
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Logo Management -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-image me-2"></i>Logo Management
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Current Active Logo -->
                                    @if($activeLogo)
                                        <div class="mb-4">
                                            <h6>Current Active Logo</h6>
                                            <div class="border rounded p-3 text-center bg-light">
                                                <img src="{{ $activeLogo->full_url }}" alt="Current Logo" class="img-fluid" style="max-height: 100px;">
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        Version {{ $activeLogo->version }} | {{ $activeLogo->dimensions }} | {{ $activeLogo->formatted_size }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mb-4">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>No active logo set
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Upload New Logo -->
                                    <div class="mb-4">
                                        <h6>Upload New Logo</h6>
                                        <form id="logoUploadForm" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="type" value="logo">
                                            <div class="mb-3">
                                                <input type="file" class="form-control" name="file" accept="image/*" required>
                                                <div class="form-text">Supported formats: PNG, JPG, JPEG, SVG. Max size: 2MB</div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload me-2"></i>Upload Logo
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Logo Versions -->
                                    <div>
                                        <h6>Logo Versions</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Preview</th>
                                                        <th>Version</th>
                                                        <th>Size</th>
                                                        <th>Uploaded</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="logoVersions">
                                                    @forelse($logos as $logo)
                                                        <tr data-asset-id="{{ $logo->id }}">
                                                            <td>
                                                                <img src="{{ $logo->full_url }}" alt="Logo v{{ $logo->version }}" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                                            </td>
                                                            <td>
                                                                v{{ $logo->version }}
                                                                @if($logo->is_active)
                                                                    <span class="badge bg-success ms-1">Active</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $logo->formatted_size }}</td>
                                                            <td>
                                                                <small>{{ $logo->created_at->format('M j, Y') }}</small><br>
                                                                <small class="text-muted">by {{ $logo->uploader->name }}</small>
                                                            </td>
                                                            <td>
                                                                @if(!$logo->is_active)
                                                                    <button class="btn btn-sm btn-success activate-btn" data-asset-id="{{ $logo->id }}" data-type="logo">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                @endif
                                                                @if(!$logo->is_active)
                                                                    <button class="btn btn-sm btn-danger delete-btn" data-asset-id="{{ $logo->id }}" data-type="logo">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">No logo versions uploaded</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        {{ $logos->appends(request()->query())->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Favicon Management -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-star me-2"></i>Favicon Management
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Current Active Favicon -->
                                    @if($activeFavicon)
                                        <div class="mb-4">
                                            <h6>Current Active Favicon</h6>
                                            <div class="border rounded p-3 text-center bg-light">
                                                <img src="{{ $activeFavicon->full_url }}" alt="Current Favicon" class="img-fluid" style="max-height: 32px;">
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        Version {{ $activeFavicon->version }} | {{ $activeFavicon->dimensions }} | {{ $activeFavicon->formatted_size }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mb-4">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>No active favicon set
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Upload New Favicon -->
                                    <div class="mb-4">
                                        <h6>Upload New Favicon</h6>
                                        <form id="faviconUploadForm" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="type" value="favicon">
                                            <div class="mb-3">
                                                <input type="file" class="form-control" name="file" accept="image/*,.ico" required>
                                                <div class="form-text">Supported formats: ICO, PNG, JPG, JPEG. Max size: 2MB. Recommended: 32x32px</div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload me-2"></i>Upload Favicon
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Favicon Versions -->
                                    <div>
                                        <h6>Favicon Versions</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Preview</th>
                                                        <th>Version</th>
                                                        <th>Size</th>
                                                        <th>Uploaded</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="faviconVersions">
                                                    @forelse($favicons as $favicon)
                                                        <tr data-asset-id="{{ $favicon->id }}">
                                                            <td>
                                                                <img src="{{ $favicon->full_url }}" alt="Favicon v{{ $favicon->version }}" class="img-thumbnail" style="max-width: 32px; max-height: 32px;">
                                                            </td>
                                                            <td>
                                                                v{{ $favicon->version }}
                                                                @if($favicon->is_active)
                                                                    <span class="badge bg-success ms-1">Active</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $favicon->formatted_size }}</td>
                                                            <td>
                                                                <small>{{ $favicon->created_at->format('M j, Y') }}</small><br>
                                                                <small class="text-muted">by {{ $favicon->uploader->name }}</small>
                                                            </td>
                                                            <td>
                                                                @if(!$favicon->is_active)
                                                                    <button class="btn btn-sm btn-success activate-btn" data-asset-id="{{ $favicon->id }}" data-type="favicon">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                @endif
                                                                @if(!$favicon->is_active)
                                                                    <button class="btn btn-sm btn-danger delete-btn" data-asset-id="{{ $favicon->id }}" data-type="favicon">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">No favicon versions uploaded</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        {{ $favicons->appends(request()->query())->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Logo upload
    $('#logoUploadForm').on('submit', function(e) {
        e.preventDefault();
        uploadAsset(this, 'logo');
    });
    
    // Favicon upload
    $('#faviconUploadForm').on('submit', function(e) {
        e.preventDefault();
        uploadAsset(this, 'favicon');
    });
    
    // Activate asset
    $(document).on('click', '.activate-btn', function() {
        const assetId = $(this).data('asset-id');
        const type = $(this).data('type');
        activateAsset(assetId, type);
    });
    
    // Delete asset
    $(document).on('click', '.delete-btn', function() {
        const assetId = $(this).data('asset-id');
        const type = $(this).data('type');
        
        if (confirm('Are you sure you want to delete this ' + type + '?')) {
            deleteAsset(assetId, type);
        }
    });
});

function uploadAsset(form, type) {
    const formData = new FormData(form);
    const submitBtn = $(form).find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Uploading...');
    
    $.ajax({
        url: '{{ route("admin.branding.upload") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                location.reload(); // Reload to show new asset
            } else {
                showAlert('error', response.message || 'Upload failed');
            }
        },
        error: function(xhr) {
            let message = 'Upload failed';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                message = errors.join(', ');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert('error', message);
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalText);
            $(form)[0].reset();
        }
    });
}

function activateAsset(assetId, type) {
    $.ajax({
        url: `/admin/branding/${assetId}/activate`,
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                location.reload(); // Reload to update active status
            } else {
                showAlert('error', response.message || 'Activation failed');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Activation failed';
            showAlert('error', message);
        }
    });
}

function deleteAsset(assetId, type) {
    $.ajax({
        url: `/admin/branding/${assetId}`,
        method: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $(`tr[data-asset-id="${assetId}"]`).fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                showAlert('error', response.message || 'Deletion failed');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Deletion failed';
            showAlert('error', message);
        }
    });
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alert = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('.card-body').first().prepend(alert);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alert.alert('close');
    }, 5000);
}
</script>
@endpush