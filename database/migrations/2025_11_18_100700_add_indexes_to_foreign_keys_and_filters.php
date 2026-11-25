<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PostgreSQL indexes primary and unique keys only, never foreign key columns. Without these
// indexes every relation lookup, eager load and ON DELETE check is a sequential scan.
// A composite index also serves lookups on its leading column, so that column is not repeated.
return new class extends Migration
{
    /** table => indexes; each index is a column or a list of columns */
    private const array INDEXES = [
        // Patients and their records
        'cities' => ['region_id'],
        'patients' => ['region_id', 'city_id', 'registered_region_id', 'registered_city_id'],
        'patient_allegories' => ['patient_id', 'allegory_id'],
        'patient_chronic_diseases' => ['patient_id', 'chronic_disease_id'],
        'patient_medical_devices' => ['patient_id', 'medical_device_id'],
        'patient_inspections' => ['patient_id'],
        'patient_assignments' => ['patient_id', 'service_id', 'staff_id'],
        'patient_diagnoses' => ['patient_id', 'examination_map_id', 'diagnosis_id'],
        'patient_normal_vital_parameters' => [['patient_id', 'vital_parameter_id'], 'vital_parameter_id'],
        'last_viewed_patients' => [['admin_id', 'patient_id'], 'patient_id'],
        'admin_selected_diagnoses' => [['admin_id', 'diagnosis_id'], 'diagnosis_id'],
        'diagnoses' => ['code'],

        // Examination maps and everything ordered within them
        'examination_maps' => [['patient_id', 'admission_date'], 'staff_id', 'department_id'],
        'examination_map_vital_parameters' => ['examination_map_id', 'vital_parameter_id'],
        'patient_services' => [['patient_id', 'examination_map_id'], 'examination_map_id', 'service_id', 'status', 'service_date'],
        'patient_service_to_staffs_assignment' => ['patient_service_id', 'service_staff_id'],
        'patient_service_to_equipments_assignment' => ['patient_service_id', 'service_equipment_id'],
        'patient_service_to_methods_assignments' => ['patient_service_id', 'service_methods_id'],
        'patient_procedures' => [['patient_id', 'examination_map_id'], 'examination_map_id', 'procedure_id', 'status'],
        'patient_medications' => [['patient_id', 'examination_map_id'], 'examination_map_id', 'medication_id', 'status'],
        'patient_medication_compatibility' => ['patient_medication_id', 'compatible_medication_id'],
        'receptions' => [['staff_id', 'date'], 'patient_id', 'examination_map_id', 'service_id', 'status'],

        // Surveys and scored documents
        'surveys' => ['survey_type_id', 'diagnosis_id'],
        'survey_groups' => ['survey_id'],
        'survey_questions' => ['survey_id', 'group_id'],
        'survey_responses' => ['patient_id', 'examination_map_id', 'survey_id', 'patient_service_id'],
        'survey_answers' => ['survey_response_id', 'question_id'],
        'staff_surveys' => ['staff_id', 'survey_id'],
        'document_questions' => ['document_id'],
        'document_responses' => ['patient_id', 'examination_map_id', 'document_id'],
        'document_answers' => ['document_response_id', 'question_id'],
        'document_scores' => ['document_id'],
        'document_score_actions' => ['document_score_id'],
        'patient_document_score_finisheds' => ['document_response_id', 'document_score_id', 'document_action_id'],

        // Staff, schedules and hospital structure
        'staff_contacts' => ['staff_id'],
        'staff_documents' => ['staff_id'],
        'staff_professional_data' => ['staff_id', 'position_id', 'addition_position_id', 'speciality_id', 'department_id'],
        'staff_schedules' => [['staff_id', 'day_of_week_id'], 'day_of_week_id'],
        'staff_absences' => [['staff_id', 'start_date']],
        'staff_service_assignment' => [['staff_id', 'staff_service_id'], 'staff_service_id'],
        'admins' => ['staff_id'],
        'departments' => ['warehouse_id', 'floor_id'],
        'department_rooms' => ['department_id', 'ward_room_id'],
        'offices' => ['floor_id'],
        'room_beds' => ['ward_room_id'],
        'equipment' => ['equipment_type_id', 'manufacturer_id', 'staff_id'],

        // Price list
        'services' => ['service_type_id', 'service_group_id'],
        'service_sub_groups' => ['service_group_id'],

        // Medications, warehouses and pharmacies
        'medications' => ['manufacturer_id', 'group_id'],
        'medication_compositions' => ['medication_id'],
        'medication_applications' => ['medication_id'],
        'medication_storages' => ['medication_id'],
        'medication_license_and_certifications' => ['medication_id'],
        'medication_additional_data' => ['medication_id'],
        'medication_additional_substance' => ['medication_composition_id'],
        'medication_packagings' => ['warehouse_accounting_id'],
        'medication_cost_and_supplies' => ['warehouse_accounting_id', 'supplier_id'],
        'supplier_manufacturers' => ['supplier_id', 'manufacturer_id'],
        'main_warehouse_medications' => [['main_warehouse_id', 'medication_id'], 'medication_id', 'supplier_id', 'expiration_date'],
        'main_warehouse_medical_supplies' => ['main_warehouse_id', 'supply_id'],
        'main_warehouse_household_materials' => ['main_warehouse_id', 'household_material_id'],
        'warehouses' => ['warehouse_type_id'],
        'warehouse_accountings' => [['warehouse_id', 'medication_id'], 'medication_id', 'expiration_date'],
        'warehouse_medical_supplies' => ['warehouse_id', 'supply_id'],
        'warehouse_household_materials' => ['warehouse_id', 'household_material_id'],
        'warehouse_contacts' => ['warehouse_id'],
        'warehouse_capacities' => ['warehouse_id'],
        'warehouse_storage_handlings' => ['warehouse_id'],
        'supply_requests' => ['warehouse_id', 'main_warehouse_id', 'status'],
        'supply_request_items' => ['request_id'],
        'pharmacies' => ['type_id'],
        'pharmacy_license_and_certifications' => ['pharmacy_id'],
        'pharmacy_support_services' => ['pharmacy_id'],
        'pharmacy_medications' => ['pharmacy_id', 'medication_id'],
        'transfers' => ['from_warehouse_id', 'to_pharmacy_id', 'status'],
        'transfer_medications' => ['transfer_id', 'medication_id'],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $tableName => $indexes) {
            Schema::table($tableName, function (Blueprint $table) use ($indexes): void {
                foreach ($indexes as $columns) {
                    $table->index($columns);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $tableName => $indexes) {
            Schema::table($tableName, function (Blueprint $table) use ($indexes): void {
                foreach ($indexes as $columns) {
                    $table->dropIndex((array) $columns);
                }
            });
        }
    }
};
