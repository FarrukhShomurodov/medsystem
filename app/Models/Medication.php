<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\MedicationFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $notify_before_days days before a batch expires that its warning is due
 * @property int $notify_min_qty units in date at or below which the drug runs low
 */
class Medication extends Model
{
    /** @use HasFactory<MedicationFactory> */
    use HasFactory;

    const RELEASEFORM = [
        'Таблетка',
        'Ампула',
        'Жидкость',
        'Сироп',
        'Капсула',
        'Порошок',
        'Гранулы',
        'Экстракт',
        'Пастилка',
        'Спрей',
        'Раствор',
        'Свечи ректальные',
        'Мазь',
        'Гель',
        'Пакетик',
        'Крем',
        'Капли',
        'Масло',
        'Глазные капли',
        'Свечи вагинальные',
        'Ушные капли',
        'Суспензия',
        'Пластырь',
        'Настойка',
        'Паста',
        'Флакон',
        'Эластичный бинт',
        'Шприц',
        'Вата',
        'Саше',
        'Рулон',
        'Бинт',
        'Система',
        'Бальзам',
    ];

    protected $fillable = [
        'name',
        'release_form',
        'dosage',
        'manufacturer_id',
        'group_id',
        'registration_number',
        'registration_date',

        'min_deduction',
        'min_quantity',
        'notify_before_days',
        'notify_min_qty',
    ];

    protected function casts(): array
    {
        return [
            'notify_before_days' => 'integer',
            'notify_min_qty' => 'integer',
        ];
    }

    /**
     * "Уведомить при остатке не больше": the drug runs low in a place once the units it has there in
     * date (StockAlertsQuery::medicationsOnHand*()) are at or below its threshold. The receipts
     * page lists such drugs and the remains pages mark their batches "мало".
     */
    public function isLowStock(int $unitsOnHand): bool
    {
        return $unitsOnHand <= $this->notify_min_qty;
    }

    /**
     * "Уведомить о сроке годности за ... дней": the warning of a batch is due from notify_before_days
     * before its expiry date on, expired batches included. The remains pages mark such batches
     * "истекает", or "просрочен" once past the date (isExpired() of the batch);
     * StockAlertsQuery::expiry() counts the same rows in SQL for the dashboard.
     */
    public function expiryWarningDue(CarbonInterface $expiresOn, CarbonInterface $today): bool
    {
        // Days, not moments: SQL compares the date column with today's date.
        return $expiresOn->toImmutable()->startOfDay()->lte($today->toImmutable()->startOfDay()->addDays($this->notify_before_days));
    }

    /**
     * @return BelongsTo<Manufacturer, $this>
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * @return BelongsTo<MedicationGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(MedicationGroup::class);
    }

    /*
     * The detail sections below are optional: a medication imported or created before they existed
     * has no row for them. An empty default model keeps every page and JSON reading them safe.
     */

    /** @return HasOne<MedicationApplication, $this> */
    public function application(): HasOne
    {
        return $this->hasOne(MedicationApplication::class)->withDefault();
    }

    /** @return HasOne<MedicationAdditionalData, $this> */
    public function additionalData(): HasOne
    {
        return $this->hasOne(MedicationAdditionalData::class)->withDefault();
    }

    /** @return HasOne<MedicationComposition, $this> */
    public function composition(): HasOne
    {
        return $this->hasOne(MedicationComposition::class)->withDefault();
    }

    /** @return HasOne<MedicationStorage, $this> */
    public function storage(): HasOne
    {
        return $this->hasOne(MedicationStorage::class)->withDefault();
    }

    /** @return HasOne<MedicationLicenseAndCertification, $this> */
    public function licenseAndCertification(): HasOne
    {
        return $this->hasOne(MedicationLicenseAndCertification::class)->withDefault();
    }

    /**
     * @return BelongsToMany<MainWarehouse, $this, Pivot>
     */
    public function mainWarehouses(): BelongsToMany
    {
        return $this->belongsToMany(MainWarehouse::class, 'main_warehouse_medications', 'medication_id', 'main_warehouse_id');
    }

    /** @return HasMany<MainWarehouseMedication, $this> */
    public function mainWarehouseBatches(): HasMany
    {
        return $this->hasMany(MainWarehouseMedication::class);
    }

    /**
     * Medications with units in date left in the main warehouse: what a doctor can prescribe and
     * a department can order.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function inMainWarehouseStock(Builder $query): void
    {
        $query->whereHas('mainWarehouseBatches', fn (Builder $batches) => $batches
            ->where('main_warehouse_id', MainWarehouse::MAIN)
            ->where('quantity', '>', 0)
            ->unexpired());
    }
}
