<?php

use App\Models\Patient;
use App\Models\PatientInspection;

use function Pest\Laravel\actingAs;

/**
 * @param  array<string, mixed>  $fields
 * @return array<string, mixed>
 */
function inspectionForm(Patient $patient, array $fields = []): array
{
    return [
        'patient_id' => $patient->id,
        'bp' => '120/80',
        'pulse' => '72',
        'saturation' => '98',
        'temperature' => '36.6',
        'chdd' => '16',
        'consciousness_level' => 'A',
        'oxygen_status' => 'Воздух',
        ...$fields,
    ];
}

it('records an inspection of the patient', function (): void {
    $patient = Patient::factory()->create();

    actingAs(adminWithPermissions('patient_inspection_store'), 'admin')
        ->post(route('patient.inspection.store'), inspectionForm($patient))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(PatientInspection::query()->where('patient_id', $patient->id)->value('bp'))->toBe('120/80');
});

it('rejects a reading longer than its column instead of failing on it', function (): void {
    // The columns are varchar(255) and the rule had no max: PostgreSQL refused the insert, a 500.
    actingAs(adminWithPermissions('patient_inspection_store'), 'admin')
        ->post(route('patient.inspection.store'), inspectionForm(Patient::factory()->create(), ['bp' => str_repeat('1', 256)]))
        ->assertSessionHasErrors('bp');

    expect(PatientInspection::query()->count())->toBe(0);
});

it('forbids recording an inspection without patient_inspection_store', function (): void {
    actingAs(adminWithPermissions('view_patients'), 'admin')
        ->post(route('patient.inspection.store'), inspectionForm(Patient::factory()->create()))
        ->assertForbidden();

    expect(PatientInspection::query()->count())->toBe(0);
});
