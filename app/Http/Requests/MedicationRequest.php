<?php

namespace App\Http\Requests;

use App\Models\Medication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicationRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'release_form' => ['required', Rule::in(Medication::RELEASEFORM)],
            'dosage' => 'required|string|max:255',

            'manufacturer_id' => [
                'required',
                'int',
                'exists:manufacturers,id',
            ],

            'group_id' => 'required|int|exists:medication_groups,id',

            'registration_number' => 'required|string|max:255',
            'registration_date' => 'required|date',

            //composition
            'active_substance_name' => 'required|string|max:255',
            'active_substance_dosage' => 'required|string|max:255',

            'additional_substance' => 'required|array',
            'additional_substance.*.substance_name' => 'required|string|max:255',
            'additional_substance.*.substance_dosage' => 'required|string|max:255',

            //application
            'administration_method' => 'required|string|max:255',
            'dosage_regimen' => 'required|string|max:255',
            'drug_interactions' => 'required|string|max:255',

            //storage
            'storage_temperature' => 'required|int',
            'storage_conditions' => 'required|string|max:255',

            //license
            'sales_license' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'quality_certificate' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'clinical_trials' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'registration_documents' => 'nullable|file|mimes:pdf,jpg,png|max:2048',

            //data
            'manufacturing_license_number' => 'nullable|string|max:255',
            'additional_usage_conditions' => 'nullable|string|max:255',
            'prescription_category' => 'nullable|string|max:255',
            'specific_features' => 'nullable|string|max:255',
        ];
    }
}
