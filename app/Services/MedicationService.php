<?php

namespace App\Services;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MedicationService
{
    private const array LICENSE_FILES = ['sales_license', 'quality_certificate', 'clinical_trials', 'registration_documents'];

    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Medication
    {
        return DB::transaction(function () use ($validated): Medication {
            $medication = Medication::query()->create($validated);

            $validated['medication_id'] = $medication->id;

            $medication->application()->create($validated);
            $medication->additionalData()->create($validated);

            $composition = $medication->composition()->create($validated);
            $composition->additionalSubstance()->createMany($validated['additional_substance']);

            $medication->storage()->create($validated);

            foreach (self::LICENSE_FILES as $fileField) {
                if (($validated[$fileField] ?? null) instanceof UploadedFile) {
                    $validated[$fileField] = $validated[$fileField]->store('medical_licenses', 'public');
                }
            }

            $medication->licenseAndCertification()->create($validated);

            return $medication;
        });
    }

    /**
     * Saves every section of the medication form. A section the medication had no row for yet
     * (an imported or seeded medication) is created.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Medication $medication, array $validated): Medication
    {
        return DB::transaction(function () use ($validated, $medication): Medication {
            $medication->update($validated);

            $this->saveSection($medication->application(), $validated);
            $this->saveSection($medication->additionalData(), $validated);
            $this->saveSection($medication->storage(), $validated);

            $composition = $this->saveSection($medication->composition(), $validated);
            $composition->additionalSubstance()->delete();
            $composition->additionalSubstance()->createMany($validated['additional_substance']);

            $license = $medication->licenseAndCertification()->firstOrNew();

            foreach (self::LICENSE_FILES as $fileField) {
                if (($validated[$fileField] ?? null) instanceof UploadedFile) {
                    $replaced = $license->$fileField;
                    $validated[$fileField] = $validated[$fileField]->store('medical_licenses', 'public');

                    // A rolled-back update still points at the old file, so it may go only after the commit.
                    if ($replaced) {
                        DB::afterCommit(fn () => Storage::disk('public')->delete($replaced));
                    }
                }
            }

            $this->saveSection($medication->licenseAndCertification(), $validated);

            return $medication;
        });
    }

    public function destroy(Medication $medication): void
    {
        $medication->delete();
    }

    /**
     * @template TSection of Model
     *
     * @param  HasOne<TSection, Medication>  $section
     * @param  array<string, mixed>  $validated
     * @return TSection
     */
    private function saveSection(HasOne $section, array $validated): Model
    {
        $fillable = $section->getRelated()->getFillable();

        return $section->updateOrCreate([], array_intersect_key($validated, array_flip($fillable)));
    }
}
