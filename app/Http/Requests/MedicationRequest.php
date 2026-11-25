<?php

namespace App\Http\Requests;

use App\Models\Medication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
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

            'min_deduction' => 'required|int',
            'min_quantity' => 'required|int',
            'notify_before_days' => 'required|int',
            'notify_min_qty' => 'required|int',

            // composition
            'active_substance_name' => 'required|string|max:255',
            'active_substance_dosage' => 'required|string|max:255',

            'additional_substance' => 'required|array',
            'additional_substance.*.substance_name' => 'required|string|max:255',
            'additional_substance.*.substance_dosage' => 'required|string|max:255',

            // application
            'administration_method' => 'required|string|max:255',
            'dosage_regimen' => 'required|string|max:255',
            'drug_interactions' => 'required|string|max:255',

            // storage
            'storage_temperature' => 'required|int',
            'storage_conditions' => 'required|string|max:255',

            // license
            'sales_license' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'quality_certificate' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'clinical_trials' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'registration_documents' => 'nullable|file|mimes:pdf,jpg,png|max:2048',

            // data
            'manufacturing_license_number' => 'nullable|string|max:255',
            'additional_usage_conditions' => 'nullable|string|max:255',
            'prescription_category' => 'nullable|string|max:255',
            'specific_features' => 'nullable|string|max:255',
        ];
    }

    /**
     * The field names of the medication wizard, so errors name the field the user sees.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'название',
            'release_form' => 'форма выпуска',
            'dosage' => 'дозировка',
            'manufacturer_id' => 'производитель',
            'group_id' => 'фармакологическая группа',
            'registration_number' => 'регистрационный номер',
            'registration_date' => 'дата регистрации',
            'min_deduction' => 'минимальное списание',
            'min_quantity' => 'минимальное количество',
            'notify_before_days' => 'срок уведомления о годности',
            'notify_min_qty' => 'остаток для уведомления',
            'active_substance_name' => 'название активного вещества',
            'active_substance_dosage' => 'дозировка активного вещества',
            'additional_substance' => 'вспомогательные вещества',
            'additional_substance.*.substance_name' => 'название вспомогательного вещества',
            'additional_substance.*.substance_dosage' => 'дозировка вспомогательного вещества',
            'administration_method' => 'способ применения',
            'dosage_regimen' => 'режим дозирования',
            'drug_interactions' => 'взаимодействие с другими препаратами',
            'storage_temperature' => 'температурный режим хранения',
            'storage_conditions' => 'условия хранения',
            'sales_license' => 'лицензия на продажу',
            'quality_certificate' => 'сертификат качества',
            'clinical_trials' => 'клинические исследования',
            'registration_documents' => 'регистрационные документы',
            'manufacturing_license_number' => 'номер лицензии на производство',
            'additional_usage_conditions' => 'дополнительные условия использования',
            'prescription_category' => 'категория рецепта',
            'specific_features' => 'индивидуальные особенности',
        ];
    }
}
