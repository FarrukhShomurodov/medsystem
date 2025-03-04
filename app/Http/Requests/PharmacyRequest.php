<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PharmacyRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type_id' => 'required|int|exists:pharmacy_types,id',
            'legal_address' => 'required|string|max:255',
            'actual_address' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'working_hours' => 'required|string|max:255',

            // license
            'license_number' => 'required|string',
            'license_issue_date' => 'required|date',
            'license_expiry_date' => 'required|date',
            'quality_certificate_number' => 'nullable|string',
            'compliance_standards' => 'nullable|string',

            // support
            'consultation_phone' => 'required|string',
            'medication_consultation_available' => 'required|boolean',
        ];
    }
}
