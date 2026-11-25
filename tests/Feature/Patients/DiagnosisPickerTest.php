<?php

use App\Models\Diagnosis;
use App\Queries\Patients\DiagnosisPickerQuery;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

/*
| The diagnosis search of the patient card (GET /api/diagnoses): the card renders only the
| viewer's favourites and asks the server for the rest of the ICD-10 catalogue as the user types.
*/

beforeEach(function (): void {
    $this->doctor = adminWithPermissions('create_patient_diagnoses');
    actingAs($this->doctor, 'admin');
});

it('finds diagnoses by the start of their code or by the words of their name, regardless of case', function (): void {
    $bronchitis = Diagnosis::factory()->create(['code' => 'J20.9', 'name' => 'Острый бронхит неуточнённый']);
    $pneumonia = Diagnosis::factory()->create(['code' => 'J18.9', 'name' => 'Пневмония неуточнённая']);
    Diagnosis::factory()->create(['code' => 'K29.5', 'name' => 'Хронический гастрит неуточнённый']);

    getJson(route('api.diagnoses.search', ['term' => 'j']))
        ->assertOk()
        ->assertExactJson([
            ['id' => $pneumonia->id, 'code' => 'J18.9', 'name' => 'Пневмония неуточнённая'],
            ['id' => $bronchitis->id, 'code' => 'J20.9', 'name' => 'Острый бронхит неуточнённый'],
        ]);

    expect(getJson(route('api.diagnoses.search', ['term' => ' БРОНХИТ  острый ']))->json('*.id'))->toBe([$bronchitis->id])
        ->and(getJson(route('api.diagnoses.search', ['term' => 'j20.9']))->json('*.id'))->toBe([$bronchitis->id])
        // A code matches from its start: "20" is not J20.9.
        ->and(getJson(route('api.diagnoses.search', ['term' => '20']))->json())->toBe([]);
});

it('leaves out the viewer\'s favourites, which the card already lists', function (): void {
    $favourite = Diagnosis::factory()->create(['code' => 'I10', 'name' => 'Эссенциальная гипертензия']);
    $other = Diagnosis::factory()->create(['code' => 'I11.9', 'name' => 'Гипертензивная болезнь сердца']);
    DB::table('admin_selected_diagnoses')->insert(['admin_id' => $this->doctor->id, 'diagnosis_id' => $favourite->id]);

    expect(getJson(route('api.diagnoses.search', ['term' => 'I1']))->json('*.id'))->toBe([$other->id]);

    // Another doctor's favourites are no business of this one.
    actingAs(adminWithPermissions('create_patient_diagnoses'), 'admin');

    expect(getJson(route('api.diagnoses.search', ['term' => 'I1']))->json('*.id'))->toBe([$favourite->id, $other->id]);
});

it('matches the wildcards of LIKE literally', function (): void {
    Diagnosis::factory()->create(['code' => 'R50.9', 'name' => 'Лихорадка неуточнённая']);

    expect(getJson(route('api.diagnoses.search', ['term' => '%']))->assertOk()->json())->toBe([])
        ->and(getJson(route('api.diagnoses.search', ['term' => '_']))->assertOk()->json())->toBe([]);
});

it('returns at most a page of diagnoses, and none before anything was typed', function (): void {
    foreach (range(1, DiagnosisPickerQuery::LIMIT + 5) as $number) {
        Diagnosis::factory()->create(['code' => sprintf('Z%02d.%d', intdiv($number, 10), $number % 10)]);
    }

    expect(getJson(route('api.diagnoses.search', ['term' => 'z']))->assertOk()->json())->toHaveCount(DiagnosisPickerQuery::LIMIT)
        ->and(getJson(route('api.diagnoses.search'))->assertOk()->json())->toBe([])
        ->and(getJson(route('api.diagnoses.search', ['term' => '   ']))->assertOk()->json())->toBe([]);
});

it('refuses an overlong search text', function (): void {
    getJson(route('api.diagnoses.search', ['term' => str_repeat('а', 101)]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('term');
});

it('is closed to accounts that may not add a diagnosis', function (): void {
    actingAs(adminWithPermissions('view_patients'), 'admin');

    getJson(route('api.diagnoses.search', ['term' => 'J']))->assertForbidden();
});
