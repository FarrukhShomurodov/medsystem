<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\StaffPosition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Staff
    {
        return DB::transaction(function () use ($validated) {
            $staff = Staff::query()->create($validated);
            $validated['staff_id'] = $staff->id;

            // Создание связанных записей
            $staff->contact()->create($validated);
            $staff->document()->create($validated);
            $staff->professionalData()->create($validated);

            foreach ($validated['days'] as $day) {
                $dayId = $day['id'];

                foreach ($day['work'] ?? [] as $work) {
                    $staff->schedule()->create([
                        'day_of_week_id' => $dayId,
                        'start_time' => $work['start_time'],
                        'end_time' => $work['end_time'],
                        'type' => 'work',
                    ]);
                }

                foreach ($day['reception'] ?? [] as $reception) {
                    $staff->schedule()->create([
                        'day_of_week_id' => $dayId,
                        'start_time' => $reception['start_time'],
                        'end_time' => $reception['end_time'],
                        'type' => 'reception',
                    ]);
                }
            }


            // Синхронизация услуг
            if (!empty($validated['services'])) {
                $staff->services()->sync($validated['services']);
            }

            if (isset($validated['surveys'])) {
                $staff->surveys()->sync($validated['surveys']);
            }

            $authValidated['email'] = $validated['login_email'];
            $authValidated['password'] = $validated['password'];

            $this->staffAuth($staff, $authValidated, 'store', $validated['position_id']);

            return $staff;
        }, 5);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Staff $staff, array $validated)
    {
        return DB::transaction(function () use ($validated, $staff) {
            $staff->update($validated);
            $validated['staff_id'] = $staff->id;

            // Создание связанных записей
            $staff->contact()->update(
                array_intersect_key($validated, array_flip($staff->contact->getFillable()))
            );
            $staff->document()->update(
                array_intersect_key($validated, array_flip($staff->document->getFillable()))
            );
            $staff->professionalData()->update(
                array_intersect_key($validated, array_flip($staff->professionalData->getFillable()))
            );

            $staff->surveys()->sync($validated['surveys'] ?? []);

            $staff->schedule()->delete();

            foreach ($validated['days'] as $day) {
                $dayId = $day['id'];

                foreach ($day['work'] ?? [] as $work) {
                    $staff->schedule()->create([
                        'day_of_week_id' => $dayId,
                        'start_time' => $work['start_time'],
                        'end_time' => $work['end_time'],
                        'type' => 'work',
                    ]);
                }

                foreach ($day['reception'] ?? [] as $reception) {
                    $staff->schedule()->create([
                        'day_of_week_id' => $dayId,
                        'start_time' => $reception['start_time'],
                        'end_time' => $reception['end_time'],
                        'type' => 'reception',
                    ]);
                }
            }

            $staff->absence()->delete();

            if (isset($validated['absence'])) {
                foreach ($validated['absence'] as $absence) {
                    if ($absence['type'] == 'day') {
                        $staff->absence()->create([
                            'type' => $absence['type'],
                            'start_date' => $absence['date'],
                            'reason' => $absence['reason'],
                        ]);
                    } else {
                        if (strpos($absence['date'], 'to') !== false) {
                            list($startDate, $endDate) = explode(' to ', $absence['date']);
                            $staff->absence()->create([
                                'type' => $absence['type'],
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                                'reason' => $absence['reason'],
                            ]);
                        } else {
                            $staff->absence()->create([
                                'type' => $absence['type'],
                                'start_date' => $absence['date'],
                                'end_date' => $absence['date'],
                                'reason' => $absence['reason'],
                            ]);
                        }
                    }
                }
            }

            // Синхронизация услуг
            if (!empty($validated['services'])) {
                $staff->services()->sync($validated['services']);
            }

            if (isset($validated['dismissal_reception_date'])) {
                $staff->document->update(['is_work' => false]);
            }

            $authValidated['email'] = $validated['login_email'];
            $authValidated['password'] = isset($validated['password']) ? $validated['password'] : null;

            $this->staffAuth($staff, $authValidated, 'update', $validated['position_id']);

            return $staff;
        });
    }

    public function staffAuth($staff, $validated, $method, $positionId): void
    {
        $positionName = StaffPosition::find($positionId)->name;

        if ($method == 'update') {
            if (isset($validated['password']) && $validated['password'] !== '') {
                $validated['password'] = Hash::make((string)$validated['password']);
            } else {
                unset($validated['password']);
            }

            $staff->admin()->update($validated);
            $admin = $staff->admin;
        } else {
            $validated['password'] = isset($validated['password'])
                ? Hash::make((string)$validated['password'])
                : null;

            $admin = $staff->admin()->create($validated);
        }

        $role = Role::firstOrCreate(['name' => $positionName]);

        $admin->assignRole($role->name);
    }

    /**
     * @param Staff $staff
     * @return void
     */
    public
    function destroy(Staff $staff): void
    {
        $staff->delete();
    }
}
