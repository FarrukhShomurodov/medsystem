<?php

namespace App\Services;

use App\Enums\AbsenceType;
use App\Enums\ScheduleType;
use App\Exceptions\Scheduling\AccountException;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\StaffContact;
use App\Models\StaffDocument;
use App\Models\StaffPosition;
use App\Models\StaffProfessionalData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Registers employees with their contacts, documents, professional data, schedule and the login
 * account that holds the role of their position.
 */
class StaffService
{
    private const string DISK = 'public';

    private const array FILES = ['avatar_url', 'sign_url'];

    public function __construct(
        private readonly StaffPositionService $positions,
        private readonly AdminService $accounts,
    ) {}

    /**
     * @param  array<string, mixed>  $validated  StaffRequest data
     */
    public function store(array $validated): Staff
    {
        $files = $this->storeUploads($validated);

        try {
            return DB::transaction(function () use ($validated, $files): Staff {
                $staff = Staff::query()->create([...Arr::except($validated, self::FILES), ...$files]);

                $staff->contact()->create([...$this->only(StaffContact::class, $validated), 'email' => $validated['login_email']]);
                $staff->document()->create($this->documentAttributes($validated));
                $staff->professionalData()->create($this->only(StaffProfessionalData::class, $validated));

                $this->syncRelations($staff, $validated);

                return $staff;
            });
        } catch (Throwable $e) {
            Storage::disk(self::DISK)->delete(array_values($files));

            throw $e;
        }
    }

    /**
     * Replaced photos and signatures are deleted once the change is committed. A dismissal date
     * dismisses the employee, which also ends their login (Admin::isActive()).
     *
     * @param  array<string, mixed>  $validated  StaffRequest data
     *
     * @throws AccountException when it would dismiss the last main administrator who can sign in
     */
    public function update(Staff $staff, array $validated): Staff
    {
        $files = $this->storeUploads($validated);
        $replaced = array_filter(Arr::only($staff->getAttributes(), array_keys($files)));

        try {
            DB::transaction(function () use ($staff, $validated, $files): void {
                // As a booking does (ReceptionService::book()): a booking waits for the new hours and
                // absences, so it is either refused by them or listed by uncoveredAppointmentsOf().
                $this->lock($staff);

                if ($this->dismisses($validated) && $staff->admin !== null && $this->accounts->isLastMainAdmin($staff->admin)) {
                    throw AccountException::lastMainAdminDismissed();
                }

                $staff->update([...Arr::except($validated, self::FILES), ...$files]);

                $staff->contact()->updateOrCreate([], $this->only(StaffContact::class, $validated));
                $staff->document()->updateOrCreate([], $this->documentAttributes($validated));
                $staff->professionalData()->updateOrCreate([], $this->only(StaffProfessionalData::class, $validated));

                $this->syncRelations($staff, $validated);
            });
        } catch (Throwable $e) {
            Storage::disk(self::DISK)->delete(array_values($files));

            throw $e;
        }

        Storage::disk(self::DISK)->delete(array_values($replaced));

        return $staff->refresh();
    }

    /**
     * The employee's login account goes with them, under the same rules as on the admins screen.
     * A record other data still points to (receptions, examination maps) is protected by
     * ON DELETE RESTRICT and stays, account included.
     *
     * @throws AccountException
     */
    public function destroy(Staff $staff, Admin $actor): void
    {
        $files = array_filter(Arr::only($staff->getAttributes(), self::FILES));

        DB::transaction(function () use ($staff, $actor): void {
            // The staff row before the accounts' rows, in the order update() takes them.
            $this->lock($staff);

            if ($staff->admin !== null) {
                $this->accounts->ensureDeletable($staff->admin, $actor);
                $staff->admin->delete();
            }

            $staff->delete();
        });

        Storage::disk(self::DISK)->delete(array_values($files));
    }

    private function lock(Staff $staff): void
    {
        Staff::query()->whereKey($staff->getKey())->lockForUpdate()->first();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncRelations(Staff $staff, array $validated): void
    {
        $staff->services()->sync($validated['services'] ?? []);
        $staff->surveys()->sync($validated['surveys'] ?? []);

        $this->replaceSchedule($staff, $validated['days'] ?? []);
        $this->replaceAbsences($staff, $validated['absence'] ?? []);
        $this->syncLoginAccount($staff, $validated);
    }

    /**
     * @param  list<array{id: int|string, work?: list<array{start_time: string, end_time: string}>, reception?: list<array{start_time: string, end_time: string}>}>  $days
     */
    private function replaceSchedule(Staff $staff, array $days): void
    {
        $staff->schedule()->delete();

        foreach ($days as $day) {
            foreach (ScheduleType::cases() as $type) {
                foreach ($day[$type->value] ?? [] as $hours) {
                    $staff->schedule()->create([
                        'day_of_week_id' => $day['id'],
                        'type' => $type,
                        'start_time' => $hours['start_time'],
                        'end_time' => $hours['end_time'],
                    ]);
                }
            }
        }
    }

    /**
     * A one-day absence has no end date; a period picked as a single day ends that day.
     *
     * @param  list<array{type: string, start_date: string, end_date?: string|null, reason: string}>  $absences
     */
    private function replaceAbsences(Staff $staff, array $absences): void
    {
        $staff->absence()->delete();

        foreach ($absences as $absence) {
            $type = AbsenceType::from($absence['type']);

            $staff->absence()->create([
                'type' => $type,
                'reason' => $absence['reason'],
                'start_date' => $absence['start_date'],
                'end_date' => $type === AbsenceType::Period ? ($absence['end_date'] ?? $absence['start_date']) : null,
            ]);
        }
    }

    /**
     * The login holds exactly one position role, the one of the current position: a position
     * change revokes the previous one. System roles given outside the staff structure (main_admin)
     * stay. Any other role is a former position's, including roles left on accounts by renames
     * before positions were linked to roles by id, so it is revoked too.
     *
     * @param  array<string, mixed>  $validated
     */
    private function syncLoginAccount(Staff $staff, array $validated): void
    {
        $role = $this->positions->roleOf(StaffPosition::query()->findOrFail((int) $validated['position_id']));

        $admin = $staff->admin ?? new Admin(['staff_id' => $staff->id]);
        $admin->email = $validated['login_email'];

        if (filled($validated['password'] ?? null)) {
            $admin->password = $validated['password'];
        }

        $admin->save();

        $systemRoleIds = $admin->roles->whereIn('name', StaffPosition::RESERVED_ROLE_NAMES)->modelKeys();

        $admin->syncRoles([...$systemRoleIds, $role->getKey()]);

        $staff->setRelation('admin', $admin);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, string> column => stored path
     *
     * @throws RuntimeException when the disk refuses a file; the files stored before it are deleted
     */
    private function storeUploads(array $validated): array
    {
        $paths = [];

        foreach (self::FILES as $column) {
            if (($validated[$column] ?? null) instanceof UploadedFile) {
                $path = $validated[$column]->store('staff_photo', self::DISK);

                if ($path === false) {
                    Storage::disk(self::DISK)->delete(array_values($paths));

                    throw new RuntimeException("The upload of {$column} could not be stored.");
                }

                $paths[$column] = $path;
            }
        }

        return $paths;
    }

    /**
     * The employment documents of the form. A dismissal date dismisses the employee, also when a
     * former employee is registered with one: their login must not work either.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function documentAttributes(array $validated): array
    {
        return [
            ...$this->only(StaffDocument::class, $validated),
            ...($this->dismisses($validated) ? ['is_work' => false] : []),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function dismisses(array $validated): bool
    {
        return isset($validated['dismissal_reception_date']);
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function only(string $model, array $validated): array
    {
        return Arr::except(Arr::only($validated, (new $model)->getFillable()), ['staff_id']);
    }
}
