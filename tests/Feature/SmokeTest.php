<?php

it('reports the application as healthy', function (): void {
    $this->get('/up')->assertOk();
});

it('renders the dashboard login form', function (): void {
    $this->get('/admin/login')
        ->assertOk()
        ->assertSeeHtml('action="'.route('dashboard.login').'"')
        ->assertSeeHtml('name="email"')
        ->assertSeeHtml('name="password"');
});

it('redirects guests from the dashboard to the login page', function (): void {
    $this->get('/dashboard')->assertRedirectToRoute('dashboard.login');
});
