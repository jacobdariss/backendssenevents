<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Compressible MIME types.
     */
    private const COMPRESSIBLE_TYPES = [
        'text/html',
        'text/css',
        'text/javascript',
        'application/javascript',
        'application/json',
        'text/xml',
        'application/xml',
        'image/svg+xml',
        'text/plain',
    ];

    /**
     * Minimum response size in bytes to compress (1 KB).
     */
    private const MIN_SIZE = 1024;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldCompress($request, $response)) {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false || strlen($content) < self::MIN_SIZE) {
            return $response;
        }

        $compressed = gzencode($content, 6);

        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', (string) strlen($compressed));
        $response->headers->remove('Content-Length');
        $response->headers->set('Vary', 'Accept-Encoding');

        return $response;
    }

    private function shouldCompress(Request $request, Response $response): bool
    {
        // Skip if browser doesn't accept gzip
        if (!str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
            return false;
        }

        // Skip if already compressed
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }

        // Skip binary responses (downloads, streams, images)
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
            || $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
            return false;
        }

        // Only compress compressible content types
        $contentType = $response->headers->get('Content-Type', '');
        foreach (self::COMPRESSIBLE_TYPES as $type) {
            if (str_contains($contentType, $type)) {
                return true;
            }
        }

        return false;
    }
}
