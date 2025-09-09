@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cogs mr-2"></i>System Settings
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- System Information -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                System Information
                            </div>
                            <div class="small text-gray-600">
                                <div class="mb-1"><strong>PHP:</strong> {{ $systemInfo['php_version'] }}</div>
                                <div class="mb-1"><strong>Laravel:</strong> {{ $systemInfo['laravel_version'] }}</div>
                                <div class="mb-1"><strong>Database:</strong> {{ $systemInfo['database_version'] }}</div>
                                <div class="mb-1"><strong>Storage Used:</strong> {{ $systemInfo['storage_used'] }}</div>
                                <div><strong>Cache Size:</strong> {{ $systemInfo['cache_size'] }}</div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-info-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-3">
                        Quick Actions
                    </div>
                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('admin.settings.maintenance') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $maintenanceSettings['maintenance_mode'] ? 'btn-success' : 'btn-warning' }} btn-block mb-2">
                                <i class="fas fa-{{ $maintenanceSettings['maintenance_mode'] ? 'play' : 'pause' }} mr-1"></i>
                                {{ $maintenanceSettings['maintenance_mode'] ? 'Disable' : 'Enable' }} Maintenance
                            </button>
                        </form>
                        
                        <div class="btn-group btn-block mb-2" role="group">
                            <form method="POST" action="{{ route('admin.settings.cache.clear') }}" class="flex-fill">
                                @csrf
                                <input type="hidden" name="type" value="all">
                                <button type="submit" class="btn btn-sm btn-info btn-block">
                                    <i class="fas fa-broom mr-1"></i>Clear All Cache
                                </button>
                            </form>
                        </div>
                        
                        <form method="POST" action="{{ route('admin.settings.optimize') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary btn-block">
                                <i class="fas fa-rocket mr-1"></i>Optimize Application
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Overview -->
        <div class="col-xl-4 col-lg-12 mb-4">
            <div class="card border-left-{{ $maintenanceSettings['maintenance_mode'] ? 'danger' : 'success' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-{{ $maintenanceSettings['maintenance_mode'] ? 'danger' : 'success' }} text-uppercase mb-1">
                        System Status
                    </div>
                    <div class="small text-gray-600">
                        <div class="mb-1">
                            <span class="badge badge-{{ $maintenanceSettings['maintenance_mode'] ? 'danger' : 'success' }}">
                                {{ $maintenanceSettings['maintenance_mode'] ? 'Maintenance Mode' : 'Online' }}
                            </span>
                        </div>
                        <div class="mb-1">
                            <strong>Debug Mode:</strong> 
                            <span class="badge badge-{{ $maintenanceSettings['debug_mode'] ? 'warning' : 'success' }}">
                                {{ $maintenanceSettings['debug_mode'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="mb-1"><strong>Log Level:</strong> {{ ucfirst($maintenanceSettings['log_level']) }}</div>
                        <div><strong>Server:</strong> {{ $systemInfo['server_software'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Tabs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configuration Settings</h6>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                        <i class="fas fa-cog mr-1"></i>General
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="mail-tab" data-toggle="tab" href="#mail" role="tab">
                        <i class="fas fa-envelope mr-1"></i>Mail
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab">
                        <i class="fas fa-shield-alt mr-1"></i>Security
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="cache-tab" data-toggle="tab" href="#cache" role="tab">
                        <i class="fas fa-database mr-1"></i>Cache & Storage
                    </a>
                </li>
            </ul>

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                
                <div class="tab-content" id="settingsTabContent">
                    <!-- General Settings -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="app_name">Application Name</label>
                                    <input type="text" class="form-control" id="app_name" name="app_name" 
                                           value="{{ $settings['app_name'] }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="app_url">Application URL</label>
                                    <input type="url" class="form-control" id="app_url" name="app_url" 
                                           value="{{ $settings['app_url'] }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="app_timezone">Timezone</label>
                                    <select class="form-control" id="app_timezone" name="app_timezone" required>
                                        <option value="UTC" {{ $settings['app_timezone'] == 'UTC' ? 'selected' : '' }}>UTC</option>
                                        <option value="Asia/Manila" {{ $settings['app_timezone'] == 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila</option>
                                        <option value="America/New_York" {{ $settings['app_timezone'] == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                        <option value="Europe/London" {{ $settings['app_timezone'] == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mail Settings -->
                    <div class="tab-pane fade" id="mail" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mail_driver">Mail Driver</label>
                                    <select class="form-control" id="mail_driver" name="mail_driver" required>
                                        <option value="smtp" {{ $settings['mail_driver'] == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                        <option value="sendmail" {{ $settings['mail_driver'] == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                        <option value="log" {{ $settings['mail_driver'] == 'log' ? 'selected' : '' }}>Log</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mail_host">Mail Host</label>
                                    <input type="text" class="form-control" id="mail_host" name="mail_host" 
                                           value="{{ $settings['mail_host'] }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mail_port">Mail Port</label>
                                    <input type="number" class="form-control" id="mail_port" name="mail_port" 
                                           value="{{ $settings['mail_port'] }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mail_username">Mail Username</label>
                                    <input type="text" class="form-control" id="mail_username" name="mail_username" 
                                           value="{{ $settings['mail_username'] }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mail_password">Mail Password</label>
                                    <input type="password" class="form-control" id="mail_password" name="mail_password" 
                                           placeholder="Leave blank to keep current password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mail_from_address">From Address</label>
                                    <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" 
                                           value="{{ $settings['mail_from_address'] }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="mail_from_name">From Name</label>
                                    <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" 
                                           value="{{ $settings['mail_from_name'] }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="session_lifetime">Session Lifetime (minutes)</label>
                                    <input type="number" class="form-control" id="session_lifetime" name="session_lifetime" 
                                           value="{{ $securitySettings['session_lifetime'] }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_timeout">Password Timeout (minutes)</label>
                                    <input type="number" class="form-control" id="password_timeout" name="password_timeout" 
                                           value="{{ $securitySettings['password_timeout'] }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_login_attempts">Max Login Attempts</label>
                                    <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" 
                                           value="{{ $securitySettings['max_login_attempts'] }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lockout_duration">Lockout Duration (minutes)</label>
                                    <input type="number" class="form-control" id="lockout_duration" name="lockout_duration" 
                                           value="{{ $securitySettings['lockout_duration'] }}" min="1" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cache & Storage Settings -->
                    <div class="tab-pane fade" id="cache" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Current Configuration:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Cache Driver:</strong> {{ ucfirst($settings['cache_driver']) }}</li>
                                        <li><strong>Session Driver:</strong> {{ ucfirst($settings['session_driver']) }}</li>
                                        <li><strong>Queue Driver:</strong> {{ ucfirst($settings['queue_driver']) }}</li>
                                        <li><strong>Filesystem Driver:</strong> {{ ucfirst($settings['filesystem_driver']) }}</li>
                                        <li><strong>Database Connection:</strong> {{ ucfirst($settings['database_connection']) }}</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3">
                                        <form method="POST" action="{{ route('admin.settings.cache.clear') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="config">
                                            <button type="submit" class="btn btn-outline-info btn-block">
                                                <i class="fas fa-cog mr-1"></i>Clear Config
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-3">
                                        <form method="POST" action="{{ route('admin.settings.cache.clear') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="route">
                                            <button type="submit" class="btn btn-outline-warning btn-block">
                                                <i class="fas fa-route mr-1"></i>Clear Routes
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-3">
                                        <form method="POST" action="{{ route('admin.settings.cache.clear') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="view">
                                            <button type="submit" class="btn btn-outline-success btn-block">
                                                <i class="fas fa-eye mr-1"></i>Clear Views
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-3">
                                        <form method="POST" action="{{ route('admin.settings.cache.clear') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="cache">
                                            <button type="submit" class="btn btn-outline-danger btn-block">
                                                <i class="fas fa-database mr-1"></i>Clear Cache
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Save Settings
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle tab switching
    $('#settingsTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Confirmation for maintenance mode
    $('form[action*="maintenance"]').on('submit', function(e) {
        const isEnabled = $(this).find('button').hasClass('btn-warning');
        const message = isEnabled ? 
            'Are you sure you want to enable maintenance mode? This will make the site unavailable to users.' :
            'Are you sure you want to disable maintenance mode?';
        
        if (!confirm(message)) {
            e.preventDefault();
        }
    });
    
    // Confirmation for cache clearing
    $('form[action*="cache/clear"]').on('submit', function(e) {
        if (!confirm('Are you sure you want to clear the cache? This may temporarily slow down the application.')) {
            e.preventDefault();
        }
    });
    
    // Confirmation for optimization
    $('form[action*="optimize"]').on('submit', function(e) {
        if (!confirm('Are you sure you want to optimize the application? This may take a few moments.')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.nav-tabs .nav-link {
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #495057;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.tab-content {
    border: 1px solid #dee2e6;
    border-top: none;
    padding: 1rem;
    background-color: #fff;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    border-radius: 0.25rem;
}

.alert ul {
    padding-left: 1.5rem;
}
</style>
@endpush