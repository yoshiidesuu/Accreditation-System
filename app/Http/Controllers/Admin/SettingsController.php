<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display the admin settings page.
     */
    public function index()
    {
        // Get current system settings
        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'app_timezone' => config('app.timezone'),
            'mail_driver' => config('mail.default'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port'),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'filesystem_driver' => config('filesystems.default'),
            'database_connection' => config('database.default'),
        ];

        // Get system information
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => $this->getDatabaseVersion(),
            'storage_used' => $this->getStorageUsage(),
            'cache_size' => $this->getCacheSize(),
        ];

        // Get maintenance settings
        $maintenanceSettings = [
            'maintenance_mode' => app()->isDownForMaintenance(),
            'backup_enabled' => config('backup.backup.enabled', false),
            'log_level' => config('logging.level', 'debug'),
            'debug_mode' => config('app.debug'),
        ];

        // Get security settings
        $securitySettings = [
            'session_lifetime' => config('session.lifetime'),
            'password_timeout' => config('auth.password_timeout'),
            'max_login_attempts' => config('auth.throttle.max_attempts', 5),
            'lockout_duration' => config('auth.throttle.decay_minutes', 1),
        ];

        return view('admin.settings.index', compact(
            'settings',
            'systemInfo',
            'maintenanceSettings',
            'securitySettings'
        ));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'app_timezone' => 'required|string',
            'mail_driver' => 'required|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
            'session_lifetime' => 'required|integer|min:1',
            'password_timeout' => 'required|integer|min:1',
            'max_login_attempts' => 'required|integer|min:1',
            'lockout_duration' => 'required|integer|min:1',
        ]);

        try {
            // Update .env file with new settings
            $this->updateEnvFile([
                'APP_NAME' => $request->app_name,
                'APP_URL' => $request->app_url,
                'APP_TIMEZONE' => $request->app_timezone,
                'MAIL_MAILER' => $request->mail_driver,
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username,
                'MAIL_FROM_ADDRESS' => $request->mail_from_address,
                'MAIL_FROM_NAME' => $request->mail_from_name,
                'SESSION_LIFETIME' => $request->session_lifetime,
                'AUTH_PASSWORD_TIMEOUT' => $request->password_timeout,
            ]);

            // Update mail password if provided
            if ($request->filled('mail_password')) {
                $this->updateEnvFile(['MAIL_PASSWORD' => $request->mail_password]);
            }

            // Clear config cache to apply changes
            Artisan::call('config:clear');
            Cache::flush();

            return back()->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance(Request $request)
    {
        try {
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
                $message = 'Maintenance mode disabled.';
            } else {
                $secret = $request->input('secret', 'admin-secret');
                Artisan::call('down', [
                    '--secret' => $secret,
                    '--render' => 'errors::503',
                ]);
                $message = 'Maintenance mode enabled.';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to toggle maintenance mode: ' . $e->getMessage());
        }
    }

    /**
     * Clear application cache.
     */
    public function clearCache(Request $request)
    {
        try {
            $type = $request->input('type', 'all');

            switch ($type) {
                case 'config':
                    Artisan::call('config:clear');
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    break;
                case 'cache':
                    Artisan::call('cache:clear');
                    break;
                case 'all':
                default:
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Artisan::call('cache:clear');
                    break;
            }

            return back()->with('success', ucfirst($type) . ' cache cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Optimize application.
     */
    public function optimize()
    {
        try {
            Artisan::call('optimize');
            return back()->with('success', 'Application optimized successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to optimize application: ' . $e->getMessage());
        }
    }

    /**
     * Get database version.
     */
    private function getDatabaseVersion()
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            
            switch ($driver) {
                case 'mysql':
                    return DB::select('SELECT VERSION() as version')[0]->version;
                case 'pgsql':
                    return DB::select('SELECT version()')[0]->version;
                case 'sqlite':
                    return DB::select('SELECT sqlite_version() as version')[0]->version;
                default:
                    return 'Unknown';
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get storage usage.
     */
    private function getStorageUsage()
    {
        try {
            $bytes = 0;
            $directories = ['app', 'framework', 'logs'];
            
            foreach ($directories as $dir) {
                $path = storage_path($dir);
                if (is_dir($path)) {
                    $bytes += $this->getDirectorySize($path);
                }
            }
            
            return $this->formatBytes($bytes);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get cache size.
     */
    private function getCacheSize()
    {
        try {
            $cacheDir = storage_path('framework/cache');
            if (is_dir($cacheDir)) {
                return $this->formatBytes($this->getDirectorySize($cacheDir));
            }
            return '0 B';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get directory size recursively.
     */
    private function getDirectorySize($directory)
    {
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Update .env file with new values.
     */
    private function updateEnvFile($data)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }
        
        file_put_contents($envFile, $envContent);
    }
}