<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class FileRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        // Create a unique key for this user/IP for file requests
        $key = 'file_requests:' . ($request->user()?->id ?? $request->ip());
        
        // Check if the rate limit has been exceeded
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            throw new TooManyRequestsHttpException(
                $seconds,
                'Too many file requests. Please try again in ' . $seconds . ' seconds.'
            );
        }
        
        // Increment the number of attempts
        RateLimiter::hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        // Add rate limit headers
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $maxAttempts - RateLimiter::attempts($key)),
            'X-RateLimit-Reset' => now()->addSeconds(RateLimiter::availableIn($key))->timestamp,
        ]);
        
        return $response;
    }
}