<?php

use App\Models\Patient;
use App\Queries\Patients\PatientPickerQuery;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

/*
| The patient search of the booking dialogs (GET /api/patients, select2 with AJAX).
*/

beforeEach(function (): void {
    actingAs(adminWithPermissions('view_appointment_receptions'), 'admin');
});

it('finds the patients whose name holds every typed word, regardless of case', function (): void {
    // The endpoint returned every patient and the dialog filtered them in the browser.
    $karimova = Patient::factory()->create(['last_name' => 'Каримова', 'first_name' => 'Дильноза', 'middle_name' => 'Рустамовна', 'birth_date' => '1990-04-12']);
    Patient::factory()->create(['last_name' => 'Каримов', 'first_name' => 'Ахмад', 'middle_name' => 'Олимович']);
    Patient::factory()->create(['last_name' => 'Юсупова', 'first_name' => 'Дильноза', 'middle_name' => null]);

    getJson(route('api.patients.index', ['term' => ' карим  ДИЛЬН ']))
        ->assertOk()
        ->assertExactJson([['id' => $karimova->id, 'full_name' => 'Каримова Дильноза Рустамовна', 'birth_date' => '1990-04-12']]);

    // select2 sends the text as q as well.
    expect(getJson(route('api.patients.index', ['q' => 'Карим']))->json('*.full_name'))
        ->toBe(['Каримов Ахмад Олимович', 'Каримова Дильноза Рустамовна']);
});

it('matches the wildcards of LIKE literally', function (): void {
    Patient::factory()->create(['last_name' => 'Ахмедова']);

    expect(getJson(route('api.patients.index', ['term' => '%']))->assertOk()->json())->toBe([])
        ->and(getJson(route('api.patients.index', ['term' => '_']))->assertOk()->json())->toBe([]);
});

it('returns at most a page of patients, also before anything was typed', function (): void {
    Patient::factory()->count(PatientPickerQuery::LIMIT + 3)->create();

    expect(getJson(route('api.patients.index'))->assertOk()->json())->toHaveCount(PatientPickerQuery::LIMIT);
});

it('refuses an overlong search text', function (): void {
    getJson(route('api.patients.index', ['term' => str_repeat('а', 101)]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('term');
});
