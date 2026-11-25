<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Every foreign key was created with ON DELETE CASCADE, so deleting a lookup row (a region, a staff
// member, a service from the price list, a medication) silently wiped patients, examination maps,
// prescriptions or stock. Keys to reference data now RESTRICT the delete, or SET NULL where the
// column is optional. Cascades stay only where the child belongs to its parent (answers, contacts,
// the records of an examination map or patient).
return new class extends Migration
{
    /** table => [column => [referenced table, on delete]] */
    private const array KEYS = [
        'patients' => [
            'region_id' => ['regions', 'set null'],
            'city_id' => ['cities', 'set null'],
            'registered_region_id' => ['regions', 'set null'],
            'registered_city_id' => ['cities', 'set null'],
        ],
        'patient_allegories' => ['allegory_id' => ['allegories', 'restrict']],
        'patient_chronic_diseases' => ['chronic_disease_id' => ['chronic_diseases', 'restrict']],
        'patient_medical_devices' => ['medical_device_id' => ['medical_devices', 'restrict']],
        'warehouses' => ['warehouse_type_id' => ['warehouse_types', 'restrict']],
        'equipment' => [
            'equipment_type_id' => ['equipment_types', 'restrict'],
            'manufacturer_id' => ['manufacturers', 'restrict'],
            'staff_id' => ['staff', 'restrict'],
        ],
        'staff_professional_data' => [
            'position_id' => ['staff_positions', 'restrict'],
            'addition_position_id' => ['staff_positions', 'set null'],
        ],
        'staff_schedules' => ['day_of_week_id' => ['day_of_weeks', 'restrict']],
        'departments' => [
            'warehouse_id' => ['warehouses', 'set null'],
            'floor_id' => ['floors', 'set null'],
        ],
        'offices' => ['floor_id' => ['floors', 'set null']],
        'examination_maps' => [
            'staff_id' => ['staff', 'restrict'],
            'department_id' => ['departments', 'restrict'],
        ],
        'medications' => [
            'manufacturer_id' => ['manufacturers', 'restrict'],
            'group_id' => ['medication_groups', 'restrict'],
        ],
        'services' => [
            'service_type_id' => ['service_types', 'restrict'],
            'service_group_id' => ['service_groups', 'restrict'],
        ],
        'patient_services' => ['service_id' => ['services', 'restrict']],
        'patient_service_to_staffs_assignment' => ['service_staff_id' => ['staff', 'restrict']],
        'patient_service_to_equipments_assignment' => ['service_equipment_id' => ['equipment', 'restrict']],
        'patient_service_to_methods_assignments' => ['service_methods_id' => ['service_methods', 'restrict']],
        'patient_procedures' => ['procedure_id' => ['procedures', 'restrict']],
        'patient_medications' => ['medication_id' => ['medications', 'restrict']],
        'patient_medication_compatibility' => ['compatible_medication_id' => ['medications', 'restrict']],
        'patient_assignments' => [
            'service_id' => ['services', 'restrict'],
            'staff_id' => ['staff', 'set null'],
        ],
        'receptions' => [
            'staff_id' => ['staff', 'restrict'],
            'service_id' => ['services', 'restrict'],
        ],
        'surveys' => ['survey_type_id' => ['survey_types', 'restrict']],
        'survey_responses' => ['survey_id' => ['surveys', 'restrict']],
        'document_responses' => ['document_id' => ['documents', 'restrict']],
        'patient_document_score_finisheds' => [
            'document_score_id' => ['document_scores', 'restrict'],
            'document_action_id' => ['document_score_actions', 'restrict'],
        ],
        'patient_normal_vital_parameters' => ['vital_parameter_id' => ['vital_parameters', 'restrict']],
        'examination_map_vital_parameters' => ['vital_parameter_id' => ['vital_parameters', 'restrict']],
        'warehouse_accountings' => ['medication_id' => ['medications', 'restrict']],
        'warehouse_medical_supplies' => ['supply_id' => ['medical_supplies', 'restrict']],
        'warehouse_household_materials' => ['household_material_id' => ['household_materials', 'restrict']],
        'medication_cost_and_supplies' => ['supplier_id' => ['suppliers', 'restrict']],
        'main_warehouse_medications' => [
            'medication_id' => ['medications', 'restrict'],
            'supplier_id' => ['suppliers', 'set null'],
        ],
        'main_warehouse_medical_supplies' => ['supply_id' => ['medical_supplies', 'restrict']],
        'main_warehouse_household_materials' => ['household_material_id' => ['household_materials', 'restrict']],
        'supply_requests' => ['main_warehouse_id' => ['main_warehouses', 'restrict']],
        'pharmacies' => ['type_id' => ['pharmacy_types', 'restrict']],
        'pharmacy_medications' => ['medication_id' => ['medications', 'restrict']],
        'transfer_medications' => ['medication_id' => ['medications', 'restrict']],
    ];

    public function up(): void
    {
        $this->replaceForeignKeys(rollback: false);
    }

    public function down(): void
    {
        $this->replaceForeignKeys(rollback: true);
    }

    private function replaceForeignKeys(bool $rollback): void
    {
        foreach (self::KEYS as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $rollback): void {
                foreach ($columns as $column => [$referencedTable, $onDelete]) {
                    $table->dropForeign([$column]);
                    $table->foreign($column)->references('id')->on($referencedTable)
                        ->onDelete($rollback ? 'cascade' : $onDelete);
                }
            });
        }
    }
};
