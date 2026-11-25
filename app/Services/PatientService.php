<?php

namespace App\Services;

use App\Exceptions\Patients\DeletionRefusedException;
use App\Models\City;
use App\Models\File;
use App\Models\Patient;
use App\Models\Region;
use App\Services\Patients\RetainedRecords;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PatientService
{
    /**
     * The address selects accept either an existing record or a newly typed name (select2 tags):
     * PatientRequest moves a typed name to its own field, so the id field only ever holds a
     * checked id. Id field => name field.
     */
    public const ADDRESS_LOOKUPS = [
        'region_id' => 'region_name',
        'city_id' => 'city_name',
        'registered_region_id' => 'registered_region_name',
        'registered_city_id' => 'registered_city_name',
    ];

    /** The residential and the registration address: region field => city field. */
    private const array ADDRESSES = [
        'region_id' => 'city_id',
        'registered_region_id' => 'registered_city_id',
    ];

    public function __construct(private readonly RetainedRecords $retainedRecords) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Patient
    {
        return DB::transaction(function () use ($validated): Patient {
            $patient = Patient::query()->create($this->attributes($validated));
            $this->storeFiles($patient, $validated['files'] ?? []);

            return $patient;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Patient $patient, array $validated): Patient
    {
        DB::transaction(function () use ($patient, $validated): void {
            $patient->update($this->attributes($validated));
            $this->storeFiles($patient, $validated['files'] ?? []);
        });

        return $patient->refresh();
    }

    /**
     * Deletes the patient with the attached documents; the files leave the disk only once the
     * rows are gone, so a refused delete keeps them.
     *
     * @throws DeletionRefusedException when the patient holds paid orders, dispensed drugs or closed stays
     */
    public function destroy(Patient $patient): void
    {
        $files = $patient->files()->get();

        DB::transaction(function () use ($patient): void {
            $this->retainedRecords->ensurePatientDeletable($patient);
            $patient->files()->delete();
            $patient->delete();
        });

        Storage::disk(File::DISK)->delete($files->pluck('url')->all());
    }

    /**
     * The patient columns of the validated form, with newly typed regions and cities replaced by
     * the ids of the records they name (created on first use).
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function attributes(array $validated): array
    {
        foreach (self::ADDRESSES as $regionField => $cityField) {
            if (array_key_exists($regionField, $validated)) {
                $validated[$regionField] = $this->regionId($validated, $regionField);
            }

            if (array_key_exists($cityField, $validated)) {
                $validated[$cityField] = $this->cityId($validated, $cityField, $validated[$regionField] ?? null);
            }
        }

        return Arr::except($validated, [...array_values(self::ADDRESS_LOOKUPS), 'files']);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function regionId(array $validated, string $field): ?int
    {
        $name = $validated[self::ADDRESS_LOOKUPS[$field]] ?? null;

        if ($name === null) {
            return isset($validated[$field]) ? (int) $validated[$field] : null;
        }

        return Region::query()->firstOrCreate(['name' => $name])->id;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function cityId(array $validated, string $field, ?int $regionId): ?int
    {
        $name = $validated[self::ADDRESS_LOOKUPS[$field]] ?? null;

        if ($name === null) {
            return isset($validated[$field]) ? (int) $validated[$field] : null;
        }

        return City::query()->firstOrCreate(['name' => $name, 'region_id' => $regionId])->id;
    }

    /**
     * Patient documents are medical data: they go to the private disk and are served by PatientFileController.
     *
     * @param  array<int, UploadedFile>  $files
     */
    private function storeFiles(Patient $patient, array $files): void
    {
        foreach ($files as $file) {
            $patient->files()->create([
                'name' => $file->getClientOriginalName(),
                'url' => $file->store('patient_files', File::DISK),
            ]);
        }
    }
}
