<?php

use App\Exports\CashierListExport;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Reception;
use App\Models\Service;
use App\Queries\Billing\BillableItem;
use App\Queries\Billing\CashierFilter;
use App\Queries\Billing\CashierItemsQuery;
use App\Services\Billing\Amount;
use App\Services\Billing\BillableType;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;

use function Pest\Laravel\actingAs;

/*
| The cashier list ("Статистика кассира") and its Excel export: the same orders, the same filters.
*/

beforeEach(function (): void {
    $this->viewer = adminWithPermissions('view_cashier');

    $this->list = fn (array $filters = []) => actingAs($this->viewer, 'admin')->get(route('cashier.index', $filters));
    $this->listed = fn (array $filters = []) => ($this->list)($filters)->assertOk()->viewData('records')->getCollection()
        ->map(fn (BillableItem $item): string => $item->key())->all();
});

it('lists the orders of every kind that wait for payment, and only those', function (): void {
    $service = PatientService::factory()->confirmed()->create();
    $reception = Reception::factory()->confirmed()->create();
    $procedure = PatientProcedure::factory()->confirmed()->create();
    $medication = PatientMedication::factory()->sentToFloor()->create();
    PatientService::factory()->create(['status' => 'assigned']);
    PatientProcedure::factory()->create(['status' => 'paid']);
    PatientMedication::factory()->create(['status' => 'ordered']);

    expect(($this->listed)())->toEqualCanonicalizing([
        "service:{$service->id}",
        "reception:{$reception->id}",
        "procedure:{$procedure->id}",
        "medication:{$medication->id}",
    ]);
});

it('offers a checkbox for procedures too, to the cashier only', function (): void {
    $procedure = PatientProcedure::factory()->confirmed()->create();
    $checkbox = "value=\"procedure:{$procedure->id}\"";

    actingAs(adminWithPermissions('view_cashier', 'accept_payments'), 'admin')
        ->get(route('cashier.index'))
        ->assertOk()
        ->assertSeeHtml($checkbox);

    ($this->list)()->assertOk()->assertDontSeeHtml($checkbox);
});

it('lists the paid orders when filtered by the paid status', function (): void {
    $paidService = PatientService::factory()->create(['status' => 'paid']);
    $paidMedication = PatientMedication::factory()->create(['status' => 'received_at_station']);
    PatientService::factory()->confirmed()->create();
    PatientProcedure::factory()->confirmed()->create();

    expect(($this->listed)(['status' => 'paid']))->toEqualCanonicalizing([
        "service:{$paidService->id}",
        "medication:{$paidMedication->id}",
    ]);
});

it('filters every kind by its own date column, prescriptions by the day they were written', function (): void {
    $day = now()->subDays(3);
    $service = PatientService::factory()->confirmed()->create(['service_date' => $day->toDateString()]);
    $reception = Reception::factory()->confirmed()->create(['date' => $day->toDateString()]);
    $procedure = PatientProcedure::factory()->confirmed()->create(['procedure_date' => $day->toDateString()]);
    $medication = PatientMedication::factory()->sentToFloor()->create(['created_at' => $day->copy()->setTime(15, 30)]);
    PatientService::factory()->confirmed()->create();
    PatientMedication::factory()->sentToFloor()->create();

    expect(($this->listed)(['service_date' => $day->toDateString()]))->toEqualCanonicalizing([
        "service:{$service->id}",
        "reception:{$reception->id}",
        "procedure:{$procedure->id}",
        "medication:{$medication->id}",
    ]);
});

it('finds patients by first and last name regardless of case', function (): void {
    $patient = Patient::factory()->create(['first_name' => 'Dilnoza', 'last_name' => 'Karimova']);
    $service = PatientService::factory()->confirmed()->for($patient)->create();
    $medication = PatientMedication::factory()->sentToFloor()->for($patient)->create();
    PatientService::factory()->confirmed()->for(Patient::factory()->create(['first_name' => 'Aziz', 'last_name' => 'Karimov']))->create();

    $expected = ["service:{$service->id}", "medication:{$medication->id}"];

    expect(($this->listed)(['last_name' => 'karimova']))->toEqualCanonicalizing($expected)
        ->and(($this->listed)(['first_name' => 'DIL', 'last_name' => 'KARIM']))->toEqualCanonicalizing($expected);
});

it('finds Cyrillic names regardless of case', function (): void {
    $patient = Patient::factory()->create(['first_name' => 'Дилноза', 'last_name' => 'Каримова']);
    $service = PatientService::factory()->confirmed()->for($patient)->create();
    PatientService::factory()->confirmed()->for(Patient::factory()->create(['first_name' => 'Азиз', 'last_name' => 'Каримов']))->create();

    expect(($this->listed)(['last_name' => 'КАРИМОВА']))->toBe(["service:{$service->id}"])
        ->and(($this->listed)(['first_name' => 'дил', 'last_name' => 'кАрИм']))->toBe(["service:{$service->id}"]);
});

it('takes % and _ in a searched name literally', function (): void {
    PatientService::factory()->confirmed()->create();

    expect(($this->listed)(['last_name' => '%']))->toBe([])
        ->and(($this->listed)(['last_name' => '_']))->toBe([]);
});

it('sorts all kinds together by date, newest first, and paginates them in the database', function (): void {
    $oldest = PatientService::factory()->confirmed()->create(['service_date' => now()->subDays(20)->toDateString()]);
    PatientProcedure::factory()->confirmed()->count(14)->create(['procedure_date' => now()->subDays(5)->toDateString()]);
    $newest = Reception::factory()->confirmed()->create(['date' => now()->addDay()->toDateString()]);

    $firstPage = ($this->list)()->assertOk()->viewData('records');

    expect($firstPage->total())->toBe(16)
        ->and($firstPage->getCollection()->first()->key())->toBe("reception:{$newest->id}")
        ->and(($this->listed)(['page' => 2]))->toBe(["service:{$oldest->id}"]);
});

it('loads a page with the same number of queries however many orders it shows', function (): void {
    $queriesFor = function (): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        ($this->list)()->assertOk();
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    };

    $seed = function (): void {
        PatientService::factory()->confirmed()->create();
        Reception::factory()->confirmed()->create();
        PatientProcedure::factory()->confirmed()->create();
        PatientMedication::factory()->sentToFloor()->create();
    };

    // The first request also loads the permissions into their cache.
    ($this->list)()->assertOk();

    $seed();
    $withFour = $queriesFor();

    $seed();
    $seed();

    expect($queriesFor())->toBe($withFour);
});

it('shows the staff member, the price and the status of each order', function (): void {
    PatientService::factory()->confirmed()
        ->for(Service::factory()->state(['name' => 'УЗИ брюшной полости', 'price' => '1250000.50', 'currency' => 'usd']))
        ->create();
    $reception = Reception::factory()->confirmed()->create();

    ($this->list)()
        ->assertOk()
        ->assertSee('УЗИ брюшной полости')
        ->assertSee("1\u{00A0}250\u{00A0}000,50 USD")
        ->assertSee($reception->staff->full_name)
        ->assertSee(['Подтверждена', 'Подтверждён']);
});

it('sends invalid filters back to the unfiltered list with an error', function (array $filters): void {
    ($this->list)($filters)
        ->assertRedirect(route('cashier.index'))
        ->assertSessionHasErrors(array_keys($filters));
})->with([
    'not a date' => [['service_date' => 'вчера']],
    'unknown status' => [['status' => 'confirmed']],
    'a name with a control character' => [['last_name' => "Kar\x07imova"]],
]);

it('refuses a name that is not valid UTF-8 or carries a NUL byte', function (array $filters): void {
    // PostgreSQL refused the bytes with SQLSTATE 22021, a 500 for the cashier, and PDO cut the name
    // at the NUL byte and searched for "Dil" instead. RejectMalformedInput refuses both for every page.
    ($this->list)($filters)->assertBadRequest();
})->with([
    'a name that is not valid UTF-8' => [['last_name' => "\xFF\xFE"]],
    'a name with a NUL byte' => [['first_name' => "Dil\0noza"]],
]);

it('ignores a page number that is not a number', function (): void {
    ($this->list)(['page' => 'abc'])->assertOk();
});

it('exports the filtered list with a total per currency', function (): void {
    Excel::fake();

    $patient = Patient::factory()->create(['last_name' => 'Karimova']);
    $inSum = PatientService::factory()->confirmed()->for($patient)
        ->for(Service::factory()->state(['price' => '150000.00', 'currency' => 'UZS']))->create();
    $inDollars = Reception::factory()->confirmed()->for($patient)
        ->for(Service::factory()->appointment()->state(['price' => '20.50', 'currency' => 'USD']))->create();
    $medication = PatientMedication::factory()->sentToFloor()->for($patient)->create(['price' => '0.10']);
    PatientService::factory()->confirmed()->create();

    actingAs($this->viewer, 'admin')
        ->get(route('cashier.export', ['last_name' => 'karimova']))
        ->assertOk();

    Excel::assertDownloaded('cashier_stats.xlsx', function (CashierListExport $export) use ($inSum, $inDollars, $medication): true {
        $rows = iterator_to_array($export->generator(), false);

        // [type, id] of each order row, then one total row per currency.
        expect(array_map(fn (array $row): array => [$row[1], $row[0]], array_slice($rows, 0, 3)))->toEqualCanonicalizing([
            [BillableType::Service->label(), $inSum->id],
            [BillableType::Reception->label(), $inDollars->id],
            [BillableType::Medication->label(), $medication->id],
        ])
            ->and(array_map(fn (array $row): array => [$row[0], $row[6] => $row[5]], array_slice($rows, 3)))
            ->toEqualCanonicalizing([['ИТОГО:', 'UZS' => '150000.10'], ['ИТОГО:', 'USD' => '20.50']])
            ->and(array_map(fn (Amount $sum): string => $sum->toDecimal(), $export->totals->all()))
            ->toEqualCanonicalizing(['UZS' => '150000.10', 'USD' => '20.50']);

        return true;
    });
});

it('refuses an export of more orders than one file may hold, before it loads them', function (): void {
    // With no date, "Оплачено" exported every order ever paid: at about 60,000 of them the request
    // ran out of memory (512 MB) or time and the cashier got an error page instead of the file.
    config(['medsystem.cashier_export_max_rows' => 2]);
    PatientService::factory()->count(3)->create(['status' => 'paid']);
    $filter = ['status' => 'paid'];

    DB::flushQueryLog();
    DB::enableQueryLog();
    $response = actingAs($this->viewer, 'admin')->get(route('cashier.export', $filter));
    DB::disableQueryLog();

    $response->assertRedirect(route('cashier.index', $filter))
        ->assertSessionHas('error', 'Записей для выгрузки: 3, а в один файл помещается не больше 2. Сузьте выбор: укажите дату или пациента.');

    expect(collect(DB::getQueryLog())->pluck('query')->filter(fn (string $sql): bool => str_contains($sql, 'from "patient_services" where "patient_services"."id" in')))
        ->toBeEmpty();
});

it('exports as many orders as one file may hold', function (): void {
    config(['medsystem.cashier_export_max_rows' => 3]);
    PatientService::factory()->count(3)->create(['status' => 'paid']);

    actingAs($this->viewer, 'admin')->get(route('cashier.export', ['status' => 'paid']))
        ->assertOk()
        ->assertDownload('cashier_stats.xlsx');
});

it('reads the orders of an export a chunk at a time, in the order of the list', function (): void {
    PatientService::factory()->confirmed()->count(3)->create(['service_date' => now()->toDateString()]);
    PatientProcedure::factory()->confirmed()->count(2)->create(['procedure_date' => now()->toDateString()]);
    PatientMedication::factory()->sentToFloor()->count(2)->create();
    $query = app(CashierItemsQuery::class);
    $filter = new CashierFilter;

    DB::flushQueryLog();
    DB::enableQueryLog();
    $chunked = $query->lazy($filter, 2)->map(fn (BillableItem $item): string => $item->key())->all();
    DB::disableQueryLog();
    $keyQueries = collect(DB::getQueryLog())->filter(fn (array $entry): bool => str_contains($entry['query'], 'billable_items'));

    // Orders that share a moment are told apart by type and id, so no chunk repeats or skips one.
    expect($chunked)->toBe($query->paginate($filter, 100)->getCollection()->map(fn (BillableItem $item): string => $item->key())->all())
        ->and($chunked)->toHaveCount(7)
        ->and($keyQueries)->toHaveCount(4);
});

it('renders the export as a real spreadsheet', function (): void {
    PatientService::factory()->confirmed()
        ->for(Service::factory()->state(['name' => 'Рентген', 'price' => '99000.00', 'currency' => 'UZS']))
        ->create();

    $response = actingAs($this->viewer, 'admin')->get(route('cashier.export'))->assertOk()->assertDownload('cashier_stats.xlsx');

    $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();

    // Prices are numbers in the sheet, not text, so that Excel can add them up.
    expect($sheet->getCell('C2')->getValue())->toBe('Рентген')
        ->and($sheet->getCell('F2')->getDataType())->toBe(DataType::TYPE_NUMERIC)
        ->and($sheet->getCell('F2')->getValue())->toEqual(99000)
        ->and($sheet->getCell('A3')->getValue())->toBe('ИТОГО:')
        ->and($sheet->getCell('F3')->getDataType())->toBe(DataType::TYPE_NUMERIC)
        ->and($sheet->getCell('F3')->getValue())->toEqual(99000);
});

it('keeps accounts without the cashier permission away from the list and the export', function (): void {
    $stranger = adminWithPermissions('view_patients');

    actingAs($stranger, 'admin')->get(route('cashier.index'))->assertForbidden();
    actingAs($stranger, 'admin')->get(route('cashier.export'))->assertForbidden();
});
