<?php

namespace App\Services\Clinical\Scoring;

use App\Enums\Gender;
use App\Models\Patient;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * What a scale needs to know about the patient besides the answers on the sheet:
 * the Humpty Dumpty sheet scores age and sex from the patient record instead of asking.
 */
final readonly class PatientDemographics
{
    public function __construct(
        public int $ageInYears,
        public bool $isMale,
    ) {}

    public static function of(Patient $patient, CarbonInterface $assessedAt): self
    {
        // Carbon 3 returns a signed float: the full years lived on the day of the assessment.
        $age = (int) CarbonImmutable::parse($patient->birth_date)->diffInYears($assessedAt);

        return new self(max(0, $age), $patient->gender === Gender::Male);
    }
}
