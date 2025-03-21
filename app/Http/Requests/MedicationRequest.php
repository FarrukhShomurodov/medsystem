<?php

namespace App\Http\Requests;

use App\Models\Medication;
use Illuminate\Database\Query\Builder;
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
//                Rule::exists('supplier_manufacturers')->where(function (Builder $query) {
//                    $query->where('supplier_id', $this->input('supplier_id'))
//                        ->where('manufacturer_id', $this->input('manufacturer_id'));
//                }),
            ],

            'registration_number' => 'required|string|max:255',
            'registration_date' => 'required|date',

            //composition
            'active_substance_name' => 'required|string|max:255',
            'active_substance_dosage' => 'required|string|max:255',
            'substance_name' => 'required|string|max:255',
            'substance_dosage' => 'required|string|max:255',
            'pharmacological_group' => 'required|string|max:255',

            //application
            'indications' => 'required|string|max:255',
            'administration_method' => 'required|string|max:255',
            'dosage_regimen' => 'required|string|max:255',
            'contraindications' => 'required|string|max:255',
            'usage_features' => 'required|string|max:255',
            'side_effects' => 'required|string|max:255',
            'drug_interactions' => 'required|string|max:255',

            //storage
            'storage_temperature' => 'required|int',
            'storage_conditions' => 'required|string|max:255',
            'special_storage_conditions' => 'required|string|max:255',

            //license
            'sales_license' => 'required|string|max:255',
            'quality_certificate' => 'required|string|max:255',
            'clinical_trials' => 'required|string|max:255',
            'registration_documents' => 'required|string|max:255',

            //data
            'manufacturing_license_number' => 'required|string|max:255',
            'additional_usage_conditions' => 'required|string|max:255',
            'prescription_category' => 'required|string|max:255',
            'specific_features' => 'required|string|max:255',

            //risk
            'risk_assessment' => 'required|string|max:255',
            'warnings' => 'required|string|max:255',
            'safety_in_use' => 'required|string|max:255',

        ];
    }
}
