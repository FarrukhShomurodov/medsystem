<?php

use App\Enums\ReceptionStatus;
use App\Models\Admin;
use App\Models\Reception;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(PermissionSeeder::class);

    // PreventRequestForgery lets every request through while unit tests run
    // (runningUnitTests()); this subclass checks as it does in production.
    $this->app->bind(PreventRequestForgery::class, fn ($app): PreventRequestForgery => new class($app, $app['encrypter']) extends PreventRequestForgery
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    });

    $this->reception = Reception::factory()->create();
    $this->admin = Admin::factory()->create()->givePermissionTo('change_receptions_status');
});

it('rejects an /api write without a CSRF token', function (): void {
    actingAs($this->admin, 'admin')
        ->withHeader('Sec-Fetch-Site', 'cross-site')
        ->call('PUT', "/api/reception-status-update/{$this->reception->id}", ['status' => 'cancelled'])
        ->assertStatus(419)
        ->assertJson(['message' => 'CSRF token mismatch.']);

    expect($this->reception->fresh()->status)->toBe(ReceptionStatus::New);
});

it('rejects an /api write with a wrong CSRF token', function (): void {
    actingAs($this->admin, 'admin')
        ->withSession(['_token' => 'session-token'])
        ->withHeader('X-CSRF-TOKEN', 'forged-token')
        ->putJson("/api/reception-status-update/{$this->reception->id}", ['status' => 'cancelled'])
        ->assertStatus(419);

    expect($this->reception->fresh()->status)->toBe(ReceptionStatus::New);
});

it('accepts the session token in the X-CSRF-TOKEN header set by $.ajaxSetup', function (): void {
    actingAs($this->admin, 'admin')
        ->withSession(['_token' => 'session-token'])
        ->withHeader('X-CSRF-TOKEN', 'session-token')
        ->putJson("/api/reception-status-update/{$this->reception->id}", ['status' => 'cancelled'])
        ->assertOk();

    expect($this->reception->fresh()->status)->toBe(ReceptionStatus::Cancelled);
});

it('accepts a same-origin browser request by its Sec-Fetch-Site header', function (): void {
    // Laravel 13 origin check: browsers send Sec-Fetch-Site; the token covers clients that do not.
    actingAs($this->admin, 'admin')
        ->withHeader('Sec-Fetch-Site', 'same-origin')
        ->putJson("/api/reception-status-update/{$this->reception->id}", ['status' => 'cancelled'])
        ->assertOk();
});
