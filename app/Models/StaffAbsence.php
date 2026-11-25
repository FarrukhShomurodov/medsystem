<?php

namespace App\Models;

use App\Enums\AbsenceReason;
use App\Enums\AbsenceType;
use Carbon\CarbonInterface;
use Database\Factories\StaffAbsenceFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property AbsenceType $type
 * @property AbsenceReason $reason
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 */
class StaffAbsence extends Model
{
    /** @use HasFactory<StaffAbsenceFactory> */
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => AbsenceType::class,
            'reason' => AbsenceReason::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Absences that include the date. A one-day absence has no end date.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function covering(Builder $query, CarbonInterface $date): void
    {
        $this->overlapping($query, $date, $date);
    }

    /**
     * Absences that share at least one day with the period.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function overlapping(Builder $query, CarbonInterface $from, CarbonInterface $to): void
    {
        $query->whereDate('start_date', '<=', $to)
            ->where(fn (Builder $end) => $end->whereDate('end_date', '>=', $from)
                ->orWhere(fn (Builder $oneDay) => $oneDay->whereNull('end_date')->whereDate('start_date', '>=', $from)));
    }

    /**
     * Whether the absence includes the date.
     */
    public function covers(CarbonInterface $date): bool
    {
        $day = $date->toDateString();

        return $this->start_date->toDateString() <= $day
            && ($this->end_date ?? $this->start_date)->toDateString() >= $day;
    }
}
