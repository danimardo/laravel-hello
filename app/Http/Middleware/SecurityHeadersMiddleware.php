<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS filtering
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy
        $viteUrl = env('VITE_DEV_SERVER_URL', 'http://localhost:5173');
        $isLocal = app()->environment('local');

        // En desarrollo, permitir Vite server (localhost:5173)
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval'" . ($isLocal ? " {$viteUrl}" : "");
        $styleSrc = "'self' 'unsafe-inline' https://fonts.googleapis.com" . ($isLocal ? " {$viteUrl}" : "");
        $connectSrc = "'self'" . ($isLocal ? " {$viteUrl} ws://localhost:5173 wss://localhost:5173" : "");

        $csp = "default-src 'self'; " .
               "style-src {$styleSrc}; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "script-src {$scriptSrc}; " .
               "img-src 'self' data: https:; " .
               "connect-src {$connectSrc}; " .
               "frame-ancestors 'none';";
        $response->headers->set('Content-Security-Policy', $csp);

        // Permissions Policy (formerly Feature Policy)
        $permissionsPolicy = "geolocation=(), " .
                           "camera=(), " .
                           "microphone=(), " .
                           "payment=(), " .
                           "usb=()";
        $response->headers->set('Permissions-Policy', $permissionsPolicy);

        // HSTS (only in production and for HTTPS)
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Remove server signature
        $response->headers->remove('Server');

        return $response;
    }
}
