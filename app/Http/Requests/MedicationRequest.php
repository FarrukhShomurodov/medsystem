<?php

namespace App\Http\Requests;

use App\Models\MedicationPackaging;
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
            'release_form' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'country_of_manufacture' => 'required|string|max:255',
            'registration_number' => 'required|string|max:255',
            'registration_date' => 'required|date',
            'expiration_date' => 'required|date',

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

            //packaging
            'packaging_type' => ['required', Rule::in(MedicationPackaging::TYPE)],
            'quantity_per_package' => 'required|int',
            'package_size' => 'required|int',
            'batch_number' => 'required|string|max:255',
            'packaging_date' => 'required|date',

            //price
            'retail_price' => 'required|string|max:255',
            'wholesale_price' => 'required|string|max:255',
            'supplier_id' => 'required|string|max:255',
            'delivery_time' => 'required|string|max:255',

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
