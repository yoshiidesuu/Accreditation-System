<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Accreditation System - Manage college accreditation processes">
    <meta name="theme-color" content="#800020">
    
    <title>{{ config('app.name', 'Accreditation System') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Accreditation System">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-light">
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="visually-hidden-focusable btn btn-primary position-absolute top-0 start-0 m-2">Skip to main content</a>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <i class="bi bi-award-fill me-2"></i>
                {{ config('app.name', 'Accreditation System') }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-house-door me-1"></i> Dashboard
                            </a>
                        </li>
                        
                        @can('view colleges')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('colleges.*') ? 'active' : '' }}" href="{{ route('colleges.index') }}">
                                <i class="bi bi-building me-1"></i> Colleges
                            </a>
                        </li>
                        @endcan
                        
                        @can('view parameters')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('parameters.*') ? 'active' : '' }}" href="{{ route('parameters.index') }}">
                                <i class="bi bi-list-check me-1"></i> Parameters
                            </a>
                        </li>
                        @endcan
                        
                        @can('view accreditations')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('accreditations.*') ? 'active' : '' }}" href="{{ route('accreditations.index') }}">
                                <i class="bi bi-clipboard-check me-1"></i> Accreditations
                            </a>
                        </li>
                        @endcan
                        
                        @can('view reports')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-graph-up me-1"></i> Reports
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('reports.swot') }}"><i class="bi bi-diagram-3 me-1"></i> SWOT Analysis</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.ranking') }}"><i class="bi bi-trophy me-1"></i> Area Rankings</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i> Analytics</a></li>
                            </ul>
                        </li>
                        @endcan
                    @endauth
                </ul>
                
                <ul class="navbar-nav">
                    @auth
                        <!-- Notifications -->
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count" style="display: none;">
                                    0
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li id="notification-list">
                                    <div class="dropdown-item-text text-muted text-center py-3">
                                        No new notifications
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="{{ route('notifications.index') }}">View all notifications</a></li>
                            </ul>
                        </li>
                        
                        <!-- User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="bg-white rounded-circle p-1 me-2">
                                    <i class="bi bi-person-fill text-primary"></i>
                                </div>
                                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">{{ Auth::user()->name }}</h6></li>
                                <li><small class="dropdown-item-text text-muted">{{ Auth::user()->email }}</small></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-1"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear me-1"></i> Settings</a></li>
                                @can('access admin')
                                <li><a class="dropdown-item" href="{{ route('admin.index') }}"><i class="bi bi-shield-lock me-1"></i> Admin Panel</a></li>
                                @endcan
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Breadcrumb -->
    @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
    <nav aria-label="breadcrumb" class="bg-white border-bottom">
        <div class="container-fluid mobile-padding">
            <ol class="breadcrumb mb-0 py-2">
                @foreach($breadcrumbs as $breadcrumb)
                    @if($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['title'] }}</li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $breadcrumb['url'] }}" class="text-decoration-none">{{ $breadcrumb['title'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </div>
    </nav>
    @endif
    
    <!-- Main Content -->
    <main id="main-content" class="flex-grow-1">
        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-0 rounded-0" role="alert">
            <div class="container-fluid mobile-padding">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-0 rounded-0" role="alert">
            <div class="container-fluid mobile-padding">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif
        
        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show m-0 rounded-0" role="alert">
            <div class="container-fluid mobile-padding">
                <i class="bi bi-exclamation-circle me-2"></i>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif
        
        @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show m-0 rounded-0" role="alert">
            <div class="container-fluid mobile-padding">
                <i class="bi bi-info-circle me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        @endif
        
        <!-- Page Content -->
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-auto">
        <div class="container-fluid mobile-padding">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-2">{{ config('app.name', 'Accreditation System') }}</h6>
                    <p class="mb-0 text-muted small">Streamlining college accreditation processes with modern technology.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted small">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Accreditation System') }}. All rights reserved.
                    </p>
                    <p class="mb-0 text-muted small">
                        Version {{ config('app.version', '1.0.0') }} | 
                        <a href="{{ route('help') }}" class="text-light text-decoration-none">Help</a> | 
                        <a href="{{ route('privacy') }}" class="text-light text-decoration-none">Privacy</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none" style="z-index: 9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="text-center text-white">
                <div class="spinner-border spinner-maroon mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading...</p>
            </div>
        </div>
    </div>
    
    @stack('scripts')
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(function(error) {
                        console.log('ServiceWorker registration failed');
                    });
            });
        }
        
        // Global loading overlay functions
        window.showLoading = function() {
            document.getElementById('loading-overlay').classList.remove('d-none');
        };
        
        window.hideLoading = function() {
            document.getElementById('loading-overlay').classList.add('d-none');
        };
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>