<?php

use App\Models\Patient;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

/*
| Input that no form can produce (bytes that are not UTF-8, a NUL byte) is refused before it reaches
| a query. PostgreSQL rejects such bytes (SQLSTATE 22021: the cashier list answered 500) and PDO cuts a
| bound string at the NUL byte, so a filter or a saved field silently took another value.
*/

it('refuses a filter that is not valid UTF-8 or carries a NUL byte', function (array $filters): void {
    actingAs(adminWithPermissions('list_patients'), 'admin')
        ->get(route('patients.index', $filters))
        ->assertBadRequest();
})->with([
    'bytes that are not UTF-8' => [['last_name' => "\xFF\xFE"]],
    'a NUL byte' => [['first_name' => "Dil\0noza"]],
    'a key that is not UTF-8' => [["\xFF" => 'x']],
    'a nested value' => [['tags' => ['ok', "\xC3"]]],
]);

it('refuses a malformed form or JSON body and answers an AJAX call with JSON', function (): void {
    $patient = Patient::factory()->create();

    actingAs(adminWithPermissions('edit_patients'), 'admin')
        ->put(route('patients.update', $patient), ['first_name' => "Anv\0ar"])
        ->assertBadRequest();

    // JSON cannot carry bytes that are not UTF-8, but "\u0000" decodes to a NUL byte.
    actingAs(adminWithPermissions('create_receptions'), 'admin')
        ->postJson('/api/receptions', ['comment' => "Aller\0gy"])
        ->assertBadRequest()
        ->assertJsonPath('message', 'Запрос содержит недопустимые символы.');

    expect($patient->fresh()->first_name)->toBe($patient->first_name);
});

it('lets Cyrillic and other well-formed input through', function (): void {
    actingAs(adminWithPermissions('list_patients'), 'admin')
        ->get(route('patients.index', ['last_name' => 'Иванова', 'first_name' => 'Ёлка 😀']))
        ->assertOk();
});

it('answers 404, not a database error, to a record id in the URL that is not a whole number', function (string $method, string $uri, string $permission): void {
    // Implicit route binding sent "abc" to PostgreSQL as the id, which refused it (22P02, or
    // 22003 for a number beyond bigint): the page answered 500.
    actingAs(adminWithPermissions($permission), 'admin')
        ->call($method, $uri)
        ->assertNotFound();
})->with([
    'a patient card' => ['GET', '/dashboard/patients/abc', 'view_patients'],
    'a patient id beyond bigint' => ['GET', '/dashboard/patients/99999999999999999999', 'view_patients'],
    'a stay form' => ['GET', '/dashboard/maps/1x/edit', 'edit_maps'],
    'a form submission' => ['PUT', '/dashboard/patients/abc', 'edit_patients'],
    'an AJAX endpoint' => ['GET', '/api/receptions/abc', 'view_appointment_receptions'],
]);

it('answers an AJAX endpoint given a malformed id with a JSON 404', function (): void {
    actingAs(adminWithPermissions('view_appointment_receptions'), 'admin')
        ->get('/api/receptions/abc')
        ->assertNotFound()
        ->assertHeader('Content-Type', 'application/json');
});

it('leaves a malformed number from anywhere but the URL a server error', function (): void {
    // Only route binding's own query is mapped: elsewhere 22P02 is a bug to see, not a 404.
    try {
        DB::transaction(fn () => DB::select('select ?::bigint as id', ['abc']));
    } catch (QueryException $e) {
    }

    expect($e ?? null)->toBeInstanceOf(QueryException::class)
        ->and($e->getCode())->toBe('22P02')
        ->and(app(ExceptionHandler::class)->render(request(), $e)->getStatusCode())->toBe(500);
});
