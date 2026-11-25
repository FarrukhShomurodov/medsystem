<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline browser hardening for every page and AJAX response of the web group.
 * No Content-Security-Policy yet: three views still have inline scripts (cashier, transfers and
 * the main warehouse's auto-submitting filters). Every asset is served from public/build.
 */
class SecurityHeaders
{
    /** @var array<string, string> */
    private const array HEADERS = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
    ];

    /** One year; sent over HTTPS only, as browsers ignore it on plain HTTP. */
    private const string HSTS = 'max-age=31536000; includeSubDomains';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = $request->isSecure()
            ? self::HEADERS + ['Strict-Transport-Security' => self::HSTS]
            : self::HEADERS;

        // A response that already sets one of these headers keeps its own value.
        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
