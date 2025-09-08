<?php

namespace App\Providers;

use App\Helpers\SecurityHelper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('security', function () {
            return new SecurityHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register custom Blade directives for XSS protection
        Blade::directive('safe', function ($expression) {
            return "<?php echo \\App\\Helpers\\SecurityHelper::encodeOutput($expression); ?>";
        });
        
        Blade::directive('safeHtml', function ($expression) {
            return "<?php echo \\App\\Helpers\\SecurityHelper::safeHtml($expression); ?>";
        });
        
        // Register custom validation rules
        Validator::extend('no_xss', function ($attribute, $value, $parameters, $validator) {
            return !SecurityHelper::containsXSS($value);
        });
        
        Validator::extend('safe_filename', function ($attribute, $value, $parameters, $validator) {
            // Check for directory traversal and dangerous characters
            return !preg_match('/\.\.|\/|\\|[<>:"|?*]/', $value);
        });
        
        // Custom validation messages
        Validator::replacer('no_xss', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' field contains potentially dangerous content.';
        });
        
        Validator::replacer('safe_filename', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' field contains invalid characters for a filename.';
        });
    }
}