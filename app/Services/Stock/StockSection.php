<?php

namespace App\Services\Stock;

use App\Enums\Concerns\HasValues;
use App\Enums\Contracts\HasLabel;
use App\Models\MainWarehouseHouseholdMaterial;
use App\Models\MainWarehouseMedicalSupply;
use App\Models\MainWarehouseMedication;

/**
 * The kinds of stock a warehouse keeps, each in tables of its own: the sections of the remains
 * pages (?type=), of a receipt and of the write-off route. Never stored.
 */
enum StockSection: string implements HasLabel
{
    use HasValues;

    case Medications = 'medications';
    case Supplies = 'supplies';
    case Household = 'household';

    public function label(): string
    {
        return match ($this) {
            self::Medications => 'Медикаменты',
            self::Supplies => 'Мед. принадлежности',
            self::Household => 'Хоз. материалы',
        };
    }

    /**
     * The stock rows of the section in the main warehouse: a medication is kept in batches, a
     * supply or household material in one row per item.
     *
     * @return class-string<MainWarehouseMedication|MainWarehouseMedicalSupply|MainWarehouseHouseholdMaterial>
     */
    public function mainWarehouseRow(): string
    {
        return match ($this) {
            self::Medications => MainWarehouseMedication::class,
            self::Supplies => MainWarehouseMedicalSupply::class,
            self::Household => MainWarehouseHouseholdMaterial::class,
        };
    }

    /** Whether the section is listed under the chosen one: every section is when none is chosen. */
    public function isShownFor(?self $chosen): bool
    {
        return $chosen === null || $chosen === $this;
    }
}
