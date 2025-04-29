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

            'view_admins',
            'create_admins',
            'edit_admins',
            'delete_admins',

            'view_specialties',
            'create_specialties',
            'edit_specialties',
            'delete_specialties',

            'view_departments',
            'create_departments',
            'edit_departments',
            'delete_departments',

            'view_department_types',
            'create_department_types',
            'edit_department_types',
            'delete_department_types',

            'view_department_rooms',
            'create_department_rooms',
            'edit_department_rooms',
            'delete_department_rooms',

            'view_equipments',
            'create_equipments',
            'edit_equipments',
            'delete_equipments',

            'view_staffs',
            'create_staffs',
            'edit_staffs',
            'delete_staffs',

            'view_staff_positions',
            'create_staff_positions',
            'edit_staff_positions',
            'delete_staff_positions',

            'view_supplies',
            'create_supplies',
            'edit_supplies',
            'delete_supplies',

            'view_receptions',
            'create_receptions',
            'edit_receptions',
            'delete_receptions',
            'view_reception_doctor_calendar',

            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',

            'view_manufacturers',
            'create_manufacturers',
            'edit_manufacturers',
            'delete_manufacturers',

            'view_warehouses',
            'create_warehouses',
            'edit_warehouses',
            'delete_warehouses',

            'view_warehouse_types',
            'create_warehouse_types',
            'edit_warehouse_types',
            'delete_warehouse_types',

            'view_warehouse_medications',
            'view_warehouse_supplies',
            'view_warehouse_medication_detail',

            'view_pharmacies',
            'create_pharmacies',
            'edit_pharmacies',
            'delete_pharmacies',

            'view_pharmacy_types',
            'create_pharmacy_types',
            'edit_pharmacy_types',
            'delete_pharmacy_types',

            'view_pharmacy_medications',

            'view_transfers',
            'create_transfers',
            'edit_transfers',
            'delete_transfers',

            'view_transfer_medications',

            'view_surveys',
            'create_surveys',
            'edit_surveys',
            'delete_surveys',

            'create_survey_response',
            'store_survey_response',

            'view_analyzes',
            'create_analyzes',
            'edit_analyzes',
            'delete_analyzes',

            'create_analyze_result',
            'store_analyze_result',
            'show_analyze_result',
            'edit_analyze_result',
            'update_analyze_result',

            'view_medications',
            'create_medications',
            'edit_medications',
            'delete_medications',

            'view_medication_groups',
            'create_medication_groups',
            'edit_medication_groups',
            'delete_medication_groups',

            'view_services',
            'create_services',
            'edit_services',
            'delete_services',

            'view_patient_services',
            'create_patient_services',
            'edit_patient_services',
            'delete_patient_services',

            'view_service_categories',
            'create_service_categories',
            'edit_service_categories',
            'delete_service_categories',

            'view_service_procedures',
            'create_service_procedures',
            'edit_service_procedures',
            'delete_service_procedures',

            'view_service_methods',
            'create_service_methods',
            'edit_service_methods',
            'delete_service_methods',

            'view_laboratories',
            'create_laboratories',
            'edit_laboratories',
            'delete_laboratories',

            'view_analyze_methods',
            'create_analyze_methods',
            'edit_analyze_methods',
            'delete_analyze_methods',

            'view_analyze_types',
            'create_analyze_types',
            'edit_analyze_types',
            'delete_analyze_types',

            'view_regions',
            'create_regions',
            'edit_regions',
            'delete_regions',

            'view_cities',
            'create_cities',
            'edit_cities',
            'delete_cities',

            'view_allegories',
            'create_allegories',
            'edit_allegories',
            'delete_allegories',

            'view_diseases',
            'create_diseases',
            'edit_diseases',
            'delete_diseases',

            'view_devices',
            'create_devices',
            'edit_devices',
            'delete_devices',

            'view_patients',
            'create_patients',
            'edit_patients',
            'delete_patients',

            'edit_maps',
            'update_maps',
            'delete_maps',

            'create_patient_map',
        ];

        $role = Role::firstOrCreate([
            'name' => 'main_admin',
            'guard_name' => 'admin',
        ]);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }

        $role->syncPermissions($permissions);

        $admin = Admin::query()->first();
        $admin->assignRole('main_admin');
    }
}
