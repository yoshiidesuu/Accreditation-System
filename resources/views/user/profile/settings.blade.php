@extends('user.layout')

@section('title', 'Account Settings')

@section('page-header')
@endsection

@section('page-title')
<div class="d-flex align-items-center">
    <i class="fas fa-cog me-2 text-primary"></i>
    Account Settings
</div>
@endsection

@section('page-description', 'Manage your account preferences and notification settings')

@section('page-actions')
<a href="{{ route('user.profile.index') }}" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-1"></i>Back to Profile
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Notification Settings -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bell me-2"></i>Notification Preferences
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.profile.notifications') }}" id="notificationForm">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Email Notifications</h6>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="email_updates" 
                                   name="notifications[email_updates]" value="1" 
                                   {{ old('notifications.email_updates', $user->notification_preferences['email_updates'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_updates">
                                <strong>System Updates</strong>
                                <br><small class="text-muted">Receive notifications about system updates and maintenance</small>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="email_reminders" 
                                   name="notifications[email_reminders]" value="1" 
                                   {{ old('notifications.email_reminders', $user->notification_preferences['email_reminders'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_reminders">
                                <strong>Task Reminders</strong>
                                <br><small class="text-muted">Get reminded about pending tasks and deadlines</small>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="email_reports" 
                                   name="notifications[email_reports]" value="1" 
                                   {{ old('notifications.email_reports', $user->notification_preferences['email_reports'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_reports">
                                <strong>Weekly Reports</strong>
                                <br><small class="text-muted">Receive weekly summary reports of your activities</small>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="email_security" 
                                   name="notifications[email_security]" value="1" 
                                   {{ old('notifications.email_security', $user->notification_preferences['email_security'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_security">
                                <strong>Security Alerts</strong>
                                <br><small class="text-muted">Important security notifications and login alerts</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Browser Notifications</h6>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="browser_notifications" 
                                   name="notifications[browser_notifications]" value="1" 
                                   {{ old('notifications.browser_notifications', $user->notification_preferences['browser_notifications'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="browser_notifications">
                                <strong>Enable Browser Notifications</strong>
                                <br><small class="text-muted">Show desktop notifications for important updates</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Notification Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Privacy Settings -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Privacy Settings
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.profile.privacy') }}" id="privacyForm">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Profile Visibility</h6>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="profile_public" 
                                   name="privacy[profile_public]" value="1" 
                                   {{ old('privacy.profile_public', $user->privacy_settings['profile_public'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="profile_public">
                                <strong>Public Profile</strong>
                                <br><small class="text-muted">Allow other users to view your profile information</small>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="show_email" 
                                   name="privacy[show_email]" value="1" 
                                   {{ old('privacy.show_email', $user->privacy_settings['show_email'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_email">
                                <strong>Show Email Address</strong>
                                <br><small class="text-muted">Display your email address in your public profile</small>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="show_phone" 
                                   name="privacy[show_phone]" value="1" 
                                   {{ old('privacy.show_phone', $user->privacy_settings['show_phone'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_phone">
                                <strong>Show Phone Number</strong>
                                <br><small class="text-muted">Display your phone number in your public profile</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Activity Tracking</h6>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="track_activity" 
                                   name="privacy[track_activity]" value="1" 
                                   {{ old('privacy.track_activity', $user->privacy_settings['track_activity'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="track_activity">
                                <strong>Activity Tracking</strong>
                                <br><small class="text-muted">Allow the system to track your activity for analytics</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Privacy Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Theme Preferences -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-palette me-2"></i>Theme Preferences
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.theme.update') }}" id="themeForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="theme_mode" class="form-label">Theme Mode</label>
                                <select class="form-select" id="theme_mode" name="theme_mode">
                                    <option value="light" {{ ($user->getThemeMode() ?? 'light') === 'light' ? 'selected' : '' }}>Light Mode</option>
                                    <option value="dark" {{ ($user->getThemeMode() ?? 'light') === 'dark' ? 'selected' : '' }}>Dark Mode</option>
                                    <option value="auto" {{ ($user->getThemeMode() ?? 'light') === 'auto' ? 'selected' : '' }}>Auto (System)</option>
                                </select>
                                <small class="form-text text-muted">Choose your preferred theme appearance</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="font_size" class="form-label">Font Size</label>
                                <select class="form-select" id="font_size" name="theme_preferences[font_size]">
                                    @php $preferences = $user->getThemePreferences() ?? []; @endphp
                                    <option value="small" {{ ($preferences['font_size'] ?? 'medium') === 'small' ? 'selected' : '' }}>Small</option>
                                    <option value="medium" {{ ($preferences['font_size'] ?? 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="large" {{ ($preferences['font_size'] ?? 'medium') === 'large' ? 'selected' : '' }}>Large</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sidebar_style" class="form-label">Sidebar Style</label>
                                <select class="form-select" id="sidebar_style" name="theme_preferences[sidebar_style]">
                                    <option value="default" {{ ($preferences['sidebar_style'] ?? 'default') === 'default' ? 'selected' : '' }}>Default</option>
                                    <option value="compact" {{ ($preferences['sidebar_style'] ?? 'default') === 'compact' ? 'selected' : '' }}>Compact</option>
                                    <option value="minimal" {{ ($preferences['sidebar_style'] ?? 'default') === 'minimal' ? 'selected' : '' }}>Minimal</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="primary_color" class="form-label">Primary Color</label>
                                <input type="color" class="form-control form-control-color" id="primary_color" 
                                       name="theme_preferences[primary_color]" 
                                       value="{{ $preferences['primary_color'] ?? '#800020' }}" 
                                       title="Choose your primary color">
                                <small class="form-text text-muted">Customize the primary color scheme</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" id="previewTheme">
                            <i class="fas fa-eye me-1"></i>Preview Changes
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Theme Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Display Preferences -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-desktop me-2"></i>Display Preferences
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.profile.display') }}" id="displayForm">
                    @csrf
                    @method('PATCH')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select @error('timezone') is-invalid @enderror" 
                                        id="timezone" name="timezone">
                                    <option value="">Select timezone</option>
                                    <option value="America/New_York" {{ old('timezone', $user->timezone ?? 'America/New_York') == 'America/New_York' ? 'selected' : '' }}>Eastern Time (ET)</option>
                                    <option value="America/Chicago" {{ old('timezone', $user->timezone) == 'America/Chicago' ? 'selected' : '' }}>Central Time (CT)</option>
                                    <option value="America/Denver" {{ old('timezone', $user->timezone) == 'America/Denver' ? 'selected' : '' }}>Mountain Time (MT)</option>
                                    <option value="America/Los_Angeles" {{ old('timezone', $user->timezone) == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (PT)</option>
                                    <option value="UTC" {{ old('timezone', $user->timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="Asia/Manila" {{ old('timezone', $user->timezone) == 'Asia/Manila' ? 'selected' : '' }}>Philippines Time (PHT)</option>
                                </select>
                                @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_format" class="form-label">Date Format</label>
                                <select class="form-select @error('date_format') is-invalid @enderror" 
                                        id="date_format" name="date_format">
                                    <option value="M d, Y" {{ old('date_format', $user->date_format ?? 'M d, Y') == 'M d, Y' ? 'selected' : '' }}>Jan 15, 2024</option>
                                    <option value="d/m/Y" {{ old('date_format', $user->date_format) == 'd/m/Y' ? 'selected' : '' }}>15/01/2024</option>
                                    <option value="m/d/Y" {{ old('date_format', $user->date_format) == 'm/d/Y' ? 'selected' : '' }}>01/15/2024</option>
                                    <option value="Y-m-d" {{ old('date_format', $user->date_format) == 'Y-m-d' ? 'selected' : '' }}>2024-01-15</option>
                                </select>
                                @error('date_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="items_per_page" class="form-label">Items Per Page</label>
                                <select class="form-select @error('items_per_page') is-invalid @enderror" 
                                        id="items_per_page" name="items_per_page">
                                    <option value="10" {{ old('items_per_page', $user->items_per_page ?? 15) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="15" {{ old('items_per_page', $user->items_per_page ?? 15) == 15 ? 'selected' : '' }}>15</option>
                                    <option value="25" {{ old('items_per_page', $user->items_per_page) == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ old('items_per_page', $user->items_per_page) == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                @error('items_per_page')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="language" class="form-label">Language</label>
                                <select class="form-select @error('language') is-invalid @enderror" 
                                        id="language" name="language">
                                    <option value="en" {{ old('language', $user->language ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                    <option value="es" {{ old('language', $user->language) == 'es' ? 'selected' : '' }}>Español</option>
                                    <option value="fr" {{ old('language', $user->language) == 'fr' ? 'selected' : '' }}>Français</option>
                                    <option value="tl" {{ old('language', $user->language) == 'tl' ? 'selected' : '' }}>Filipino</option>
                                </select>
                                @error('language')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Display Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Account Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Account Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Export Data</h6>
                        <p class="small text-muted mb-3">Download a copy of your account data and activity.</p>
                        <button class="btn btn-outline-info btn-sm" onclick="exportData()">
                            <i class="fas fa-download me-1"></i>Export My Data
                        </button>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Account Deactivation</h6>
                        <p class="small text-muted mb-3">Temporarily deactivate your account. You can reactivate it later.</p>
                        <button class="btn btn-outline-warning btn-sm" onclick="deactivateAccount()">
                            <i class="fas fa-pause me-1"></i>Deactivate Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportData() {
    if (confirm('Export your account data? This may take a few minutes to prepare.')) {
        // Create a form to submit the export request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("user.profile.export") }}';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
}

function deactivateAccount() {
    if (confirm('Are you sure you want to deactivate your account? You can reactivate it by logging in again.')) {
        if (confirm('This will log you out and temporarily disable your account. Continue?')) {
            // Create a form to submit the deactivation request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("user.profile.deactivate") }}';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Submit form
            document.body.appendChild(form);
            form.submit();
        }
    }
}

// Request browser notification permission
document.getElementById('browser_notifications').addEventListener('change', function() {
    if (this.checked && 'Notification' in window) {
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(function(permission) {
                if (permission !== 'granted') {
                    document.getElementById('browser_notifications').checked = false;
                    alert('Browser notifications permission denied. Please enable it in your browser settings.');
                }
            });
        } else if (Notification.permission === 'denied') {
            this.checked = false;
            alert('Browser notifications are blocked. Please enable them in your browser settings.');
        }
    }
});
</script>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Theme form handling
    $('#themeForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Apply theme changes immediately
                    applyThemeChanges(response.theme_mode, response.theme_preferences);
                    
                    // Show success message
                    showAlert('success', response.message);
                } else {
                    showAlert('error', 'Failed to update theme preferences.');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while updating theme preferences.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('error', message);
            }
        });
    });
    
    // Preview theme changes
    $('#previewTheme').on('click', function() {
        const themeMode = $('#theme_mode').val();
        const primaryColor = $('#primary_color').val();
        const fontSize = $('#font_size').val();
        const sidebarStyle = $('#sidebar_style').val();
        
        // Apply preview changes
        previewThemeChanges(themeMode, {
            primary_color: primaryColor,
            font_size: fontSize,
            sidebar_style: sidebarStyle
        });
        
        showAlert('info', 'Theme preview applied. Save to make changes permanent.');
    });
    
    // Theme mode change handler
    $('#theme_mode').on('change', function() {
        const mode = $(this).val();
        if (mode === 'auto') {
            // Detect system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyThemeMode(prefersDark ? 'dark' : 'light');
        } else {
            applyThemeMode(mode);
        }
    });
});

function applyThemeChanges(themeMode, preferences) {
    // Apply theme mode
    applyThemeMode(themeMode);
    
    // Apply theme preferences
    if (preferences) {
        if (preferences.primary_color) {
            document.documentElement.style.setProperty('--bs-primary', preferences.primary_color);
        }
        
        if (preferences.font_size) {
            const fontSizes = {
                'small': '0.875rem',
                'medium': '1rem',
                'large': '1.125rem'
            };
            document.documentElement.style.setProperty('--bs-body-font-size', fontSizes[preferences.font_size]);
        }
        
        if (preferences.sidebar_style) {
            $('body').removeClass('sidebar-compact sidebar-minimal').addClass('sidebar-' + preferences.sidebar_style);
        }
    }
}

function previewThemeChanges(themeMode, preferences) {
    applyThemeChanges(themeMode, preferences);
}

function applyThemeMode(mode) {
    if (mode === 'dark') {
        $('html').attr('data-bs-theme', 'dark');
        $('body').addClass('dark-mode').removeClass('light-mode');
    } else {
        $('html').attr('data-bs-theme', 'light');
        $('body').addClass('light-mode').removeClass('dark-mode');
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'info' ? 'alert-info' : 'alert-warning';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at the top of the page
    $('.container').prepend(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endpush

@push('styles')
<style>
.form-check-label {
    cursor: pointer;
}

.form-check-input {
    cursor: pointer;
}

.card-title {
    font-size: 1.1rem;
}

.form-label {
    font-weight: 600;
}

.btn {
    font-weight: 500;
}
</style>
@endpush