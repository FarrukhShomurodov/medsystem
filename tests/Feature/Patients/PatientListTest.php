<?php

use App\Models\Department;
use App\Models\ExaminationMap;
use App\Models\Patient;
use App\Models\Staff;
use Database\Seeders\StaffPositionSeeder;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Frontend\RenderedPage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    actingAs(adminWithPermissions('list_patients'), 'admin');
});

/**
 * The rows of the rendered patient list, by patient id: each cell's text under its column header.
 *
 * @param  array<string, mixed>  $filters
 * @return array<int, array<string, string>>
 */
function patientListRows(array $filters = []): array
{
    $html = (string) test()->get(route('patients.index', $filters))->assertOk()->getContent();

    return collect(RenderedPage::tableRows($html, '//main//table'))->keyBy(fn (array $row): int => (int) $row['#'])->all();
}

/**
 * Ids of the patients the list shows for the given filters.
 *
 * @param  array<string, mixed>  $filters
 * @return list<int>
 */
function listedPatientIds(array $filters): array
{
    return test()->get(route('patients.index', $filters))
        ->assertOk()
        ->viewData('patients')
        ->pluck('id')
        ->sort()
        ->values()
        ->all();
}

it('finds a patient by id', function (): void {
    // The filter was "id ILIKE '%…%'", which PostgreSQL rejects for a bigint column: a 500.
    $wanted = Patient::factory()->create();
    Patient::factory()->create();

    expect(listedPatientIds(['id' => $wanted->id]))->toBe([$wanted->id]);
});

it('rejects an id filter that is not a number', function (): void {
    test()->from(route('patients.index'))
        ->get(route('patients.index', ['id' => 'abc']))
        ->assertRedirect(route('patients.index'))
        ->assertSessionHasErrors('id');
});

it('matches names partially and regardless of case', function (): void {
    // The name filters were ILIKE without wildcards: only the exact full name matched.
    $ivanov = Patient::factory()->create(['first_name' => 'Иван', 'last_name' => 'Иванов', 'middle_name' => 'Петрович']);
    Patient::factory()->create(['first_name' => 'Пётр', 'last_name' => 'Сидоров', 'middle_name' => 'Ильич']);

    expect(listedPatientIds(['first_name' => 'ива']))->toBe([$ivanov->id])
        ->and(listedPatientIds(['second_name' => 'ИВАН']))->toBe([$ivanov->id])
        ->and(listedPatientIds(['middle_name' => 'етро']))->toBe([$ivanov->id]);
});

it('matches the percent sign and the underscore literally', function (): void {
    Patient::factory()->create(['first_name' => 'Анна']);

    expect(listedPatientIds(['first_name' => '%']))->toBe([])
        ->and(listedPatientIds(['first_name' => '_']))->toBe([]);
});

it('finds a name holding a literal %, _ or backslash, and only that name', function (): void {
    $underscore = Patient::factory()->create(['last_name' => 'Ан_на']);
    $percent = Patient::factory()->create(['last_name' => 'Ли 100%']);
    $backslash = Patient::factory()->create(['last_name' => 'Ким\\Ли']);
    Patient::factory()->create(['last_name' => 'Ан-на']);
    Patient::factory()->create(['last_name' => 'Ли 1000']);

    // As wildcards "н_н" and "100%" would also match the other two; a lone backslash, the escape
    // character of LIKE, would end the pattern with an escape and fail on PostgreSQL.
    expect(listedPatientIds(['second_name' => 'Н_Н']))->toBe([$underscore->id])
        ->and(listedPatientIds(['second_name' => '100%']))->toBe([$percent->id])
        ->and(listedPatientIds(['second_name' => '\\']))->toBe([$backslash->id]);
});

it('filters by the exact date of birth', function (): void {
    // The filter was "birth_date >= x": everyone born after the date.
    $born = Patient::factory()->create(['birth_date' => '1990-05-17']);
    Patient::factory()->create(['birth_date' => '1995-01-01']);
    Patient::factory()->create(['birth_date' => '1980-01-01']);

    expect(listedPatientIds(['birth_date' => '1990-05-17']))->toBe([$born->id]);
});

it('filters by the department of the open stay', function (): void {
    $cardiology = Department::factory()->create();
    $inCardiology = ExaminationMap::factory()->create(['department_id' => $cardiology->id])->patient_id;
    ExaminationMap::factory()->discharged()->create(['department_id' => $cardiology->id]);
    ExaminationMap::factory()->create();

    expect(listedPatientIds(['department_id' => $cardiology->id]))->toBe([$inCardiology]);
});

it('shows the latest stay of each patient without a query per row', function (): void {
    $queries = function (int $patients): int {
        ExaminationMap::factory()->count($patients)->create();
        DB::flushQueryLog();
        DB::enableQueryLog();
        test()->get(route('patients.index'))->assertOk();

        return count(DB::getQueryLog());
    };

    // The first request also loads the permission cache.
    $queries(0);

    expect($queries(6))->toBe($queries(2));
});

it('forbids the list without list_patients', function (): void {
    actingAs(adminWithPermissions('view_patients'), 'admin')
        ->get(route('patients.index'))
        ->assertForbidden();
});

it('sends a refused filter back to the unfiltered list, also when it is submitted twice', function (): void {
    // The refusal went back to the previous URL, which after the second submit was the refused
    // filter itself: the browser looped on redirects.
    $refused = route('patients.index', ['id' => 'abc']);

    test()->get($refused)->assertRedirect(route('patients.index'));
    test()->get($refused)->assertRedirect(route('patients.index'))->assertSessionHasErrors('id');
});

it('shows the admission date of the stay, not the day the patient was registered', function (): void {
    // The column printed the patient's created_at, a registration timestamp with seconds, under
    // "Дата поступления": a stay that began days ago read as admitted today, and a patient without
    // a stay had an admission date as well.
    $inpatient = Patient::factory()->create(['created_at' => now()->subYear(), 'birth_date' => '1984-03-09']);
    ExaminationMap::factory()->for($inpatient)->create(['admission_date' => today()->subDays(6)->toDateString()]);
    $outpatient = Patient::factory()->create();

    $rows = patientListRows();

    expect($rows[$inpatient->id]['Дата поступления'])->toBe(today()->subDays(6)->format('d.m.Y'))
        ->and($rows[$inpatient->id]['Дата рождения / возраст'])->toStartWith('09.03.1984 / ')
        ->and($rows[$inpatient->id]['Дней в стационаре'])->toBe('6')
        ->and($rows[$outpatient->id]['Дата поступления'])->toBe('—');
});

it('names the attending doctor in full in the rows and in the doctor filter', function (): void {
    // Only the first name was printed: two doctors called Арсений could not be told apart.
    seed(StaffPositionSeeder::class);
    $sokolov = Staff::factory()->doctor()->create(['last_name' => 'Соколов', 'name' => 'Арсений', 'middle_name' => 'Петрович']);
    Staff::factory()->doctor()->create(['last_name' => 'Максимов', 'name' => 'Арсений', 'middle_name' => 'Иванович']);
    $stay = ExaminationMap::factory()->create(['staff_id' => $sokolov->id]);

    expect(patientListRows()[$stay->patient_id]['Лечащий врач'])->toBe('Соколов Арсений Петрович');

    $page = (string) test()->get(route('patients.index'))->getContent();

    expect(RenderedPage::selectOptions($page, 'filter-staff'))->toBe(['Все', 'Максимов Арсений Иванович', 'Соколов Арсений Петрович']);
});

it('has no columns that nothing in the system fills in', function (): void {
    // "Палата" and "Дата и название операции" were a dash in every row: no stay records a bed or
    // an operation.
    ExaminationMap::factory()->create();

    expect(array_keys(collect(patientListRows())->first()))
        ->not->toContain('Палата')
        ->not->toContain('Дата и название операции');
});
