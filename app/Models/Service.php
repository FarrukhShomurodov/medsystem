<?php

namespace App\Models;

use App\Queries\Billing\BillableItem;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property numeric-string $price decimal:2, as the cast returns it
 * @property string|null $currency
 */
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'code',
        'price',
        'discount',
        'currency',
        'service_type_id',
        'service_group_id',
        'is_active',
        'is_appointment',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_appointment' => 'boolean',
        ];
    }

    /**
     * The currency of the price as billing reads it: the code typed into the price list, or the
     * default currency when the entry names none.
     */
    public function priceCurrency(): string
    {
        $currency = Str::upper(trim($this->currency ?? ''));

        return $currency === '' ? BillableItem::DEFAULT_CURRENCY : $currency;
    }

    /**
     * @return BelongsTo<ServiceGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ServiceGroup::class, 'service_group_id');
    }

    /**
     * @return BelongsTo<ServiceType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}
