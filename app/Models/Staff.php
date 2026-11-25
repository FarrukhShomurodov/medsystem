<?php

namespace App\Models;

use App\Enums\Gender;
use App\Services\Scheduling\AppointmentCalendar;
use Carbon\CarbonInterface;
use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property Gender $gender
 */
class Staff extends Model
{
    /** @use HasFactory<StaffFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'last_name',
        'middle_name',
        'date_birthday',
        'gender',
        'citizenship',
        'avatar_url',
        'sign_url',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
        ];
    }

    /**
     * "Last First Middle", the order staff names are shown in across the UI.
     *
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->last_name} {$this->name} {$this->middle_name}"));
    }

    /**
     * @return HasOne<StaffContact, $this>
     */
    public function contact(): HasOne
    {
        return $this->hasOne(StaffContact::class);
    }

    /**
     * @return HasOne<StaffDocument, $this>
     */
    public function document(): HasOne
    {
        return $this->hasOne(StaffDocument::class);
    }

    /**
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'staff_service_assignment', 'staff_id', 'staff_service_id');
    }

    /**
     * @return BelongsToMany<Survey, $this>
     */
    public function surveys(): BelongsToMany
    {
        return $this->belongsToMany(Survey::class, 'staff_surveys', 'staff_id', 'survey_id');
    }

    /**
     * @return HasOne<StaffProfessionalData, $this>
     */
    public function professionalData(): HasOne
    {
        return $this->hasOne(StaffProfessionalData::class);
    }

    /**
     * @return HasMany<StaffSchedule, $this>
     */
    public function schedule(): HasMany
    {
        return $this->hasMany(StaffSchedule::class);
    }

    /**
     * @return HasMany<StaffAbsence, $this>
     */
    public function absence(): HasMany
    {
        return $this->hasMany(StaffAbsence::class);
    }

    /**
     * @return HasMany<Reception, $this>
     */
    public function receptions(): HasMany
    {
        return $this->hasMany(Reception::class);
    }

    /**
     * The login account of the employee, if they have one.
     *
     * @return HasOne<Admin, $this>
     */
    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Staff not dismissed: the exact complement of dismissed(), so staff registered without
     * employment documents count as employed, as they do at sign-in (Admin::active()).
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function employed(Builder $query): void
    {
        $query->whereDoesntHave('document', fn (Builder $document) => $document->where('is_work', false));
    }

    /**
     * Staff whose dismissal is recorded on their employment documents (is_work = false); see isDismissed().
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function dismissed(Builder $query): void
    {
        $query->whereRelation('document', 'is_work', false);
    }

    /**
     * Employed staff with working or reception hours on the weekday of the date who are not absent that day.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function workingOn(Builder $query, CarbonInterface $date): void
    {
        $query->employed()
            ->whereRelation('schedule', 'day_of_week_id', DayOfWeek::idOf($date))
            ->whereDoesntHave('absence', /** @param Builder<StaffAbsence> $absence */ fn (Builder $absence) => $absence->covering($date));
    }

    /**
     * Staff whose main position is the given one (StaffPosition::DOCTOR, ...); an additional
     * position does not count.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function inPosition(Builder $query, int $positionId): void
    {
        $query->whereRelation('professionalData', 'position_id', $positionId);
    }

    /** Whether the employee works on the day by their schedule (workingOn()). */
    public function isWorkingOn(CarbonInterface $date): bool
    {
        return static::query()->whereKey($this->getKey())->workingOn($date)->exists();
    }

    /**
     * Dismissal is recorded on the employment documents; staff registered without them count as
     * employed. The one test of employment for a single employee, matching dismissed() and employed().
     */
    public function isDismissed(): bool
    {
        return $this->document()->where('is_work', false)->exists();
    }

    /**
     * Length of one appointment in minutes, or null when the employee takes no appointments.
     */
    public function slotMinutes(): ?int
    {
        $minutes = $this->professionalData?->admission_period;

        return $minutes > 0 ? (int) $minutes : null;
    }

    /**
     * The appointment grid of the day, see AppointmentCalendar::availability().
     *
     * @return list<array{time: string, is_available: bool, admission_period: int}>
     */
    public function availabilityOn(CarbonInterface $date): array
    {
        return AppointmentCalendar::of($this, $date, $date)->availability($date);
    }

    /**
     * The booked slots of the day, see AppointmentCalendar::bookings().
     *
     * @return list<array{time: string, admission_period: int, is_booked: bool, reception_id: int|null}>
     */
    public function bookingsOn(CarbonInterface $date): array
    {
        return AppointmentCalendar::of($this, $date, $date)->bookings($date);
    }
}
