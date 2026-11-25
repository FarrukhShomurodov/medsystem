<?php

namespace App\Support\Authorization;

/**
 * Every permission of the application, with the Russian label the staff position form shows for
 * it. Route middleware, controllers, Blade @can and the sidebar check the names; PermissionSeeder
 * seeds them on the admin guard, and permissions:prune deletes the ones a database holds beyond them.
 */
final class PermissionCatalog
{
    /** Permission name => Russian label, in seeding order (the order of the position form). */
    public const array PERMISSIONS = [
        'view_dashboard' => 'Просмотр панели управления',

        // Admins
        'list_admins' => 'Список администраторов',
        'create_admins' => 'Создание администраторов',
        'edit_admins' => 'Редактирование администраторов',
        'delete_admins' => 'Удаление администраторов',

        // Specialties
        'list_specialties' => 'Список специальностей',
        'create_specialties' => 'Создание специальностей',
        'edit_specialties' => 'Редактирование специальностей',
        'delete_specialties' => 'Удаление специальностей',

        // Departments
        'list_departments' => 'Список отделений',
        'create_departments' => 'Создание отделений',
        'edit_departments' => 'Редактирование отделений',
        'delete_departments' => 'Удаление отделений',

        // Department rooms
        'list_department_rooms' => 'Список помещений отделений',
        'create_department_rooms' => 'Создание помещений отделений',
        'edit_department_rooms' => 'Редактирование помещений отделений',
        'delete_department_rooms' => 'Удаление помещений отделений',

        // Equipment
        'list_equipments' => 'Список оборудования',
        'create_equipments' => 'Создание оборудования',
        'edit_equipments' => 'Редактирование оборудования',
        'delete_equipments' => 'Удаление оборудования',

        // Equipment types
        'list_equipment_types' => 'Список типов оборудования',
        'create_equipment_types' => 'Создание типов оборудования',
        'edit_equipment_types' => 'Редактирование типов оборудования',
        'delete_equipment_types' => 'Удаление типов оборудования',

        // Staff
        'list_staffs' => 'Список персонала',
        'create_staffs' => 'Создание персонала',
        'edit_staffs' => 'Редактирование персонала',
        'delete_staffs' => 'Удаление персонала',

        // Staff positions
        'list_staff_positions' => 'Список должностей персонала',
        'create_staff_positions' => 'Создание должностей персонала',
        'edit_staff_positions' => 'Редактирование должностей персонала',
        'delete_staff_positions' => 'Удаление должностей персонала',

        // Supplies
        'list_supplies' => 'Список расходников',
        'create_supplies' => 'Создание расходников',
        'edit_supplies' => 'Редактирование расходников',
        'delete_supplies' => 'Удаление расходников',

        // Receptions
        'list_receptions' => 'Список приемов',
        'create_receptions' => 'Создание приемов',
        'change_receptions_status' => 'Изменение статуса приема',
        'view_appointment_receptions' => 'Назначение приемов',
        'view_reception_doctor_calendar' => 'Просмотр календаря врача',

        // Suppliers
        'list_suppliers' => 'Список поставщиков',
        'create_suppliers' => 'Создание поставщиков',
        'edit_suppliers' => 'Редактирование поставщиков',
        'delete_suppliers' => 'Удаление поставщиков',

        // Manufacturers
        'list_manufacturers' => 'Список производителей',
        'create_manufacturers' => 'Создание производителей',
        'edit_manufacturers' => 'Редактирование производителей',
        'delete_manufacturers' => 'Удаление производителей',

        // Warehouses
        'list_warehouses' => 'Список складов',
        'view_warehouses' => 'Просмотр складов',
        'create_warehouses' => 'Создание складов',
        'edit_warehouses' => 'Редактирование складов',
        'delete_warehouses' => 'Удаление складов',

        // Warehouse types
        'list_warehouse_types' => 'Список типов складов',
        'create_warehouse_types' => 'Создание типов складов',
        'edit_warehouse_types' => 'Редактирование типов складов',
        'delete_warehouse_types' => 'Удаление типов складов',

        // Warehouse stock
        'view_warehouse_medications' => 'Просмотр медикаментов на складе',
        'view_warehouse_supplies' => 'Просмотр расходников на складе',
        'view_warehouse_medication_detail' => 'Просмотр деталей медикамента на складе',
        'view_warehouse_remains' => 'Просмотр остатков складов',

        // Pharmacies
        'list_pharmacies' => 'Список аптек',
        'create_pharmacies' => 'Создание аптек',
        'edit_pharmacies' => 'Редактирование аптек',
        'delete_pharmacies' => 'Удаление аптек',
        'view_pharmacy_medications' => 'Просмотр медикаментов в аптеках',

        // Pharmacy types
        'list_pharmacy_types' => 'Список типов аптек',
        'create_pharmacy_types' => 'Создание типов аптек',
        'edit_pharmacy_types' => 'Редактирование типов аптек',
        'delete_pharmacy_types' => 'Удаление типов аптек',

        // Transfers
        'list_transfers' => 'Список перемещений',
        'create_transfers' => 'Создание перемещений',
        'edit_transfers' => 'Редактирование перемещений',
        'delete_transfers' => 'Удаление перемещений',
        'view_transfer_medications' => 'Просмотр медикаментов в перемещениях',

        // Surveys
        'list_surveys' => 'Список опросов',
        'view_surveys' => 'Просмотр опросов',
        'create_surveys' => 'Создание опросов',
        'edit_surveys' => 'Редактирование опросов',
        'delete_surveys' => 'Удаление опросов',
        'create_survey_response' => 'Создание ответа на опрос',
        'store_survey_response' => 'Сохранение ответа на опрос',

        // Medications
        'list_medications' => 'Список медикаментов',
        'view_medications' => 'Просмотр медикаментов',
        'create_medications' => 'Создание медикаментов',
        'edit_medications' => 'Редактирование медикаментов',
        'delete_medications' => 'Удаление медикаментов',

        // Medication groups
        'list_medication_groups' => 'Список групп медикаментов',
        'create_medication_groups' => 'Создание групп медикаментов',
        'edit_medication_groups' => 'Редактирование групп медикаментов',
        'delete_medication_groups' => 'Удаление групп медикаментов',

        // Services
        'list_services' => 'Список услуг',
        'create_services' => 'Создание услуг',
        'edit_services' => 'Редактирование услуг',
        'delete_services' => 'Удаление услуг',

        // Patient services
        'list_patient_services' => 'Список услуг пациента',
        'create_patient_services' => 'Создание услуг пациента',
        'edit_patient_services' => 'Редактирование услуг пациента',
        'delete_patient_services' => 'Удаление услуг пациента',

        // Service groups
        'list_service_groups' => 'Список групп услуг',
        'create_service_groups' => 'Создание групп услуг',
        'edit_service_groups' => 'Редактирование групп услуг',
        'delete_service_groups' => 'Удаление групп услуг',

        // Service types
        'list_service_types' => 'Список типов услуг',
        'create_service_types' => 'Создание типов услуг',
        'edit_service_types' => 'Редактирование типов услуг',
        'delete_service_types' => 'Удаление типов услуг',

        // Service procedures
        'list_service_procedures' => 'Список процедур',
        'create_service_procedures' => 'Создание процедур',
        'edit_service_procedures' => 'Редактирование процедур',
        'delete_service_procedures' => 'Удаление процедур',

        // Service methods
        'list_service_methods' => 'Список методов услуг',
        'create_service_methods' => 'Создание методов услуг',
        'edit_service_methods' => 'Редактирование методов услуг',
        'delete_service_methods' => 'Удаление методов услуг',

        // Regions
        'list_regions' => 'Список регионов',
        'create_regions' => 'Создание регионов',
        'edit_regions' => 'Редактирование регионов',
        'delete_regions' => 'Удаление регионов',

        // Cities
        'list_cities' => 'Список городов',
        'create_cities' => 'Создание городов',
        'edit_cities' => 'Редактирование городов',
        'delete_cities' => 'Удаление городов',

        // Allergies
        'list_allegories' => 'Список аллергий',
        'create_allegories' => 'Создание аллергий',
        'edit_allegories' => 'Редактирование аллергий',
        'delete_allegories' => 'Удаление аллергий',

        // Chronic diseases
        'list_diseases' => 'Список заболеваний',
        'create_diseases' => 'Создание заболеваний',
        'edit_diseases' => 'Редактирование заболеваний',
        'delete_diseases' => 'Удаление заболеваний',

        // Medical devices
        'list_devices' => 'Список устройств',
        'create_devices' => 'Создание устройств',
        'edit_devices' => 'Редактирование устройств',
        'delete_devices' => 'Удаление устройств',

        // Patients
        'list_patients' => 'Список пациентов',
        'view_patients' => 'Просмотр пациентов',
        'create_patients' => 'Создание пациентов',
        'edit_patients' => 'Редактирование пациентов',
        'delete_patients' => 'Удаление пациентов',
        'sister_window_show' => 'Просмотр сестринского окна',
        'patient_inspection_store' => 'Создание осмотра',
        'create_patient_map' => 'Создание карты пациента',
        'confirm_patient_registration' => 'Подтверждение услуг, приёмов и процедур пациента (регистратура)',

        // Examination maps
        'edit_maps' => 'Редактирование карт',
        'update_maps' => 'Обновление карт',
        'delete_maps' => 'Удаление карт',

        // Diagnoses
        'list_diagnoses' => 'Список диагнозов',
        'create_diagnoses' => 'Создание диагнозов',
        'edit_diagnoses' => 'Редактирование диагнозов',
        'delete_diagnoses' => 'Удаление диагнозов',
        'create_patient_diagnoses' => 'Создание диагноза пациента',
        'list_patient_diagnoses' => 'Список диагнозов пациента',

        // Prescriptions and procedures
        'create_patient_medication' => 'Назначение медикамента пациенту',
        'edit_patient_medication' => 'Редактирование медикамента пациента',
        'list_patient_medication' => 'Список медикаментов пациента',
        'create_patient_procedure' => 'Назначение процедуры пациенту',

        // Household materials
        'list_household_materials' => 'Список хоз. материалов',
        'create_household_materials' => 'Создание хоз. материалов',
        'edit_household_materials' => 'Редактирование хоз. материалов',
        'delete_household_materials' => 'Удаление хоз. материалов',

        // Cashier
        'view_cashier' => 'Просмотр кассы',
        'accept_payments' => 'Приём оплаты в кассе',

        // Main warehouse and the supply requests of the department warehouses
        'main_warehouse_remain' => 'Просмотр остатков общего склада',
        'edit_main_warehouse' => 'Приход и списание товаров общего склада',
        'create_supply_requests' => 'Создание заявок на общий склад',
        'approve_supply_requests' => 'Одобрение заявок на общий склад',

        // Offices
        'list_offices' => 'Список кабинетов',
        'create_offices' => 'Создание кабинетов',
        'edit_offices' => 'Редактирование кабинетов',
        'delete_offices' => 'Удаление кабинетов',

        // Floors
        'list_floors' => 'Список этажей',
        'view_floors' => 'Просмотр этажей',
        'create_floors' => 'Создание этажей',
        'edit_floors' => 'Редактирование этажей',
        'delete_floors' => 'Удаление этажей',
    ];

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::PERMISSIONS);
    }

    /** The Russian label of a permission; one the catalogue does not hold any more keeps its name. */
    public static function label(string $name): string
    {
        return self::PERMISSIONS[$name] ?? $name;
    }
}
