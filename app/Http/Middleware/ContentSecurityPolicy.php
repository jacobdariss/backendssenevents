<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Uniquement sur les réponses HTML
        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $appUrl    = config('app.url', '');
        $cdnDomain = 'https://cdnjs.cloudflare.com';
        $jsdelivr  = 'https://cdn.jsdelivr.net';
        $bunny     = 'https://*.b-cdn.net';
        $youtube   = 'https://www.youtube.com https://www.youtube-nocookie.com';
        $firebase  = 'https://*.googleapis.com https://*.gstatic.com https://fcm.googleapis.com';

        $policies = [
            "default-src 'self' {$appUrl}",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$cdnDomain} {$jsdelivr} https://js.stripe.com https://www.paypal.com {$firebase}",
            "style-src 'self' 'unsafe-inline' {$cdnDomain} {$jsdelivr} https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com {$cdnDomain}",
            "img-src 'self' data: blob: {$appUrl} {$bunny} https://*.amazonaws.com https://img.youtube.com",
            "media-src 'self' blob: {$appUrl} {$bunny} https://*.amazonaws.com",
            "frame-src 'self' {$youtube} https://js.stripe.com https://www.paypal.com",
            "connect-src 'self' {$appUrl} {$firebase} https://fcm.googleapis.com wss:",
            "worker-src 'self' blob:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', $policies)
        );

        // Headers de sécurité supplémentaires
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
