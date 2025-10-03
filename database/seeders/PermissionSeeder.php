<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_dashboard',

            // --- Admins
            'list_admins',
            'view_admins',
            'create_admins',
            'edit_admins',
            'delete_admins',

            // --- Specialties
            'list_specialties',
            'view_specialties',
            'create_specialties',
            'edit_specialties',
            'delete_specialties',

            // --- Departments
            'list_departments',
            'view_departments',
            'create_departments',
            'edit_departments',
            'delete_departments',

            // --- Department Rooms
            'list_department_rooms',
            'view_department_rooms',
            'create_department_rooms',
            'edit_department_rooms',
            'delete_department_rooms',

            // --- Equipments
            'list_equipments',
            'view_equipments',
            'create_equipments',
            'edit_equipments',
            'delete_equipments',

            // --- Equipment Types
            'list_equipment_types',
            'view_equipment_types',
            'create_equipment_types',
            'edit_equipment_types',
            'delete_equipment_types',

            // --- Staffs
            'list_staffs',
            'view_staffs',
            'create_staffs',
            'edit_staffs',
            'delete_staffs',

            // --- Staff Positions
            'list_staff_positions',
            'view_staff_positions',
            'create_staff_positions',
            'edit_staff_positions',
            'delete_staff_positions',

            // --- Supplies
            'list_supplies',
            'view_supplies',
            'create_supplies',
            'edit_supplies',
            'delete_supplies',

            // --- Receptions
            'list_receptions',
            'create_receptions',
            'change_receptions_status',
            'view_receptions',
            'view_appointment_receptions',
            'view_reception_doctor_calendar',

            // --- Suppliers
            'list_suppliers',
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',

            // --- Manufacturers
            'list_manufacturers',
            'view_manufacturers',
            'create_manufacturers',
            'edit_manufacturers',
            'delete_manufacturers',

            // --- Warehouses
            'list_warehouses',
            'view_warehouses',
            'create_warehouses',
            'edit_warehouses',
            'delete_warehouses',

            // --- Warehouse Types
            'list_warehouse_types',
            'view_warehouse_types',
            'create_warehouse_types',
            'edit_warehouse_types',
            'delete_warehouse_types',

            // --- Warehouse Medications & Supplies
            'view_warehouse_medications',
            'view_warehouse_supplies',
            'view_warehouse_medication_detail',

            // --- Pharmacies
            'list_pharmacies',
            'view_pharmacies',
            'create_pharmacies',
            'edit_pharmacies',
            'delete_pharmacies',

            // --- Pharmacy Types
            'list_pharmacy_types',
            'view_pharmacy_types',
            'create_pharmacy_types',
            'edit_pharmacy_types',
            'delete_pharmacy_types',

            'view_pharmacy_medications',

            // --- Transfers
            'list_transfers',
            'view_transfers',
            'create_transfers',
            'edit_transfers',
            'delete_transfers',

            'view_transfer_medications',

            // --- Surveys
            'list_surveys',
            'view_surveys',
            'create_surveys',
            'edit_surveys',
            'delete_surveys',

            'create_survey_response',
            'store_survey_response',

            // --- Medications
            'list_medications',
            'view_medications',
            'create_medications',
            'edit_medications',
            'delete_medications',

            // --- Medication Groups
            'list_medication_groups',
            'view_medication_groups',
            'create_medication_groups',
            'edit_medication_groups',
            'delete_medication_groups',

            // --- Services
            'list_services',
            'view_services',
            'create_services',
            'edit_services',
            'delete_services',

            // --- Patient Services
            'list_patient_services',
            'view_patient_services',
            'create_patient_services',
            'edit_patient_services',
            'delete_patient_services',

            // --- Service Groups
            'list_service_groups',
            'view_service_groups',
            'create_service_groups',
            'edit_service_groups',
            'delete_service_groups',

            // --- Service Types
            'list_service_types',
            'view_service_types',
            'create_service_types',
            'edit_service_types',
            'delete_service_types',

            // --- Service Procedures
            'list_service_procedures',
            'view_service_procedures',
            'create_service_procedures',
            'edit_service_procedures',
            'delete_service_procedures',

            // ---Equipment
            'list_equipment',
            'view_equipments',
            'create_equipments',
            'edit_equipments',
            'delete_equipments',

            // ---Equipment Type
            'list_equipment_type',
            'view_equipments_type',
            'create_equipments_type',
            'edit_equipments_type',
            'delete_equipments_type',

            // --- Service Methods
            'list_service_methods',
            'view_service_methods',
            'create_service_methods',
            'edit_service_methods',
            'delete_service_methods',

            // --- Laboratories
            'list_laboratories',
            'view_laboratories',
            'create_laboratories',
            'edit_laboratories',
            'delete_laboratories',

            // --- Regions
            'list_regions',
            'view_regions',
            'create_regions',
            'edit_regions',
            'delete_regions',

            // --- Cities
            'list_cities',
            'view_cities',
            'create_cities',
            'edit_cities',
            'delete_cities',

            // --- Allegories
            'list_allegories',
            'view_allegories',
            'create_allegories',
            'edit_allegories',
            'delete_allegories',

            // --- Diseases
            'list_diseases',
            'view_diseases',
            'create_diseases',
            'edit_diseases',
            'delete_diseases',

            // --- Devices
            'list_devices',
            'view_devices',
            'create_devices',
            'edit_devices',
            'delete_devices',

            // --- Patients
            'list_patients',
            'view_patients',
            'create_patients',
            'edit_patients',
            'delete_patients',

            'sister_window_show',
            'patient_inspection_store',
            'create_patient_map',

            // --- Maps
            'edit_maps',
            'update_maps',
            'delete_maps',

            // --- Diagnoses
            'list_diagnoses',
            'view_diagnoses',
            'create_diagnoses',
            'edit_diagnoses',
            'delete_diagnoses',

            // --- Diagnoses
            'create_patient_diagnoses',
            'list_patient_diagnoses',

            // --- Medication
            'create_patient_medication',
            'edit_patient_medication',
            'list_patient_medication',

            // --- Procedures
            'create_patient_procedure',

            // --- Household Materials
            'list_household_materials',
            'view_household_materials',
            'create_household_materials',
            'edit_household_materials',
            'delete_household_materials',

            'view_cashier',
            'list_cashier',
            'main_warehouse_remain',

            // offices
            'list_offices',
            'create_offices',
            'edit_offices',
            'delete_offices',

            // list_floors
            'list_floors',
            'create_floors',
            'view_floors',
            'edit_floors',
            'delete_floors',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }

        $roleMainAdmin = Role::firstOrCreate(['name' => 'main_admin', 'guard_name' => 'admin']);
        $roleRegister = Role::firstOrCreate(['name' => 'Регистратор', 'guard_name' => 'admin']);
        $roleCashier = Role::firstOrCreate(['name' => 'Кассир', 'guard_name' => 'admin']);

        $roleMainAdmin->syncPermissions($permissions);

        // Регистратор
        $roleRegister->syncPermissions([
            'view_patients',
            'create_patients',
            'edit_patients',
            'delete_patients',
            'patient_inspection_store',
            'create_patient_map',
            'view_patient_services',
            'create_patient_services',
            'edit_patient_services',
            'delete_patient_services',
            'view_dashboard',
            'view_cashier',
        ]);

        // Кассир
        $roleCashier->syncPermissions([
            'view_dashboard',
            'view_cashier',
        ]);

        // Назначаем роль главного админа первому пользователю
        if ($admin = Admin::query()->first()) {
            $admin->assignRole('main_admin');
        }
    }
}
