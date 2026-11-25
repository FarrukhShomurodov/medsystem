<?php

use App\Models\Admin;
use Database\Seeders\PermissionSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(PermissionSeeder::class);
});

it('sends the browser hardening headers with dashboard pages', function (): void {
    actingAs(Admin::factory()->mainAdmin()->create(), 'admin')
        ->get('/dashboard')
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()')
        ->assertHeaderMissing('Strict-Transport-Security');
});

it('adds HSTS only to requests made over HTTPS', function (): void {
    actingAs(Admin::factory()->mainAdmin()->create(), 'admin')
        ->get('https://localhost/dashboard')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

it('sends the headers with AJAX responses and the login page too', function (): void {
    $this->get('/admin/login')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $this->getJson('/api/patients')->assertUnauthorized()->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('marks the session cookie HttpOnly and SameSite=Lax', function (): void {
    $cookie = collect($this->get('/admin/login')->headers->getCookies())
        ->firstWhere(fn ($cookie): bool => $cookie->getName() === config('session.cookie'));

    expect($cookie)->not->toBeNull()
        ->and($cookie->isHttpOnly())->toBeTrue()
        ->and($cookie->getSameSite())->toBe('lax');
});
