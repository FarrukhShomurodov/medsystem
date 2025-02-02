<?php
return [
    'service_categories *',

    'medications *' => [
        'name',
    ],

    'patient_service_to_medications_assignment *' => [
        'medication_id',
        'patient_service_id',
        'dosage',
        'admission_period',
        'notes',
    ],

    //
    'procedures *' => [
        'name',
    ],

    'patient_service_to_procedures_assignment *' => [
        'procedure_id',
        'patient_service_id',
        'service_date *',
    ],

    'service_methods',
    'patient_service_to_methods_assignments',

    'patient_service_equipments',
    'patient_service_to_equipments_assignment',

    'service_staff',
    'service_staff_specialties',
    'patient_service_to_staffs_assignment',


    'service' => [
        'id',
        'name *',
        'description',
        'code',
        'price *',
        'discount *',
        'currency',
        'duration',
        'service_category_id',
    ],

    'patient_service' => [
        'service_id',
        'patient_id',
        'examination_map_id',
        'doctor_id',
        'institution_id',
        'service_date *',
        'service_time *',
        'place' => [
            'institution/cabinet',
            'address',
        ],
        'results',
        'status',
        'payment' => [
            'status' => 'paid, unpaid, partially_paid',
            'method' => 'cash, card, insurance_company'
        ],
        'insurance_policy' => [
            'company_name',
            'insurance_policy_number',
            'percentage_of_coverage',
        ]
    ],
];
