<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class SupplyRequestItem extends Model
{
    protected $fillable = [
        'request_id',
        'supplyable_id',
        'supplyable_type',
        'requested_quantity',
        'approved_quantity',
    ];

    protected function casts(): array
    {
        return [
            'requested_quantity' => 'integer',
            'approved_quantity' => 'integer',
        ];
    }

    /** @return BelongsTo<SupplyRequest, $this> */
    public function request(): BelongsTo
    {
        return $this->belongsTo(SupplyRequest::class, 'request_id');
    }

    /**
     * A medication, a medical supply or a household material, by the warehouse type.
     *
     * @return MorphTo<Model, $this>
     */
    public function supplyable(): MorphTo
    {
        return $this->morphTo();
    }

    /** What kind of item was ordered, in the words of the orders page. */
    public function kindLabel(): string
    {
        return match (Relation::getMorphedModel($this->supplyable_type)) {
            Medication::class => 'Медикамент',
            MedicalSupply::class => 'Расходник',
            HouseholdMaterial::class => 'Хоз. товар',
            default => 'Неизвестно',
        };
    }
}
