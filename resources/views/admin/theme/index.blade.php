@extends('admin.layout')

@section('title', 'Theme Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Theme Management</h4>
                    <div>
                        <button type="button" class="btn btn-outline-secondary me-2" id="previewBtn">
                            <i class="fas fa-eye"></i> Preview Changes
                        </button>
                        <button type="button" class="btn btn-outline-warning me-2" onclick="resetTheme()">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                        <button type="submit" form="themeForm" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form id="themeForm" action="{{ route('admin.theme.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Navigation Tabs -->
                        <ul class="nav nav-tabs mb-4" id="themeTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="colors-tab" data-bs-toggle="tab" data-bs-target="#colors" type="button" role="tab">
                                    <i class="fas fa-palette"></i> Colors
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button" role="tab">
                                    <i class="fas fa-image"></i> Branding
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="layout-tab" data-bs-toggle="tab" data-bs-target="#layout" type="button" role="tab">
                                    <i class="fas fa-th-large"></i> Layout
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="typography-tab" data-bs-toggle="tab" data-bs-target="#typography" type="button" role="tab">
                                    <i class="fas fa-font"></i> Typography
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                    <i class="fas fa-cog"></i> General
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="themeTabContent">
                            <!-- Colors Tab -->
                            <div class="tab-pane fade show active" id="colors" role="tabpanel">
                                <div class="row">
                                    @foreach($colorSettings as $setting)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <label for="{{ $setting->key }}" class="form-label">{{ $setting->description }}</label>
                                            <div class="input-group">
                                                <input type="color" 
                                                       class="form-control form-control-color" 
                                                       id="{{ $setting->key }}" 
                                                       name="settings[{{ $loop->index }}][value]" 
                                                       value="{{ $setting->value }}">
                                                <input type="hidden" name="settings[{{ $loop->index }}][key]" value="{{ $setting->key }}">
                                                <span class="input-group-text">{{ $setting->value }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Branding Tab -->
                            <div class="tab-pane fade" id="branding" role="tabpanel">
                                <div class="row">
                                    <!-- Logo Upload -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0">Logo Upload</h5>
                                            </div>
                                            <div class="card-body">
                                                @if($brandingSettings->where('key', 'logo_url')->first())
                                                    <div class="mb-3">
                                                        <img src="{{ $brandingSettings->where('key', 'logo_url')->first()->value }}" 
                                                             alt="Current Logo" 
                                                             class="img-thumbnail" 
                                                             style="max-height: 100px;">
                                                    </div>
                                                @endif
                                                <form action="{{ route('admin.theme.upload-logo') }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <input type="file" class="form-control" name="logo" accept="image/*" required>
                                                        <div class="form-text">Supported formats: PNG, JPG, JPEG, SVG. Max size: 2MB</div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-upload"></i> Upload Logo
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Favicon Upload -->
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0">Favicon Upload</h5>
                                            </div>
                                            <div class="card-body">
                                                @if($brandingSettings->where('key', 'favicon_url')->first())
                                                    <div class="mb-3">
                                                        <img src="{{ $brandingSettings->where('key', 'favicon_url')->first()->value }}" 
                                                             alt="Current Favicon" 
                                                             class="img-thumbnail" 
                                                             style="max-height: 32px;">
                                                    </div>
                                                @endif
                                                <form action="{{ route('admin.theme.upload-favicon') }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <input type="file" class="form-control" name="favicon" accept=".ico,.png" required>
                                                        <div class="form-text">Supported formats: ICO, PNG. Max size: 1MB</div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-upload"></i> Upload Favicon
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Logo Alt Text -->
                                    @foreach($brandingSettings->where('key', 'logo_alt_text') as $setting)
                                        <div class="col-12 mb-3">
                                            <label for="{{ $setting->key }}" class="form-label">{{ $setting->description }}</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="{{ $setting->key }}" 
                                                   name="settings[{{ $colorSettings->count() + $loop->index }}][value]" 
                                                   value="{{ $setting->value }}">
                                            <input type="hidden" name="settings[{{ $colorSettings->count() + $loop->index }}][key]" value="{{ $setting->key }}">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Layout Tab -->
                            <div class="tab-pane fade" id="layout" role="tabpanel">
                                <div class="row">
                                    @foreach($layoutSettings as $setting)
                                        <div class="col-md-6 mb-3">
                                            <label for="{{ $setting->key }}" class="form-label">{{ $setting->description }}</label>
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="{{ $setting->key }}" 
                                                       name="settings[{{ $colorSettings->count() + $brandingSettings->where('key', 'logo_alt_text')->count() + $loop->index }}][value]" 
                                                       value="{{ $setting->value }}">
                                                <input type="hidden" name="settings[{{ $colorSettings->count() + $brandingSettings->where('key', 'logo_alt_text')->count() + $loop->index }}][key]" value="{{ $setting->key }}">
                                                @if(str_contains($setting->key, 'width') || str_contains($setting->key, 'height'))
                                                    <span class="input-group-text">px</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Typography Tab -->
                            <div class="tab-pane fade" id="typography" role="tabpanel">
                                <div class="row">
                                    @foreach($typographySettings as $setting)
                                        <div class="col-md-6 mb-3">
                                            <label for="{{ $setting->key }}" class="form-label">{{ $setting->description }}</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="{{ $setting->key }}" 
                                                   name="settings[{{ $colorSettings->count() + $brandingSettings->where('key', 'logo_alt_text')->count() + $layoutSettings->count() + $loop->index }}][value]" 
                                                   value="{{ $setting->value }}">
                                            <input type="hidden" name="settings[{{ $colorSettings->count() + $brandingSettings->where('key', 'logo_alt_text')->count() + $layoutSettings->count() + $loop->index }}][key]" value="{{ $setting->key }}">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- General Tab -->
                            <div class="tab-pane fade" id="general" role="tabpanel">
                                <div class="row">
                                    @foreach($generalSettings as $setting)
                                        <div class="col-md-6 mb-3">
                                            <label for="{{ $setting->key }}" class="form-label">{{ $setting->description }}</label>
                                            @if($setting->type === 'boolean')
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="{{ $setting->key }}" 
                                                           name="settings[{{ $colorSettings->count() + $brandingSettings->where('key', 'logo_alt_text')->count() + $layoutSettings->count() + $typographySettings->count() + $loop->index }}][value]" 
                                                           value="1" 
                                                           {{ $setting->value == '1' ? 'checked' : '' }}>
                                                    <input type="hidden" name="settings[{{ $colorSettings->count() + $brandingSettings->where('key', 'logo_alt_text')->count() + $layoutSettings->count() + $typographySettings->count() + $loop->index }}][key]" value="{{ $setting->key }}">
                                                </div>
                                            @elseif($setting->key === 'default_theme_mode')
                                                <select class="form-select" 
                                                        id="{{ $setting->key }}" 
                                                        name="settings[{{ $colorSettings->count() + $brandingSettings->where('key', 'logo_alt_text')->count() + $layoutSettings->count() + $typographySettings->count() + $loop->index }}][value]">
                                                    <option value="light" {{ $setting->value === 'light' ? 'selected' : '' }}>Light</option>
                                                    <option value="dark" {{ $setting->value === 'dark' ? 'selected' : '' }}>Dark</option>
                                                </select>
                                                <input type="hidden" name="settings[{{ $colorSettings->count() + $brandingSettings->where('key', 'logo_alt_text')->count() + $layoutSettings->count() + $typographySettings->count() + $loop->index }}][key]" value="{{ $setting->key }}">
                                            @else
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="{{ $setting->key }}" 
                                                       name="settings[{{ $colorSettings->count() + $brandingSettings->where('key', 'logo_alt_text')->count() + $layoutSettings->count() + $typographySettings->count() + $loop->index }}][value]" 
                                                       value="{{ $setting->value }}">
                                                <input type="hidden" name="settings[{{ $colorSettings->count() + $brandingSettings->where('key', 'logo_alt_text')->count() + $layoutSettings->count() + $typographySettings->count() + $loop->index }}][key]" value="{{ $setting->key }}">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Theme Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="applyPreview()">Apply Changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Color picker change handler
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('change', function() {
        const textSpan = this.parentElement.querySelector('.input-group-text');
        if (textSpan) {
            textSpan.textContent = this.value;
        }
    });
});

// Preview functionality
document.getElementById('previewBtn').addEventListener('click', function() {
    const formData = new FormData(document.getElementById('themeForm'));
    const settings = {};
    
    // Convert form data to settings object
    for (let [key, value] of formData.entries()) {
        if (key.includes('[key]')) {
            const index = key.match(/\[(\d+)\]/)[1];
            const settingKey = value;
            const settingValue = formData.get(`settings[${index}][value]`);
            settings[settingKey] = settingValue;
        }
    }
    
    // Send preview request
    fetch('{{ route("admin.theme.preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ settings: settings })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Apply preview CSS
            let previewStyle = document.getElementById('preview-style');
            if (!previewStyle) {
                previewStyle = document.createElement('style');
                previewStyle.id = 'preview-style';
                document.head.appendChild(previewStyle);
            }
            previewStyle.textContent = data.css;
            
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-info alert-dismissible fade show';
            alert.innerHTML = `
                Preview applied! You can see the changes in the current page.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.querySelector('.card-body').firstChild);
        }
    })
    .catch(error => {
        console.error('Preview error:', error);
    });
});

// Reset theme function
function resetTheme() {
    if (confirm('Are you sure you want to reset all theme settings to defaults? This action cannot be undone.')) {
        window.location.href = '{{ route("admin.theme.reset") }}';
    }
}

// Handle checkbox values for boolean settings
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        this.value = this.checked ? '1' : '0';
    });
});
</script>
@endpush