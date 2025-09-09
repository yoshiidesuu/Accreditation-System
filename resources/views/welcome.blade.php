<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Accreditation Management System') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-maroon: #800020;
            --secondary-maroon: #a0002a;
            --light-maroon: #b33347;
            --dark-maroon: #5c0017;
            --accent-gold: #d4af37;
            --light-gold: #f4e4a6;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            overflow-x: hidden;
        }
        
        /* Navigation */
        .navbar {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--secondary-maroon) 100%);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(128, 0, 32, 0.1);
            transition: all 0.3s ease;
            padding: 1rem 0;
        }
        
        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 2px 30px rgba(128, 0, 32, 0.2);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--white) !important;
            text-decoration: none;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--accent-gold) !important;
            transform: translateY(-2px);
        }
        
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: var(--accent-gold);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }
        
        .btn-login {
            background: var(--accent-gold);
            color: var(--primary-maroon) !important;
            border: 2px solid var(--accent-gold);
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-login:hover {
            background: transparent;
            color: var(--accent-gold) !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--secondary-maroon) 50%, var(--dark-maroon) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        .btn-primary-custom {
            background: var(--accent-gold);
            color: var(--primary-maroon);
            border: none;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-right: 1rem;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
            color: var(--primary-maroon);
        }
        
        .btn-secondary-custom {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary-custom:hover {
            background: var(--white);
            color: var(--primary-maroon);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.2);
        }
        
        .hero-image {
            position: relative;
        }
        
        .hero-image img {
            max-width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        /* Features Section */
        .features {
            padding: 5rem 0;
            background: var(--bg-light);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-maroon);
            margin-bottom: 1rem;
        }
        
        .section-title p {
            font-size: 1.1rem;
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: var(--white);
            padding: 2.5rem 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(128, 0, 32, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(128, 0, 32, 0.1);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(128, 0, 32, 0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-maroon), var(--secondary-maroon));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: var(--white);
            font-size: 2rem;
        }
        
        .feature-card h4 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-maroon);
            margin-bottom: 1rem;
        }
        
        .feature-card p {
            color: var(--text-light);
            line-height: 1.6;
        }
        
        /* Stats Section */
        .stats {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--secondary-maroon) 100%);
            padding: 4rem 0;
            color: var(--white);
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--accent-gold);
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        /* About Section */
        .about {
            padding: 5rem 0;
        }
        
        .about-content {
            display: flex;
            align-items: center;
            gap: 3rem;
        }
        
        .about-text h3 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary-maroon);
            margin-bottom: 1.5rem;
        }
        
        .about-text p {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }
        
        .about-features {
            list-style: none;
            padding: 0;
        }
        
        .about-features li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }
        
        .about-features li i {
            color: var(--accent-gold);
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, var(--dark-maroon) 0%, var(--primary-maroon) 100%);
            padding: 4rem 0;
            color: var(--white);
            text-align: center;
        }
        
        .cta h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .cta p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        /* Footer */
        .footer {
            background: var(--text-dark);
            color: var(--white);
            padding: 3rem 0 1rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h5 {
            color: var(--accent-gold);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .footer-section p,
        .footer-section a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            line-height: 1.6;
        }
        
        .footer-section a:hover {
            color: var(--accent-gold);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .btn-primary-custom,
            .btn-secondary-custom {
                display: block;
                margin: 0.5rem 0;
                text-align: center;
            }
            
            .about-content {
                flex-direction: column;
                text-align: center;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
        }
        
        /* Animations */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                {{ config('app.name', 'AMS') }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn-login">
                                    <i class="fas fa-tachometer-alt me-1"></i>
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn-login">
                                    <i class="fas fa-sign-in-alt me-1"></i>
                                    Login
                                </a>
                            @endauth
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="hero-content">
                        <h1>Excellence in Academic Accreditation</h1>
                        <p>Streamline your accreditation processes with our comprehensive management system. Track, manage, and report on all aspects of institutional quality assurance.</p>
                        <div class="hero-buttons">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="btn-primary-custom">
                                        <i class="fas fa-tachometer-alt me-2"></i>
                                        Go to Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn-primary-custom">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Get Started
                                    </a>
                                @endauth
                            @endif
                            <a href="#features" class="btn-secondary-custom">
                                <i class="fas fa-info-circle me-2"></i>
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-image">
                        <div class="floating">
                            <svg width="500" height="400" viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- University Building -->
                                <rect x="150" y="200" width="200" height="150" fill="#f8f9fa" stroke="#d4af37" stroke-width="2"/>
                                <rect x="170" y="220" width="30" height="40" fill="#800020"/>
                                <rect x="210" y="220" width="30" height="40" fill="#800020"/>
                                <rect x="250" y="220" width="30" height="40" fill="#800020"/>
                                <rect x="290" y="220" width="30" height="40" fill="#800020"/>
                                <rect x="170" y="270" width="30" height="40" fill="#800020"/>
                                <rect x="210" y="270" width="30" height="40" fill="#800020"/>
                                <rect x="250" y="270" width="30" height="40" fill="#800020"/>
                                <rect x="290" y="270" width="30" height="40" fill="#800020"/>
                                <rect x="220" y="320" width="60" height="30" fill="#d4af37"/>
                                <!-- Pillars -->
                                <rect x="140" y="180" width="15" height="170" fill="#d4af37"/>
                                <rect x="345" y="180" width="15" height="170" fill="#d4af37"/>
                                <!-- Roof -->
                                <polygon points="130,180 250,120 370,180" fill="#800020"/>
                                <!-- Flag -->
                                <rect x="380" y="100" width="3" height="80" fill="#2c3e50"/>
                                <rect x="383" y="100" width="40" height="25" fill="#d4af37"/>
                                <!-- Documents floating -->
                                <rect x="50" y="150" width="60" height="80" fill="#ffffff" stroke="#800020" stroke-width="2" rx="5" transform="rotate(-15 80 190)"/>
                                <rect x="400" y="180" width="60" height="80" fill="#ffffff" stroke="#800020" stroke-width="2" rx="5" transform="rotate(10 430 220)"/>
                                <!-- Checkmarks -->
                                <path d="M70 180 L75 185 L85 175" stroke="#28a745" stroke-width="3" fill="none" transform="rotate(-15 80 190)"/>
                                <path d="M420 210 L425 215 L435 205" stroke="#28a745" stroke-width="3" fill="none" transform="rotate(10 430 220)"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                        <span class="stat-number" data-count="500">0</span>
                        <span class="stat-label">Institutions Served</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                        <span class="stat-number" data-count="1000">0</span>
                        <span class="stat-label">Accreditation Reports</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                        <span class="stat-number" data-count="98">0</span>
                        <span class="stat-label">Success Rate %</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
                        <span class="stat-number" data-count="24">0</span>
                        <span class="stat-label">Support Hours</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-title" data-aos="fade-up">
                <h2>Comprehensive Features</h2>
                <p>Everything you need to manage your accreditation processes efficiently and effectively</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h4>Parameter Management</h4>
                        <p>Define and manage accreditation parameters with dynamic form fields, validation rules, and hierarchical organization for comprehensive quality assessment.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h4>Accreditation Tracking</h4>
                        <p>Track accreditation status, manage documentation, set deadlines, and monitor progress with automated notifications and comprehensive reporting.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>SWOT Analysis</h4>
                        <p>Conduct comprehensive SWOT analysis with structured data collection, impact assessment, and strategic planning tools for institutional improvement.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4>Report Generation</h4>
                        <p>Generate comprehensive reports in multiple formats (PDF, Excel, CSV) with customizable templates, charts, and automated data visualization.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h4>Role-Based Access</h4>
                        <p>Secure role-based access control with different permission levels for administrators, staff, coordinators, and faculty members.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <h4>Multi-College Support</h4>
                        <p>Manage multiple colleges and departments with centralized administration, individual college dashboards, and cross-institutional reporting.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="about-text">
                        <h3>Why Choose Our Accreditation Management System?</h3>
                        <p>Our comprehensive platform is designed specifically for educational institutions seeking to streamline their accreditation processes. With years of experience in quality assurance and educational technology, we provide the tools you need to succeed.</p>
                        
                        <ul class="about-features">
                            <li><i class="fas fa-check-circle"></i> Intuitive user interface designed for educators</li>
                            <li><i class="fas fa-check-circle"></i> Comprehensive documentation management</li>
                            <li><i class="fas fa-check-circle"></i> Real-time collaboration tools</li>
                            <li><i class="fas fa-check-circle"></i> Automated workflow management</li>
                            <li><i class="fas fa-check-circle"></i> Advanced analytics and reporting</li>
                            <li><i class="fas fa-check-circle"></i> 24/7 technical support</li>
                        </ul>
                        
                        <a href="#contact" class="btn-primary-custom">
                            <i class="fas fa-envelope me-2"></i>
                            Contact Us
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="about-image">
                        <svg width="500" height="400" viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Dashboard mockup -->
                            <rect x="50" y="50" width="400" height="300" fill="#ffffff" stroke="#800020" stroke-width="2" rx="10"/>
                            <!-- Header -->
                            <rect x="50" y="50" width="400" height="60" fill="#800020" rx="10"/>
                            <circle cx="80" cy="80" r="8" fill="#d4af37"/>
                            <rect x="100" y="75" width="100" height="10" fill="#ffffff" rx="5"/>
                            <!-- Navigation -->
                            <rect x="70" y="130" width="80" height="8" fill="#800020" rx="4"/>
                            <rect x="160" y="130" width="80" height="8" fill="#a0002a" rx="4"/>
                            <rect x="250" y="130" width="80" height="8" fill="#a0002a" rx="4"/>
                            <!-- Content cards -->
                            <rect x="70" y="160" width="100" height="80" fill="#f8f9fa" stroke="#e9ecef" stroke-width="1" rx="5"/>
                            <rect x="190" y="160" width="100" height="80" fill="#f8f9fa" stroke="#e9ecef" stroke-width="1" rx="5"/>
                            <rect x="310" y="160" width="100" height="80" fill="#f8f9fa" stroke="#e9ecef" stroke-width="1" rx="5"/>
                            <!-- Charts -->
                            <rect x="70" y="260" width="180" height="60" fill="#f8f9fa" stroke="#e9ecef" stroke-width="1" rx="5"/>
                            <rect x="270" y="260" width="140" height="60" fill="#f8f9fa" stroke="#e9ecef" stroke-width="1" rx="5"/>
                            <!-- Chart elements -->
                            <path d="M80 310 L100 300 L120 290 L140 295 L160 285 L180 280 L200 275 L220 270 L240 265" stroke="#800020" stroke-width="2" fill="none"/>
                            <circle cx="320" cy="290" r="20" fill="none" stroke="#800020" stroke-width="3"/>
                            <path d="M320 270 A20 20 0 0 1 340 290" fill="#d4af37"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                    <h3>Ready to Transform Your Accreditation Process?</h3>
                    <p>Join hundreds of institutions already using our platform to streamline their quality assurance processes.</p>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primary-custom">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Access Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary-custom">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Get Started Today
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h5><i class="fas fa-graduation-cap me-2"></i>{{ config('app.name', 'AMS') }}</h5>
                    <p>Empowering educational institutions with comprehensive accreditation management solutions. Excellence in quality assurance through innovative technology.</p>
                </div>
                
                <div class="footer-section">
                    <h5>Quick Links</h5>
                    <p><a href="#home">Home</a></p>
                    <p><a href="#features">Features</a></p>
                    <p><a href="#about">About</a></p>
                    <p><a href="{{ route('login') }}">Login</a></p>
                </div>
                
                <div class="footer-section">
                    <h5>Features</h5>
                    <p><a href="#">Parameter Management</a></p>
                    <p><a href="#">Accreditation Tracking</a></p>
                    <p><a href="#">SWOT Analysis</a></p>
                    <p><a href="#">Report Generation</a></p>
                </div>
                
                <div class="footer-section">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-envelope me-2"></i>info@accreditationms.com</p>
                    <p><i class="fas fa-phone me-2"></i>+1 (555) 123-4567</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i>123 Education St, Academic City</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Accreditation Management System') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const increment = target / 100;
                let current = 0;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        counter.textContent = Math.ceil(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
            });
        }
        
        // Trigger counter animation when stats section is visible
        const statsSection = document.querySelector('.stats');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        if (statsSection) {
            observer.observe(statsSection);
        }
        
        // Add fade-in animation to elements
        const fadeElements = document.querySelectorAll('.fade-in');
        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        });
        
        fadeElements.forEach(element => {
            fadeObserver.observe(element);
        });
    </script>
</body>
</html>
