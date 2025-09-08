<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Offline - Accreditation System</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#800020">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="AccredSys">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <!-- Inline critical CSS for offline page -->
    <style>
        :root {
            --maroon-primary: #800020;
            --maroon-secondary: #a0002a;
            --maroon-light: #f8f1f3;
            --maroon-dark: #600018;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, var(--maroon-light) 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            line-height: 1.6;
        }
        
        .offline-container {
            text-align: center;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(128, 0, 32, 0.1);
            border: 1px solid rgba(128, 0, 32, 0.1);
        }
        
        .offline-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: var(--maroon-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
        
        .offline-title {
            color: var(--maroon-primary);
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .offline-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        
        .offline-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            min-width: 140px;
        }
        
        .btn-primary {
            background: var(--maroon-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--maroon-secondary);
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--maroon-primary);
            border: 2px solid var(--maroon-primary);
        }
        
        .btn-outline:hover {
            background: var(--maroon-primary);
            color: white;
        }
        
        .connection-status {
            margin-top: 2rem;
            padding: 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            display: none;
        }
        
        .connection-status.online {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .connection-status.offline {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .features-list {
            text-align: left;
            margin: 2rem 0;
            padding: 1.5rem;
            background: var(--maroon-light);
            border-radius: 8px;
        }
        
        .features-list h4 {
            color: var(--maroon-primary);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .features-list ul {
            list-style: none;
            padding: 0;
        }
        
        .features-list li {
            padding: 0.5rem 0;
            color: #555;
            position: relative;
            padding-left: 1.5rem;
        }
        
        .features-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--maroon-primary);
            font-weight: bold;
        }
        
        @media (max-width: 480px) {
            .offline-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .offline-title {
                font-size: 1.5rem;
            }
            
            .offline-message {
                font-size: 1rem;
            }
            
            .offline-actions {
                flex-direction: column;
            }
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">
            📡
        </div>
        
        <h1 class="offline-title">You are currently offline</h1>
        
        <p class="offline-message">
            It looks like you've lost your internet connection. Don't worry - some features of the Accreditation System are still available offline.
        </p>
        
        <div class="features-list">
            <h4>Available Offline:</h4>
            <ul>
                <li>View cached dashboard data</li>
                <li>Browse previously loaded reports</li>
                <li>Access downloaded parameter files</li>
                <li>View notification history</li>
            </ul>
        </div>
        
        <div class="offline-actions">
            <button onclick="checkConnection()" class="btn btn-primary" id="retry-btn">
                Try Again
            </button>
            <a href="/" class="btn btn-outline">Go to Dashboard</a>
        </div>
        
        <div class="connection-status" id="connection-status">
            <span id="status-text">Checking connection...</span>
        </div>
    </div>
    
    <script>
        // Check online/offline status
        function updateConnectionStatus() {
            const statusEl = document.getElementById('connection-status');
            const statusText = document.getElementById('status-text');
            
            if (navigator.onLine) {
                statusEl.className = 'connection-status online';
                statusText.textContent = '✓ Connection restored! You can now access all features.';
                statusEl.style.display = 'block';
                
                // Auto-redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '/';
                }, 2000);
            } else {
                statusEl.className = 'connection-status offline';
                statusText.textContent = '✗ Still offline. Please check your internet connection.';
                statusEl.style.display = 'block';
            }
        }
        
        function checkConnection() {
            const retryBtn = document.getElementById('retry-btn');
            retryBtn.innerHTML = '<span class="loading"></span>Checking...';
            retryBtn.disabled = true;
            
            // Simulate checking connection
            setTimeout(() => {
                updateConnectionStatus();
                retryBtn.innerHTML = 'Try Again';
                retryBtn.disabled = false;
            }, 1500);
        }
        
        // Listen for online/offline events
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        
        // Initial status check
        updateConnectionStatus();
        
        // Register service worker if not already registered
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered:', registration);
                })
                .catch(error => {
                    console.log('SW registration failed:', error);
                });
        }
    </script>
</body>
</html>