<?php

use App\Models\Admin;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\StaffDocument;
use App\Services\AdminService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(PermissionSeeder::class);

    $this->admin = Admin::factory()->mainAdmin()->create();
});

function dismissedEmployee(): Staff
{
    return Staff::factory()->has(StaffDocument::factory()->dismissed(), 'document')->create();
}

it('signs an admin in and sends them to the page they asked for', function (): void {
    $this->get('/dashboard/patients')->assertRedirectToRoute('dashboard.login');

    $this->post('/admin/login', ['email' => $this->admin->email, 'password' => 'password'])
        ->assertRedirect('/dashboard/patients');

    $this->assertAuthenticatedAs($this->admin, 'admin');
});

it('rejects wrong credentials with a Russian message', function (): void {
    $this->from('/admin/login')
        ->post('/admin/login', ['email' => $this->admin->email, 'password' => 'wrong-password'])
        ->assertRedirect('/admin/login')
        ->assertSessionHasErrors(['login' => 'Неверные данные для входа в систему.']);

    $this->assertGuest('admin');
});

it('refuses sign-in to a dismissed employee, saying why only to someone who knows the password', function (): void {
    // Dismissal only set staff_documents.is_work: the employee's login went on working.
    $employee = Admin::factory()->forStaff(dismissedEmployee())->create();

    $this->from('/admin/login')
        ->post('/admin/login', ['email' => $employee->email, 'password' => 'password'])
        ->assertRedirect('/admin/login')
        ->assertSessionHasErrors(['login' => 'Учётная запись отключена: сотрудник уволен.']);

    $this->post('/admin/login', ['email' => $employee->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors(['login' => 'Неверные данные для входа в систему.']);

    $this->assertGuest('admin');
});

it('signs in employees still employed, also those registered without employment documents', function (): void {
    foreach ([Staff::factory()->nurse()->create(), Staff::factory()->create()] as $staff) {
        $employee = Admin::factory()->forStaff($staff)->create();

        $this->post('/admin/login', ['email' => $employee->email, 'password' => 'password'])
            ->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($employee, 'admin');
        Auth::guard('admin')->logout();
    }
});

it('ends the session of an employee dismissed while signed in', function (): void {
    $staff = Staff::factory()->nurse()->create();
    $employee = Admin::factory()->forStaff($staff)->create()->givePermissionTo('view_dashboard');

    actingAs($employee, 'admin')->get('/dashboard')->assertOk();

    $staff->document->update(['is_work' => false]);

    $this->withSession(['marker' => true])
        ->get('/dashboard')
        ->assertRedirectToRoute('dashboard.login')
        ->assertSessionHasErrors(['login' => 'Учётная запись отключена: сотрудник уволен.']);

    $this->assertGuest('admin');
    expect(session()->has('marker'))->toBeFalse();

    $this->get('/dashboard')->assertRedirectToRoute('dashboard.login');
});

it('answers the AJAX requests of a dismissed employee with 401', function (): void {
    $employee = Admin::factory()->forStaff(dismissedEmployee())->create()->givePermissionTo('create_patient_services');

    actingAs($employee, 'admin')
        ->getJson(route('api.examination-maps.by-patient', Patient::factory()->create()))
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Учётная запись отключена: сотрудник уволен.']);

    $this->assertGuest('admin');
});

it('refuses a dismissed employee who comes back with a "remember me" cookie, before any change is made', function (): void {
    // The cookie signs the account in again without a password, and an AJAX write must not get
    // through either: the account check runs before the route's controller.
    $employee = Admin::factory()->forStaff(dismissedEmployee())->create(['remember_token' => Str::random(60)])
        ->givePermissionTo('create_patient_diagnoses');
    $recaller = Auth::guard('admin')->getRecallerName();
    $cookie = "{$employee->id}|{$employee->remember_token}|{$employee->password}";

    $this->withCookie($recaller, $cookie)
        ->get('/dashboard')
        ->assertRedirectToRoute('dashboard.login')
        ->assertSessionHasErrors(['login' => 'Учётная запись отключена: сотрудник уволен.']);

    $this->assertGuest('admin');

    $this->withCookie($recaller, $cookie)
        ->withHeader('Sec-Fetch-Site', 'same-origin')
        ->postJson('/api/selected-diagnosis', ['diagnosis_id' => 1])
        ->assertUnauthorized();

    expect(DB::table('admin_selected_diagnoses')->count())->toBe(0);
});

/**
 * Signs the account in through the login form, so its id is in the session, as in the browser.
 */
function signInThroughForm(Admin $account, bool $remember = false): void
{
    test()->post('/admin/login', ['email' => $account->email, 'password' => 'password', 'remember' => $remember ? '1' : null])
        ->assertSessionHasNoErrors();
}

/**
 * The next request of the browser: a new PHP process, which knows the account only by its session or cookie.
 */
function nextRequest(): void
{
    Auth::forgetGuards();
}

it('ends the open session of an account whose password another administrator resets', function (): void {
    // Resetting a leaked password left the open session working until it expired.
    $employee = Admin::factory()->forStaff(Staff::factory()->nurse()->create())->create()->givePermissionTo('view_dashboard');

    signInThroughForm($employee);
    $this->get('/dashboard')->assertOk();

    app(AdminService::class)->update($employee->fresh(), ['email' => $employee->email, 'password' => 'a-new-password']);
    nextRequest();

    $this->get('/dashboard')->assertRedirectToRoute('dashboard.login');
    $this->assertGuest('admin');

    nextRequest();
    $this->get('/dashboard')->assertRedirectToRoute('dashboard.login');
});

it('answers the AJAX requests of a session whose password was reset with 401', function (): void {
    $employee = Admin::factory()->forStaff(Staff::factory()->nurse()->create())->create()->givePermissionTo('create_patient_services');
    $patient = Patient::factory()->create();

    signInThroughForm($employee);
    $this->getJson(route('api.examination-maps.by-patient', $patient))->assertOk();

    app(AdminService::class)->update($employee->fresh(), ['email' => $employee->email, 'password' => 'a-new-password']);
    nextRequest();

    $this->getJson(route('api.examination-maps.by-patient', $patient))->assertUnauthorized();
    $this->assertGuest('admin');
});

it('refuses a "remember me" cookie issued before the password was reset', function (): void {
    // The cookie carries a MAC of the password hash: a new password invalidates it without a new remember token.
    $employee = Admin::factory()->forStaff(Staff::factory()->nurse()->create())->create()->givePermissionTo('view_dashboard');
    $recaller = Auth::guard('admin')->getRecallerName();

    signInThroughForm($employee, remember: true);
    $employee->refresh();
    $cookie = "{$employee->id}|{$employee->remember_token}|".Auth::guard('admin')->hashPasswordForCookie($employee->password);

    session()->flush();
    nextRequest();
    $this->withCookie($recaller, $cookie)->get('/dashboard')->assertOk();

    app(AdminService::class)->update($employee->fresh(), ['email' => $employee->email, 'password' => 'a-new-password']);
    session()->flush();
    nextRequest();

    $this->withCookie($recaller, $cookie)->get('/dashboard')->assertRedirectToRoute('dashboard.login');
    $this->assertGuest('admin');
    expect($employee->fresh()->remember_token)->toBe($employee->remember_token);
});

it('keeps signed in an administrator who changes their own password', function (): void {
    signInThroughForm($this->admin);

    $this->put(route('admins.update', $this->admin), ['email' => $this->admin->email, 'password' => 'a-new-password'])
        ->assertRedirect(route('admins.index'));
    nextRequest();

    $this->get('/dashboard')->assertOk();
    $this->assertAuthenticatedAs($this->admin, 'admin');
});

it('validates the login form in Russian', function (): void {
    $this->post('/admin/login', ['email' => 'not-an-email', 'password' => ''])
        ->assertSessionHasErrors(['email', 'password']);

    expect(session('errors')->first('password'))->toContain('Пароль');
});

it('answers an e-mail sent as an array with the validation error, and still counts the attempts', function (array $email): void {
    // The sign-in limiter read the e-mail as a string before the form was validated: email[]=...
    // was a 500 that the limiter never counted, and the login page that re-displayed the flashed
    // array was a 500 too.
    // Followed like a browser, so the form re-displaying the flashed input is rendered too.
    foreach (range(1, 5) as $attempt) {
        $this->from('/admin/login')
            ->followingRedirects()
            ->post('/admin/login', ['email' => $email, 'password' => 'x'])
            ->assertOk()
            ->assertSee('Поле Логин должно быть строкой.');
    }

    $this->from('/admin/login')
        ->followingRedirects()
        ->post('/admin/login', ['email' => $email, 'password' => 'x'])
        ->assertOk()
        ->assertSee('Слишком много попыток входа.');
    $this->assertGuest('admin');
})->with([
    'list' => [['someone@example.test']],
    'nested' => [['x' => ['y' => 'someone@example.test']]],
]);

it('locks the login form after five attempts a minute', function (): void {
    foreach (range(1, 5) as $attempt) {
        $this->post('/admin/login', ['email' => $this->admin->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['login' => 'Неверные данные для входа в систему.']);
    }

    // Even the right password is refused until the minute is over.
    $this->post('/admin/login', ['email' => $this->admin->email, 'password' => 'password'])
        ->assertRedirect()
        ->assertHeader('Retry-After')
        ->assertSessionHasErrors('login');

    expect(session('errors')->first('login'))->toStartWith('Слишком много попыток входа.');
    $this->assertGuest('admin');
});

it('issues a new session id and CSRF token on sign-in', function (): void {
    $sessionId = Str::random(40);

    $this->withCookie(config('session.cookie'), $sessionId)
        ->withSession(['_token' => 'token-before-login'])
        ->post('/admin/login', ['email' => $this->admin->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    expect(session()->getId())->not->toBe($sessionId)
        ->and(session()->token())->not->toBe('token-before-login');
});

it('remembers the admin only when "remember me" is ticked', function (): void {
    $recaller = Auth::guard('admin')->getRecallerName();

    $this->post('/admin/login', ['email' => $this->admin->email, 'password' => 'password'])
        ->assertCookieMissing($recaller);

    Auth::guard('admin')->logout();

    $this->post('/admin/login', ['email' => $this->admin->email, 'password' => 'password', 'remember' => '1'])
        ->assertCookie($recaller);
});

it('signs the admin out, invalidates the session and accepts POST only', function (): void {
    actingAs($this->admin, 'admin')->get('/dashboard')->assertOk();

    $this->get('/dashboard/logout')->assertMethodNotAllowed();

    $this->withSession(['_token' => 'token-before-logout', 'marker' => true])
        ->post('/dashboard/logout')
        ->assertRedirectToRoute('dashboard.login');

    $this->assertGuest('admin');
    expect(session()->has('marker'))->toBeFalse()
        ->and(session()->token())->not->toBe('token-before-logout');

    $this->get('/dashboard')->assertRedirectToRoute('dashboard.login');
});

it('uses the admin guard by default and has no guard for the removed user accounts', function (): void {
    // "web" used to be the default guard: a role or permission created without a guard landed on
    // it, where no admin can hold it, and auth() outside auth:admin saw nobody.
    expect(config('auth.defaults.guard'))->toBe(Admin::GUARD)
        ->and(config('auth.guards'))->not->toHaveKey('user')
        ->and(Permission::create(['name' => 'guardless_permission'])->guard_name)->toBe(Admin::GUARD);

    Auth::guard(Admin::GUARD)->login($this->admin);

    expect(auth()->user()?->is($this->admin))->toBeTrue();
});
