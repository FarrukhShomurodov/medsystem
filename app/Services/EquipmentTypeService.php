<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminSelectedDiagnosis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Admin
    {
        return DB::transaction(function () use ($validated) {
            $validated['password'] = isset($validated['password'])
                ? Hash::make((string)$validated['password'])
                : null;

            return Admin::query()->create($validated);
        });
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Admin $admin, array $validated): Admin
    {
        return DB::transaction(function () use ($admin, $validated) {
            if (isset($validated['password']) && $validated['password'] !== '') {
                $validated['password'] = Hash::make((string)$validated['password']);
            } else {
                unset($validated['password']);
            }

            $admin->update($validated);

            return $admin->refresh();
        });
    }

    public function destroy(Admin $admin): void
    {
        DB::transaction(function () use ($admin) {
            $admin->delete();
        });
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function selectedDiagnosis(array $validated)
    {
        $hasDiagnosis = AdminSelectedDiagnosis::query()->where('admin_id', $validated['admin_id'])->where(
            'diagnosis_id',
            $validated['diagnosis_id']
        );
        if ($hasDiagnosis->exists()) {
            return $hasDiagnosis->delete();
        } else {
            return AdminSelectedDiagnosis::query()->create($validated);
        }
    }
}
