<?php

use App\Models\Admin;

/*
| The sign-in page (admin/auth/login): Russian, its three fields, and the messages of a refused
| sign-in next to the field or above the form.
*/

function loginPage(): DOMXPath
{
    $document = new DOMDocument;
    $document->loadHTML((string) test()->get(route('dashboard.login'))->assertOk()->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);

    return new DOMXPath($document);
}

it('asks for the login, the password and whether to stay signed in', function (): void {
    $page = loginPage();

    expect($page->query('//html[@lang="ru"]')->length)->toBe(1)
        ->and($page->query('//form[@method="POST"][@action="'.route('dashboard.login').'"]/input[@name="_token"]')->length)->toBe(1)
        ->and($page->query('//label[@for="email"]')->length)->toBe(1)
        ->and($page->query('//input[@id="email"][@name="email"][@type="email"][@autocomplete="username"][@required]')->length)->toBe(1)
        ->and($page->query('//label[@for="password"]')->length)->toBe(1)
        ->and($page->query('//input[@id="password"][@name="password"][@type="password"][@autocomplete="current-password"]')->length)->toBe(1)
        ->and($page->query('//button[@data-password-toggle="#password"][@type="button"][@aria-label="Показать пароль"]')->length)->toBe(1)
        ->and($page->query('//input[@type="checkbox"][@name="remember"][@value="1"]')->length)->toBe(1)
        ->and($page->query('//button[@type="submit"][normalize-space()="Войти"]')->length)->toBe(1);
});

it('shows a field error under its field and keeps the typed login', function (): void {
    $this->from(route('dashboard.login'))->post(route('dashboard.login'), ['email' => 'not-an-email', 'password' => ''])->assertRedirect();

    $page = loginPage();

    expect($page->query('//input[@id="email"][contains(@class, "is-invalid")][@value="not-an-email"][@aria-invalid="true"][@aria-describedby="email-error"]')->length)->toBe(1)
        ->and($page->query('//div[@id="email-error"][contains(@class, "invalid-feedback")]')->length)->toBe(1)
        ->and($page->query('//div[@id="password-error"][contains(@class, "invalid-feedback")]')->length)->toBe(1);
});

it('says above the form why the sign-in was refused, also after too many attempts', function (): void {
    $admin = Admin::factory()->create();

    $this->from(route('dashboard.login'))->post(route('dashboard.login'), ['email' => $admin->email, 'password' => 'wrong-password']);

    expect(loginPage()->query('//div[contains(@class, "alert-danger")][@role="alert"][contains(normalize-space(), "Неверные данные для входа")]')->length)->toBe(1);

    foreach (range(1, 6) as $attempt) {
        $this->from(route('dashboard.login'))->post(route('dashboard.login'), ['email' => $admin->email, 'password' => 'wrong-password']);
    }

    expect(loginPage()->query('//div[contains(@class, "alert-danger")][contains(normalize-space(), "Слишком много попыток входа")]')->length)->toBe(1);
});
