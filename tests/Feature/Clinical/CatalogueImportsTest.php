<?php

use App\Imports\DangerousCombinationImport;
use App\Imports\DiagnosisImport;
use App\Imports\ServicesImport;
use App\Models\DangerousCombination;
use App\Models\Diagnosis;
use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\ServiceType;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Clinical\Support\Spreadsheet;

use function Pest\Laravel\actingAs;

/*
| The Excel imports of the services price list, the ICD-10 diagnoses and the drug interactions,
| with files laid out like the ones users upload.
*/

function servicesSheet(array ...$rows): array
{
    return [
        ['Наименование', 'Описание', 'Код', 'Стоимость', 'Скидка', 'Валюта', 'Тип ID', 'Группа ID', 'Активна', 'По записи'],
        ...$rows,
    ];
}

/** The ministry list: the code moves right with its depth, "-" marks the tree expander. */
function icdSheet(array ...$rows): array
{
    return [['Код', '', '', '', '', '', 'Название', 'Примечание'], ...$rows];
}

function interactionsSheet(array ...$rows): array
{
    return [
        ['№', 'Drug1', 'Препарат 1', 'Drug2', 'Препарат 2', 'Severity', 'Тяжесть', 'Interaction/Comment', 'Взаимодействие/Комментарии'],
        ...$rows,
    ];
}

describe('every import', function (): void {
    it('reads only the columns of its layout, however wide the sheet is used', function (Closure $import, Closure $sheet): void {
        $rows = Excel::toCollection($import(), Spreadsheet::upload($sheet(), usedUpTo: 'XFD'))->first();

        // Without the column limit every row came with all 16 384 cells of the sheet width, and the
        // real interaction list (2452 rows, used up to XFD) ran out of memory before it was imported.
        expect($rows)->toHaveCount(1)
            ->and($rows->first())->not->toHaveKey('formatting')
            ->and($rows->first()->count())->toBeLessThan(30);
    })->with([
        'services' => [fn (): ServicesImport => new ServicesImport, fn (): array => servicesSheet(['ЭКГ', null, null, 80000, null, 'UZS', 1, 1, 1, 0])],
        'diagnoses' => [fn (): DiagnosisImport => new DiagnosisImport, fn (): array => icdSheet(['', '', '-', 'A00', '', '', 'Холера', ''])],
        'interactions' => [fn (): DangerousCombinationImport => new DangerousCombinationImport, fn (): array => interactionsSheet([1, 'isoflurane', 'Изофлуран', 'dronedarone', 'дронедарон', 'contraindicated', 'противопоказано', '', ''])],
    ]);

    it('imports a sheet used across the whole width', function (): void {
        actingAs(adminWithPermissions('create_medications'), 'admin');

        $this->post(route('dangerous.combination.import'), ['file' => Spreadsheet::upload(interactionsSheet(
            [1, 'isoflurane', 'Изофлуран', 'dronedarone', 'дронедарон', 'contraindicated', 'противопоказано', '', ''],
        ), usedUpTo: 'XFD')])->assertSessionHas('success', 'Импортировано строк: 1.');

        expect(DangerousCombination::query()->value('drug2_ru'))->toBe('дронедарон');
    });
});

describe('services price list', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('create_services'), 'admin');
    });

    it('imports every valid row and reports the skipped ones with their reasons', function (): void {
        $type = ServiceType::factory()->create();
        $group = ServiceGroup::factory()->create();

        $file = Spreadsheet::upload(servicesSheet(
            ['Консультация кардиолога', 'Первичный приём', 'K-01', 150000, 10, 'UZS', $type->id, $group->id, 1, 1],
            ['ЭКГ', null, 2001, 80000, null, 'UZS', $type->id, $group->id, 'да', 'нет'],
            ['УЗИ сердца', null, null, 200000, null, 'UZS', $type->id, 999_999, 1, 0],
            [null, null, null, 50000, null, 'UZS', $type->id, $group->id, 1, 0],
        ));

        // The old import read the undefined key "group_id" and crashed on the first valid row.
        $this->post(route('services.import'), ['file' => $file])
            ->assertRedirect(route('services.index'))
            ->assertSessionHas('success', 'Импортировано строк: 2.')
            ->assertSessionHas('warning', fn (string $warning): bool => str_contains($warning, 'Пропущено строк: 2.')
                && str_contains($warning, 'строка 4: Выбранное значение для «Группа ID» некорректно.')
                && str_contains($warning, 'строка 5: Поле «Наименование» обязательно для заполнения.'));

        expect(Service::query()->orderBy('id')->get(['name', 'code', 'price', 'discount', 'service_type_id', 'service_group_id', 'is_active', 'is_appointment'])->toArray())
            ->toBe([
                ['name' => 'Консультация кардиолога', 'code' => 'K-01', 'price' => '150000.00', 'discount' => 10, 'service_type_id' => $type->id, 'service_group_id' => $group->id, 'is_active' => true, 'is_appointment' => true],
                ['name' => 'ЭКГ', 'code' => '2001', 'price' => '80000.00', 'discount' => null, 'service_type_id' => $type->id, 'service_group_id' => $group->id, 'is_active' => true, 'is_appointment' => false],
            ]);
    });

    it('checks that the service type exists, not that the id is numeric', function (): void {
        $group = ServiceGroup::factory()->create();
        ServiceType::factory()->create();

        // where('id', is_numeric(...)) compared the id with true, so any number passed as type 1.
        $this->post(route('services.import'), ['file' => Spreadsheet::upload(servicesSheet(
            ['Рентген', null, null, 90000, null, 'UZS', 999_999, $group->id, 1, 0],
        ))])->assertSessionHas('warning', fn (string $warning): bool => str_contains($warning, '«Тип ID»'));

        expect(Service::query()->count())->toBe(0);
    });

    it('rejects a file that is not a spreadsheet', function (): void {
        $this->post(route('services.import'), ['file' => UploadedFile::fake()->create('services.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('file');
    });

    it('is closed to accounts that cannot create services', function (): void {
        actingAs(adminWithPermissions('list_services'), 'admin');

        $this->get(route('services.import.show'))->assertForbidden();
        $this->post(route('services.import'), ['file' => Spreadsheet::upload(servicesSheet())])->assertForbidden();
    });
});

describe('ICD-10 diagnoses', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('create_diagnoses'), 'admin');
    });

    it('takes the code from the column of its depth and skips rows without a code', function (): void {
        $file = Spreadsheet::upload(icdSheet(
            ['-', 'A', '', '', '', '', 'Класс I. Некоторые инфекционные и паразитарные болезни', ''],
            ['', '-', 'A00-A09', '', '', '', 'Кишечные инфекции', ''],
            ['', '', '-', 'A00', '', '', 'Холера', ''],
            ['', '', '', '', 'A00.0', '', 'Холера, вызванная холерным вибрионом 01', ''],
            ['', '', '', '', '', '', 'Строка без кода', ''],
        ));

        // The old import stored "-" for every non-leaf row and crashed on the row without a code.
        $this->post(route('diagnoses.import'), ['file' => $file])
            ->assertRedirect(route('diagnoses.index'))
            ->assertSessionHas('success', 'Импортировано строк: 4.')
            ->assertSessionHas('warning', fn (string $warning): bool => str_contains($warning, 'строка 6: Поле «Код» обязательно для заполнения.'));

        expect(Diagnosis::query()->orderBy('id')->pluck('name', 'code')->all())->toBe([
            'A' => 'Класс I. Некоторые инфекционные и паразитарные болезни',
            'A00-A09' => 'Кишечные инфекции',
            'A00' => 'Холера',
            'A00.0' => 'Холера, вызванная холерным вибрионом 01',
        ]);
    });

    it('updates the names of codes already in the catalogue instead of adding them again', function (): void {
        $cholera = Diagnosis::factory()->create(['code' => 'A00', 'name' => 'Холера (старое название)']);

        $this->post(route('diagnoses.import'), ['file' => Spreadsheet::upload(icdSheet(
            ['', '', '-', 'A00', '', '', 'Холера', ''],
            ['', '', '', '', 'A00.9', '', 'Холера неуточненная', ''],
            ['', '', '', '', 'A00.9', '', 'Холера неуточненная (уточнено)', ''],
        ))])->assertSessionHas('success', 'Импортировано строк: 3.');

        expect(Diagnosis::query()->count())->toBe(2)
            ->and($cholera->fresh()->name)->toBe('Холера')
            ->and(Diagnosis::query()->where('code', 'A00.9')->value('name'))->toBe('Холера неуточненная (уточнено)');
    });

    it('is closed to accounts that cannot create diagnoses', function (): void {
        actingAs(adminWithPermissions('list_diagnoses'), 'admin');

        $this->post(route('diagnoses.import'), ['file' => Spreadsheet::upload(icdSheet())])->assertForbidden();
    });
});

describe('drug interactions', function (): void {
    beforeEach(function (): void {
        actingAs(adminWithPermissions('create_medications'), 'admin');
    });

    it('imports the first data row and maps the Russian severity', function (): void {
        $file = Spreadsheet::upload(interactionsSheet(
            [1, 'isoflurane', 'Изофлуран', 'dronedarone', 'дронедарон  ', 'contraindicated', 'противопоказано', 'both increase QTc', 'Оба увеличивают интервал QTc'],
            [2, 'isoflurane', 'Изофлуран', 'thioridazine', 'тиоридазин', 'contraindicated', 'противопоказано', 'both increase QTc', 'Оба увеличивают интервал QTc'],
            [3, 'isoflurane', 'Изофлуран', 'alfuzosin', '', 'serious', 'Тяжёлое', '', ''],
        ));

        // forget(0) dropped the first pair: WithHeadingRow had already consumed the headings.
        $this->post(route('dangerous.combination.import'), ['file' => $file])
            ->assertRedirect(route('medications.index'))
            ->assertSessionHas('success', 'Импортировано строк: 2.')
            ->assertSessionHas('warning', fn (string $warning): bool => str_contains($warning, 'строка 4: Поле «Препарат 2» обязательно для заполнения.'));

        expect(DangerousCombination::query()->orderBy('id')->first()->only(['drug1_ru', 'drug2_ru', 'severity_en', 'severity_ru', 'mechanism_ru', 'comment_ru']))
            ->toBe([
                'drug1_ru' => 'Изофлуран',
                'drug2_ru' => 'дронедарон',
                'severity_en' => 'contraindicated',
                'severity_ru' => 'противопоказано',
                'mechanism_ru' => null,
                'comment_ru' => 'Оба увеличивают интервал QTc',
            ]);
    });

    it('does not add a row already in the list again when the list is imported again', function (): void {
        $row = [1, 'isoflurane', 'Изофлуран', 'dronedarone', 'дронедарон', 'contraindicated', 'противопоказано', 'both increase QTc', 'Оба увеличивают интервал QTc'];

        $this->post(route('dangerous.combination.import'), ['file' => Spreadsheet::upload(interactionsSheet($row))]);
        $this->post(route('dangerous.combination.import'), ['file' => Spreadsheet::upload(interactionsSheet($row))])
            ->assertSessionHas('success', 'Импортировано строк: 1.');

        expect(DangerousCombination::query()->count())->toBe(1);
    });

    it('keeps every severity the list gives a pair instead of merging them into the last one', function (): void {
        // The real list names carbamazepine + irinotecan twice: contraindicated, then serious.
        // Merging the pair kept only the milder warning.
        $this->post(route('dangerous.combination.import'), ['file' => Spreadsheet::upload(interactionsSheet(
            [826, 'Carbamazepine', 'карбамазепин', 'irinotecan', 'иринотекан', 'contraindicated', 'Противопоказано', 'decreases irinotecan levels', 'Снижает уровень иринотекана'],
            [827, 'Carbamazepine', 'карбамазепин', 'irinotecan', 'иринотекан', 'serious', 'Тяжёлое', 'decreases SN-38 levels', 'Снижает уровень SN-38'],
        ))])->assertSessionHas('success', 'Импортировано строк: 2.');

        expect(DangerousCombination::query()->where('drug2_ru', 'иринотекан')->orderBy('id')->pluck('severity_ru')->all())
            ->toBe(['Противопоказано', 'Тяжёлое']);
    });

    it('adds a row the file names twice once, and keeps rows that differ only in which field is empty', function (): void {
        $row = [1, 'warfarin', 'варфарин', 'aspirin', 'аспирин', 'serious', 'Тяжёлое', 'bleeding', ''];
        $sheet = interactionsSheet(
            $row,
            $row,
            [3, 'warfarin', 'варфарин', 'aspirin', 'аспирин', 'serious', 'Тяжёлое', '', 'bleeding'],
        );

        $this->post(route('dangerous.combination.import'), ['file' => Spreadsheet::upload($sheet)])
            ->assertSessionHas('success', 'Импортировано строк: 3.');
        $this->post(route('dangerous.combination.import'), ['file' => Spreadsheet::upload($sheet)])
            ->assertSessionHas('success', 'Импортировано строк: 3.');

        expect(DangerousCombination::query()->orderBy('id')->get(['comment_en', 'comment_ru'])->toArray())->toBe([
            ['comment_en' => 'bleeding', 'comment_ru' => null],
            ['comment_en' => null, 'comment_ru' => 'bleeding'],
        ]);
    });

    it('imports the list with a fixed number of queries, however long it is', function (): void {
        // One firstOrCreate per row scanned the whole unindexed table each time: the real list
        // (about 2 450 rows) spent most of its import time in the database, and twice the list
        // ran past the web server's 60-second timeout.
        DangerousCombination::factory()->create(['drug1_ru' => 'препарат 1', 'drug2_ru' => 'варфарин', 'severity_en' => 'serious']);
        $rows = array_map(
            fn (int $n): array => [$n, "drug {$n}", "препарат {$n}", 'warfarin', 'варфарин', 'serious', 'Тяжёлое', '', ''],
            range(1, 60),
        );
        $file = Spreadsheet::upload(interactionsSheet(...$rows));
        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        Excel::import($import = new DangerousCombinationImport, $file);

        expect($queries)->toBeLessThanOrEqual(3)
            ->and($import->report()->imported)->toBe(60)
            ->and(DangerousCombination::query()->count())->toBe(61);
    });

    it('lists the imported pairs fifty to a page', function (): void {
        // The page rendered the whole list: about 1.8 MB for the real one, growing with every import.
        DangerousCombination::factory()->count(50)->create();
        $last = DangerousCombination::factory()->create(['drug1_ru' => 'Последний препарат']);

        $this->get(route('dangerous.combination.import.show'))
            ->assertOk()
            ->assertSee('Загруженные комбинации (51)')
            ->assertDontSee('Последний препарат')
            ->assertSee(route('dangerous.combination.import.show', ['page' => 2]), escape: false);

        $this->get(route('dangerous.combination.import.show', ['page' => 2]))
            ->assertOk()
            ->assertSee('Последний препарат')
            ->assertViewHas('combinations', fn (LengthAwarePaginator $combinations): bool => $combinations->count() === 1 && $combinations->first()->is($last));
    });

    it('is closed to accounts that cannot create medications', function (): void {
        actingAs(adminWithPermissions('list_medications'), 'admin');

        $this->get(route('dangerous.combination.import.show'))->assertForbidden();
        $this->post(route('dangerous.combination.import'), ['file' => Spreadsheet::upload(interactionsSheet())])->assertForbidden();
    });
});
