<?php

use App\Models\HouseholdMaterial;
use App\Models\MainWarehouse;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MedicalSupply;
use App\Models\Medication;
use App\Models\WarehouseMedicalSupply;
use App\Queries\Stock\StockAlertsQuery;
use App\Services\Stock\StockLot;
use App\Services\Stock\StockSection;
use Database\Seeders\WarehouseSeeder;
use Tests\Feature\Frontend\RenderedPage;
use Tests\Feature\Stock\StockFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/*
| When stock runs low or near its expiry: the rules of the medication card ("Уведомить при остатке
| не больше", "Уведомить о сроке годности за ... дней"), and one fixed level for supplies and
| household materials. The receipts page's "Заканчивающиеся позиции", the remains pages' "мало" and
| "истекает" and the dashboard's stock card all apply them.
*/

beforeEach(function (): void {
    seed(WarehouseSeeder::class);
    actingAs(adminWithPermissions('edit_main_warehouse', 'main_warehouse_remain', 'view_warehouse_remains'), 'admin');
});

/**
 * The running-out list of the receipts page: name => units, in the order shown.
 *
 * @return array<string, int>
 */
function runningOut(): array
{
    $response = get(route('main.warehouse.receipts.goods.index'))->assertOk();

    return collect($response->viewData('remainMedications'))
        ->mapWithKeys(fn (array $line): array => [$line['medication']->name => $line['onHand']])
        ->all();
}

/** How many rows of a remains page carry a badge ("мало", "истекает", "просрочен"); the legend shows each once. */
function badgedRows(string $html, string $badge): int
{
    return substr_count($html, $badge === 'мало' ? 'me-1">мало</span>' : "ms-1\">{$badge}</span>");
}

describe('the running-out list of the receipts page', function (): void {
    it('counts a drug over all its batches, not one batch at a time', function (): void {
        // A used-up batch stayed in the list for good (no page can write it off) although the
        // drug was well stocked under a newer batch.
        $restocked = Medication::factory()->create(['name' => 'Глюкоза', 'notify_min_qty' => 20]);
        StockFixtures::mainBatch($restocked, 0, StockFixtures::monthsAhead(6));
        StockFixtures::mainBatch($restocked, 267, StockFixtures::monthsAhead(12));
        // Two small batches are one low drug, listed once with their sum.
        $low = Medication::factory()->create(['name' => 'Парацетамол', 'notify_min_qty' => 20]);
        StockFixtures::mainBatch($low, 5, StockFixtures::monthsAhead(6));
        StockFixtures::mainBatch($low, 5, StockFixtures::monthsAhead(8));

        expect(runningOut())->toBe(['Парацетамол' => 10]);
        get(route('main.warehouse.receipts.goods.index'))->assertSeeHtml('<span class="badge bg-warning-lt ms-2">1</span>');
    });

    it('counts no expired units, and lists a drug with nothing left in date', function (): void {
        $expired = Medication::factory()->create(['name' => 'Амоксициллин', 'notify_min_qty' => 20]);
        StockFixtures::mainBatch($expired, 300, today()->subDay()->toDateString());
        $usedUp = Medication::factory()->create(['name' => 'Ибупрофен', 'notify_min_qty' => 20]);
        StockFixtures::mainBatch($usedUp, 0, StockFixtures::monthsAhead(6));

        expect(runningOut())->toBe(['Амоксициллин' => 0, 'Ибупрофен' => 0]);
    });

    it('applies the threshold of each drug, at or below it', function (): void {
        // The list used a fixed 20 while the remains page used the drug's own threshold.
        $wide = Medication::factory()->create(['name' => 'Анальгин', 'notify_min_qty' => 100]);
        StockFixtures::mainBatch($wide, 50, StockFixtures::monthsAhead(6));
        $narrow = Medication::factory()->create(['name' => 'Но-шпа', 'notify_min_qty' => 5]);
        StockFixtures::mainBatch($narrow, 10, StockFixtures::monthsAhead(6));
        // "Уведомить при остатке не больше 30": 30 units left is low.
        $atThreshold = Medication::factory()->create(['name' => 'Цитрамон', 'notify_min_qty' => 30]);
        StockFixtures::mainBatch($atThreshold, 15, StockFixtures::monthsAhead(6));
        StockFixtures::mainBatch($atThreshold, 15, StockFixtures::monthsAhead(9));

        expect(runningOut())->toBe(['Анальгин' => 50, 'Цитрамон' => 30]);
    });

    it('lists supplies and household materials at or below the fixed level', function (): void {
        $supply = fn (int $quantity) => MainWarehouseMedicalSupply::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => MedicalSupply::factory()->create()->id, 'quantity' => $quantity]);
        $material = fn (int $quantity) => MainWarehouseHouseholdMaterial::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'household_material_id' => HouseholdMaterial::factory()->create()->id, 'quantity' => $quantity]);
        $lowSupply = $supply(StockLot::LOW_STOCK);
        $supply(StockLot::LOW_STOCK + 1);
        $lowMaterial = $material(0);
        $material(StockLot::LOW_STOCK + 1);

        $response = get(route('main.warehouse.receipts.goods.index'))->assertOk();

        expect($response->viewData('remainSupplies')->modelKeys())->toBe([$lowSupply->id])
            ->and($response->viewData('remainHousehold')->modelKeys())->toBe([$lowMaterial->id]);
    });

    it('counts as out of stock on the dashboard the drugs it lists with nothing left', function (): void {
        $expired = Medication::factory()->create(['name' => 'Амоксициллин', 'notify_min_qty' => 20]);
        StockFixtures::mainBatch($expired, 300, today()->subDay()->toDateString());
        $usedUp = Medication::factory()->create(['name' => 'Ибупрофен', 'notify_min_qty' => 20]);
        StockFixtures::mainBatch($usedUp, 0, StockFixtures::monthsAhead(6));
        $low = Medication::factory()->create(['name' => 'Парацетамол', 'notify_min_qty' => 20]);
        StockFixtures::mainBatch($low, 3, StockFixtures::monthsAhead(6));

        $empty = array_keys(array_filter(runningOut(), fn (int $units): bool => $units === 0));

        expect($empty)->toBe(['Амоксициллин', 'Ибупрофен'])
            ->and(app(StockAlertsQuery::class)->outOfStockInMainWarehouse(today()))->toBe(count($empty));
    });
});

describe('the remains pages', function (): void {
    it('marks a drug "мало" by its units in date across its batches, at or below its threshold', function (): void {
        // Two batches of 60 with a threshold of 100: each batch was marked low, with 120 on hand.
        $stocked = Medication::factory()->create(['notify_min_qty' => 100, 'notify_before_days' => 30]);
        StockFixtures::mainBatch($stocked, 60, StockFixtures::monthsAhead(6));
        StockFixtures::mainBatch($stocked, 60, StockFixtures::monthsAhead(8));
        // Exactly at the threshold: the form promises a warning at "не больше".
        $atThreshold = Medication::factory()->create(['notify_min_qty' => 40, 'notify_before_days' => 30]);
        StockFixtures::mainBatch($atThreshold, 40, StockFixtures::monthsAhead(6));
        $ward = StockFixtures::departmentWarehouse();
        StockFixtures::departmentBatch($ward, $stocked, 60, StockFixtures::monthsAhead(6));
        StockFixtures::departmentBatch($ward, $stocked, 60, StockFixtures::monthsAhead(8));
        StockFixtures::departmentBatch($ward, $atThreshold, 40, StockFixtures::monthsAhead(6));

        expect(badgedRows((string) get(route('main.warehouse.remains'))->assertOk()->getContent(), 'мало'))->toBe(1)
            ->and(badgedRows((string) get(route('warehouse.remains'))->assertOk()->getContent(), 'мало'))->toBe(1);
    });

    it('marks exactly the batches the dashboard counts as near their expiry, the expired ones apart', function (): void {
        $medication = Medication::factory()->create(['notify_before_days' => 30, 'notify_min_qty' => 0]);
        $ward = StockFixtures::departmentWarehouse();

        foreach ([-1, 0, 1, 29, 30, 31, 60] as $days) {
            StockFixtures::mainBatch($medication, 10, today()->addDays($days)->toDateString());
            StockFixtures::departmentBatch($ward, $medication, 10, today()->addDays($days)->toDateString());
        }

        $alerts = app(StockAlertsQuery::class);
        $main = (string) get(route('main.warehouse.remains'))->getContent();
        $wards = (string) get(route('warehouse.remains'))->getContent();

        // From the expiry date minus notify_before_days on: 5 of 7, of which 1 is past its date.
        expect(badgedRows($main, 'истекает') + badgedRows($main, 'просрочен'))
            ->toBe($alerts->expiryInMainWarehouse(today())->due)
            ->toBe(5)
            ->and(badgedRows($main, 'просрочен'))->toBe($alerts->expiryInMainWarehouse(today())->expired)->toBe(1)
            ->and(badgedRows($wards, 'истекает') + badgedRows($wards, 'просрочен'))->toBe($alerts->expiryInWardWarehouses(today())->due)
            ->and(badgedRows($wards, 'просрочен'))->toBe($alerts->expiryInWardWarehouses(today())->expired);
    });

    it('marks a batch past its expiry date "просрочен", apart from those that expire soon', function (string $page): void {
        // Both were marked "истекает": the page the dashboard's "из них просрочено" links to did not
        // show which batch had expired and waited to be written off.
        $medication = Medication::factory()->create(['notify_before_days' => 30, 'notify_min_qty' => 0]);
        $ward = StockFixtures::departmentWarehouse();

        // Yesterday's batch has expired; today's is still issued (unexpired() keeps it), like next week's.
        foreach ([-1, 0, 7] as $days) {
            StockFixtures::mainBatch($medication, 10, today()->addDays($days)->toDateString());
            StockFixtures::departmentBatch($ward, $medication, 10, today()->addDays($days)->toDateString());
        }

        $html = (string) get(route($page))->assertOk()->getContent();

        expect(badgedRows($html, 'просрочен'))->toBe(1)
            ->and(badgedRows($html, 'истекает'))->toBe(2)
            ->and(RenderedPage::tableRows($html, '//main//table'))
            ->sequence(
                fn ($row) => $row->{'Срок годности'}->toBe(today()->subDay()->format('d.m.Y').' просрочен'),
                fn ($row) => $row->{'Срок годности'}->toBe(today()->format('d.m.Y').' истекает'),
                fn ($row) => $row->{'Срок годности'}->toBe(today()->addDays(7)->format('d.m.Y').' истекает'),
            );
    })->with(['main.warehouse.remains', 'warehouse.remains']);

    it('marks supplies and household materials "мало" at or below the fixed level', function (): void {
        MainWarehouseMedicalSupply::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => MedicalSupply::factory()->create()->id, 'quantity' => StockLot::LOW_STOCK]);
        MainWarehouseMedicalSupply::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => MedicalSupply::factory()->create()->id, 'quantity' => StockLot::LOW_STOCK + 1]);
        MainWarehouseHouseholdMaterial::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'household_material_id' => HouseholdMaterial::factory()->create()->id, 'quantity' => 1]);

        expect(badgedRows((string) get(route('main.warehouse.remains'))->getContent(), 'мало'))->toBe(2);
    });
});

describe('the section filter of the stock pages', function (): void {
    beforeEach(function (): void {
        $medication = Medication::factory()->create(['name' => 'Гепарин']);
        $supply = MedicalSupply::factory()->create(['name' => 'Бинт']);
        StockFixtures::mainBatch($medication, 50, StockFixtures::monthsAhead(6));
        MainWarehouseMedicalSupply::query()->create(['main_warehouse_id' => MainWarehouse::MAIN, 'supply_id' => $supply->id, 'quantity' => 5]);
        $ward = StockFixtures::departmentWarehouse();
        StockFixtures::departmentBatch($ward, $medication, 50, StockFixtures::monthsAhead(6));
        WarehouseMedicalSupply::query()->create(['warehouse_id' => $ward->id, 'supply_id' => $supply->id, 'quantity' => 5]);
    });

    it('lists the chosen section, or every section when none is chosen, on both remains pages', function (string $page): void {
        get(route($page))->assertOk()->assertSeeText(['Гепарин', 'Бинт']);

        get(route($page, ['type' => StockSection::Supplies->value]))
            ->assertOk()
            ->assertViewHas('type', StockSection::Supplies)
            ->assertSeeText('Бинт')
            ->assertDontSeeText('Гепарин');
    })->with(['main.warehouse.remains', 'warehouse.remains']);

    it('sends an unknown or malformed section back to the unfiltered page on every stock page', function (string $page, mixed $type): void {
        // The department page took any ?type= and listed nothing; the main one fell back to every section.
        get(route($page, ['type' => $type]))
            ->assertRedirect(route($page))
            ->assertSessionHasErrors('type');
    })->with(['main.warehouse.remains', 'warehouse.remains', 'main.warehouse.receipts.goods.index'])
        ->with(['an unknown section' => 'devices', 'an array' => [['medications']]]);
});
