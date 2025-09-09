<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Accreditation System') }}</title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#800000">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom User Styles -->
    <style>
        :root {
            --maroon-primary: #800000;
            --maroon-secondary: #a52a2a;
            --maroon-light: #cd5c5c;
            --maroon-dark: #5c0000;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .user-navbar {
            background: linear-gradient(135deg, var(--maroon-primary), var(--maroon-secondary));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .user-navbar .navbar-brand {
            color: white !important;
            font-weight: 600;
        }
        
        .user-navbar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 8px 16px;
            border-radius: 6px;
            margin: 0 4px;
            transition: all 0.3s ease;
        }
        
        .user-navbar .nav-link:hover,
        .user-navbar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white !important;
        }
        
        .user-content {
            padding: 30px 0;
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--maroon-primary), var(--maroon-secondary));
            color: white;
            border-radius: 12px 12px 0 0 !important;
            border: none;
        }
        
        .btn-primary {
            background: var(--maroon-primary);
            border-color: var(--maroon-primary);
        }
        
        .btn-primary:hover {
            background: var(--maroon-secondary);
            border-color: var(--maroon-secondary);
        }
        
        .btn-outline-primary {
            color: var(--maroon-primary);
            border-color: var(--maroon-primary);
        }
        
        .btn-outline-primary:hover {
            background: var(--maroon-primary);
            border-color: var(--maroon-primary);
        }
        
        .text-primary {
            color: var(--maroon-primary) !important;
        }
        
        .bg-primary {
            background-color: var(--maroon-primary) !important;
        }
        
        .role-badge {
            background: var(--maroon-light);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .permission-restricted {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .permission-tooltip {
            position: relative;
        }
        
        .permission-tooltip::after {
            content: 'Access Restricted';
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        
        .permission-tooltip:hover::after {
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .user-content {
                padding: 20px 0;
            }
            
            .card {
                margin-bottom: 15px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- User Navigation -->
    <nav class="navbar navbar-expand-lg user-navbar">
        <div class="container">
            <a class="navbar-brand" href="{{ route('user.dashboard') ?? '#' }}">
                <i class="fas fa-graduation-cap me-2"></i>Accreditation System
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="userNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') ?? '#' }}">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    
                    @can('view', App\Models\College::class)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.colleges*') ? 'active' : '' }}" href="{{ route('user.colleges.index') ?? '#' }}">
                            <i class="fas fa-university me-1"></i>Colleges
                        </a>
                    </li>
                    @endcan
                    
                    @can('view', App\Models\Area::class)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.areas*') ? 'active' : '' }}" href="{{ route('user.areas.index') ?? '#' }}">
                            <i class="fas fa-layer-group me-1"></i>Areas
                        </a>
                    </li>
                    @endcan
                    
                    @can('view', App\Models\Parameter::class)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.parameters*') ? 'active' : '' }}" href="{{ route('user.parameters.index') ?? '#' }}">
                            <i class="fas fa-cogs me-1"></i>Parameters
                        </a>
                    </li>
                    @endcan
                    
                    @can('view', App\Models\ParameterContent::class)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.parameter-contents*') ? 'active' : '' }}" href="{{ route('user.parameter-contents.index') ?? '#' }}">
                            <i class="fas fa-file-alt me-1"></i>Documents
                        </a>
                    </li>
                    @endcan
                    
                    @can('parameter_contents.request_access')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.access-requests*') ? 'active' : '' }}" href="{{ route('user.access-requests.index') ?? '#' }}">
                            <i class="fas fa-key me-1"></i>Access Requests
                        </a>
                    </li>
                    @endcan
                    
                    @hasanyrole('staff')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.accreditations*') ? 'active' : '' }}" href="{{ route('user.accreditations.index') ?? '#' }}">
                            <i class="fas fa-certificate me-1"></i>Accreditations
                        </a>
                    </li>
                    @endhasanyrole
                    
                    @hasrole('overall_coordinator')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.accreditations.coordinatorTagging') || request()->routeIs('user.accreditations.assignAccreditors') || request()->routeIs('user.accreditations.showTagging') ? 'active' : '' }}" href="{{ route('user.accreditations.coordinatorTagging') ?? '#' }}">
                            <i class="fas fa-tags me-1"></i>Coordinator Tagging
                        </a>
                    </li>
                    @endhasrole
                    
                    @hasanyrole('accreditor_lead|accreditor_member')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.accreditations.accreditor-dashboard') ? 'active' : '' }}" href="{{ route('user.accreditations.accreditor-dashboard') ?? '#' }}">
                            <i class="fas fa-clipboard-check me-1"></i>Accreditor Dashboard
                        </a>
                    </li>
                    @endhasanyrole
                    
                    @can('view', App\Models\SwotEntry::class)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.swot*') ? 'active' : '' }}" href="{{ route('user.swot.index') ?? '#' }}">
                            <i class="fas fa-chart-line me-1"></i>SWOT Analysis
                        </a>
                    </li>
                    @endcan
                    
                    @can('view-reports')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.reports*') ? 'active' : '' }}" href="{{ route('user.reports.index') ?? '#' }}">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </li>
                    @endcan
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            {{ auth()->user()->first_name ?? 'User' }} {{ auth()->user()->last_name ?? '' }}
                            <span class="role-badge ms-2">{{ auth()->user()->roles->first()->name ?? 'User' }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('user.profile') ?? '#' }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('user.settings') ?? '#' }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item" id="themeToggle" type="button">
                                    <i class="fas fa-moon me-2" id="themeIcon"></i>
                                    <span id="themeText">Dark Mode</span>
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') ?? '#' }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container user-content">
        <!-- Page Header -->
        @hasSection('page-header')
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h2 text-primary mb-1">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-muted mb-0">@yield('page-description', '')</p>
                    </div>
                    <div>
                        @yield('page-actions')
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <!-- Content -->
        @yield('content')
    </div>
    
    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') ?? '#' }}" method="POST" class="d-none">
        @csrf
    </form>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
    
    <!-- Role-based Feature Visibility -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add permission tooltips to restricted elements
            const restrictedElements = document.querySelectorAll('.permission-restricted');
            restrictedElements.forEach(element => {
                element.classList.add('permission-tooltip');
            });
            
            // Handle role-based navigation highlighting
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
    
    @stack('scripts')
    
    <!-- Theme Toggle Script -->
    <script>
    $(document).ready(function() {
        // Initialize theme based on user preference or system default
        const userThemeMode = '{{ auth()->user()->getThemeMode() ?? "light" }}';
        const userPreferences = @json(auth()->user()->getThemePreferences() ?? []);
        
        // Apply initial theme
        initializeTheme(userThemeMode, userPreferences);
        
        // Theme toggle functionality
        $('#themeToggle').on('click', function() {
            const currentMode = $('html').attr('data-bs-theme') || 'light';
            const newMode = currentMode === 'light' ? 'dark' : 'light';
            
            // Apply theme immediately
            applyThemeMode(newMode);
            updateThemeToggleUI(newMode);
            
            // Save preference to server
            saveThemePreference(newMode);
        });
        
        // Listen for system theme changes when in auto mode
        if (userThemeMode === 'auto') {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addListener(function(e) {
                applyThemeMode(e.matches ? 'dark' : 'light');
                updateThemeToggleUI(e.matches ? 'dark' : 'light');
            });
        }
    });
    
    function initializeTheme(themeMode, preferences) {
        let actualMode = themeMode;
        
        if (themeMode === 'auto') {
            actualMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        
        applyThemeMode(actualMode);
        updateThemeToggleUI(actualMode);
        
        // Apply user preferences
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
            
            if (preferences.sidebar_style && preferences.sidebar_style !== 'default') {
                $('body').addClass('sidebar-' + preferences.sidebar_style);
            }
        }
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
    
    function updateThemeToggleUI(mode) {
        const icon = $('#themeIcon');
        const text = $('#themeText');
        
        if (mode === 'dark') {
            icon.removeClass('fa-moon').addClass('fa-sun');
            text.text('Light Mode');
        } else {
            icon.removeClass('fa-sun').addClass('fa-moon');
            text.text('Dark Mode');
        }
    }
    
    function saveThemePreference(mode) {
        $.ajax({
            url: '{{ route("user.theme.update") }}',
            method: 'PUT',
            data: {
                theme_mode: mode,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log('Theme preference saved:', mode);
            },
            error: function(xhr) {
                console.error('Failed to save theme preference:', xhr.responseText);
            }
        });
    }
    </script>
</body>
</html>