<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffPositionRequest;
use App\Models\StaffPosition;
use App\Services\StaffPositionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;

class StaffPositionController extends Controller
{
    protected StaffPositionService $service;
    protected array $permissionsRu;

    public function __construct(StaffPositionService $service)
    {
        $this->middleware(['permission:list_staff_positions'])->only('index');
        $this->middleware(['permission:create_staff_positions'])->only(['create', 'store']);
        $this->middleware(['permission:edit_staff_positions'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_staff_positions'])->only('destroy');

        $this->service = $service;

        $this->permissionsRu = [
            'view_dashboard' => 'Просмотр панели управления',

            // --- Admins
            'list_admins' => 'Список администраторов',
            'view_admins' => 'Просмотр администраторов',
            'create_admins' => 'Создание администраторов',
            'edit_admins' => 'Редактирование администраторов',
            'delete_admins' => 'Удаление администраторов',

            // --- Specialties
            'list_specialties' => 'Список специальностей',
            'view_specialties' => 'Просмотр специальностей',
            'create_specialties' => 'Создание специальностей',
            'edit_specialties' => 'Редактирование специальностей',
            'delete_specialties' => 'Удаление специальностей',

            // --- Departments
            'list_departments' => 'Список отделений',
            'view_departments' => 'Просмотр отделений',
            'create_departments' => 'Создание отделений',
            'edit_departments' => 'Редактирование отделений',
            'delete_departments' => 'Удаление отделений',

            // --- Department Rooms
            'list_department_rooms' => 'Список помещений отделений',
            'view_department_rooms' => 'Просмотр помещений отделений',
            'create_department_rooms' => 'Создание помещений отделений',
            'edit_department_rooms' => 'Редактирование помещений отделений',
            'delete_department_rooms' => 'Удаление помещений отделений',

            // --- Equipments
            'list_equipments' => 'Список оборудования',
            'view_equipments' => 'Просмотр оборудования',
            'create_equipments' => 'Создание оборудования',
            'edit_equipments' => 'Редактирование оборудования',
            'delete_equipments' => 'Удаление оборудования',

            // --- Equipment Types
            'list_equipment_types' => 'Список типов оборудования',
            'view_equipment_types' => 'Просмотр типов оборудования',
            'create_equipment_types' => 'Создание типов оборудования',
            'edit_equipment_types' => 'Редактирование типов оборудования',
            'delete_equipment_types' => 'Удаление типов оборудования',

            // --- Staffs
            'list_staffs' => 'Список персонала',
            'view_staffs' => 'Просмотр персонала',
            'create_staffs' => 'Создание персонала',
            'edit_staffs' => 'Редактирование персонала',
            'delete_staffs' => 'Удаление персонала',

            // --- Staff Positions
            'list_staff_positions' => 'Список должностей персонала',
            'view_staff_positions' => 'Просмотр должностей персонала',
            'create_staff_positions' => 'Создание должностей персонала',
            'edit_staff_positions' => 'Редактирование должностей персонала',
            'delete_staff_positions' => 'Удаление должностей персонала',

            // --- Supplies
            'list_supplies' => 'Список расходников',
            'view_supplies' => 'Просмотр расходников',
            'create_supplies' => 'Создание расходников',
            'edit_supplies' => 'Редактирование расходников',
            'delete_supplies' => 'Удаление расходников',

            // --- Receptions
            'list_receptions' => 'Список приемов',
            'create_receptions' => 'Создание приемов',
            'change_receptions_status' => 'Изменение статуса приема',
            'view_receptions' => 'Просмотр приемов',
            'view_appointment_receptions' => 'Назначение приемов',
            'view_reception_doctor_calendar' => 'Просмотр календаря врача',

            // --- Suppliers
            'list_suppliers' => 'Список поставщиков',
            'view_suppliers' => 'Просмотр поставщиков',
            'create_suppliers' => 'Создание поставщиков',
            'edit_suppliers' => 'Редактирование поставщиков',
            'delete_suppliers' => 'Удаление поставщиков',

            // --- Manufacturers
            'list_manufacturers' => 'Список производителей',
            'view_manufacturers' => 'Просмотр производителей',
            'create_manufacturers' => 'Создание производителей',
            'edit_manufacturers' => 'Редактирование производителей',
            'delete_manufacturers' => 'Удаление производителей',

            // --- Warehouses
            'list_warehouses' => 'Список складов',
            'view_warehouses' => 'Просмотр складов',
            'create_warehouses' => 'Создание складов',
            'edit_warehouses' => 'Редактирование складов',
            'delete_warehouses' => 'Удаление складов',

            // --- Warehouse Types
            'list_warehouse_types' => 'Список типов складов',
            'view_warehouse_types' => 'Просмотр типов складов',
            'create_warehouse_types' => 'Создание типов складов',
            'edit_warehouse_types' => 'Редактирование типов складов',
            'delete_warehouse_types' => 'Удаление типов складов',

            // --- Warehouse Medications & Supplies
            'view_warehouse_medications' => 'Просмотр медикаментов на складе',
            'view_warehouse_supplies' => 'Просмотр расходников на складе',
            'view_warehouse_medication_detail' => 'Просмотр деталей медикамента на складе',

            // --- Pharmacies
            'list_pharmacies' => 'Список аптек',
            'view_pharmacies' => 'Просмотр аптек',
            'create_pharmacies' => 'Создание аптек',
            'edit_pharmacies' => 'Редактирование аптек',
            'delete_pharmacies' => 'Удаление аптек',

            // --- Pharmacy Types
            'list_pharmacy_types' => 'Список типов аптек',
            'view_pharmacy_types' => 'Просмотр типов аптек',
            'create_pharmacy_types' => 'Создание типов аптек',
            'edit_pharmacy_types' => 'Редактирование типов аптек',
            'delete_pharmacy_types' => 'Удаление типов аптек',

            'view_pharmacy_medications' => 'Просмотр медикаментов в аптеках',

            // --- Transfers
            'list_transfers' => 'Список перемещений',
            'view_transfers' => 'Просмотр перемещений',
            'create_transfers' => 'Создание перемещений',
            'edit_transfers' => 'Редактирование перемещений',
            'delete_transfers' => 'Удаление перемещений',

            'view_transfer_medications' => 'Просмотр медикаментов в перемещениях',

            // --- Surveys
            'list_surveys' => 'Список опросов',
            'view_surveys' => 'Просмотр опросов',
            'create_surveys' => 'Создание опросов',
            'edit_surveys' => 'Редактирование опросов',
            'delete_surveys' => 'Удаление опросов',

            'create_survey_response' => 'Создание ответа на опрос',
            'store_survey_response' => 'Сохранение ответа на опрос',

            // --- Medications
            'list_medications' => 'Список медикаментов',
            'view_medications' => 'Просмотр медикаментов',
            'create_medications' => 'Создание медикаментов',
            'edit_medications' => 'Редактирование медикаментов',
            'delete_medications' => 'Удаление медикаментов',

            // --- Medication Groups
            'list_medication_groups' => 'Список групп медикаментов',
            'view_medication_groups' => 'Просмотр групп медикаментов',
            'create_medication_groups' => 'Создание групп медикаментов',
            'edit_medication_groups' => 'Редактирование групп медикаментов',
            'delete_medication_groups' => 'Удаление групп медикаментов',

            // --- Services
            'list_services' => 'Список услуг',
            'view_services' => 'Просмотр услуг',
            'create_services' => 'Создание услуг',
            'edit_services' => 'Редактирование услуг',
            'delete_services' => 'Удаление услуг',

            // --- Patient Services
            'list_patient_services' => 'Список услуг пациента',
            'view_patient_services' => 'Просмотр услуг пациента',
            'create_patient_services' => 'Создание услуг пациента',
            'edit_patient_services' => 'Редактирование услуг пациента',
            'delete_patient_services' => 'Удаление услуг пациента',

            // --- Service Groups
            'list_service_groups' => 'Список групп услуг',
            'view_service_groups' => 'Просмотр групп услуг',
            'create_service_groups' => 'Создание групп услуг',
            'edit_service_groups' => 'Редактирование групп услуг',
            'delete_service_groups' => 'Удаление групп услуг',

            // --- Service Types
            'list_service_types' => 'Список типов услуг',
            'view_service_types' => 'Просмотр типов услуг',
            'create_service_types' => 'Создание типов услуг',
            'edit_service_types' => 'Редактирование типов услуг',
            'delete_service_types' => 'Удаление типов услуг',

            // --- Service Procedures
            'list_service_procedures' => 'Список процедур',
            'view_service_procedures' => 'Просмотр процедур',
            'create_service_procedures' => 'Создание процедур',
            'edit_service_procedures' => 'Редактирование процедур',
            'delete_service_procedures' => 'Удаление процедур',

            // --- Service Methods
            'list_service_methods' => 'Список методов услуг',
            'view_service_methods' => 'Просмотр методов услуг',
            'create_service_methods' => 'Создание методов услуг',
            'edit_service_methods' => 'Редактирование методов услуг',
            'delete_service_methods' => 'Удаление методов услуг',

            // --- Laboratories
            'list_laboratories' => 'Список лабораторий',
            'view_laboratories' => 'Просмотр лабораторий',
            'create_laboratories' => 'Создание лабораторий',
            'edit_laboratories' => 'Редактирование лабораторий',
            'delete_laboratories' => 'Удаление лабораторий',

            // --- Regions
            'list_regions' => 'Список регионов',
            'view_regions' => 'Просмотр регионов',
            'create_regions' => 'Создание регионов',
            'edit_regions' => 'Редактирование регионов',
            'delete_regions' => 'Удаление регионов',

            // --- Cities
            'list_cities' => 'Список городов',
            'view_cities' => 'Просмотр городов',
            'create_cities' => 'Создание городов',
            'edit_cities' => 'Редактирование городов',
            'delete_cities' => 'Удаление городов',

            // --- Allegories
            'list_allegories' => 'Список аллергий',
            'view_allegories' => 'Просмотр аллергий',
            'create_allegories' => 'Создание аллергий',
            'edit_allegories' => 'Редактирование аллергий',
            'delete_allegories' => 'Удаление аллергий',

            // --- Diseases
            'list_diseases' => 'Список заболеваний',
            'view_diseases' => 'Просмотр заболеваний',
            'create_diseases' => 'Создание заболеваний',
            'edit_diseases' => 'Редактирование заболеваний',
            'delete_diseases' => 'Удаление заболеваний',

            // --- Devices
            'list_devices' => 'Список устройств',
            'view_devices' => 'Просмотр устройств',
            'create_devices' => 'Создание устройств',
            'edit_devices' => 'Редактирование устройств',
            'delete_devices' => 'Удаление устройств',

            // --- Patients
            'list_patients' => 'Список пациентов',
            'view_patients' => 'Просмотр пациентов',
            'create_patients' => 'Создание пациентов',
            'edit_patients' => 'Редактирование пациентов',
            'delete_patients' => 'Удаление пациентов',

            'sister_window_show' => 'Просмотр сестринского окна',
            'patient_inspection_store' => 'Создание осмотра',
            'create_patient_map' => 'Создание карты пациента',

            // --- Maps
            'edit_maps' => 'Редактирование карт',
            'update_maps' => 'Обновление карт',
            'delete_maps' => 'Удаление карт',

            // --- Diagnoses
            'list_diagnoses' => 'Список диагнозов',
            'view_diagnoses' => 'Просмотр диагнозов',
            'create_diagnoses' => 'Создание диагнозов',
            'edit_diagnoses' => 'Редактирование диагнозов',
            'delete_diagnoses' => 'Удаление диагнозов',

            'create_patient_diagnoses' => 'Создание диагноза пациента',
            'list_patient_diagnoses' => 'Список диагнозов пациента',

            // --- Patient Medications
            'create_patient_medication' => 'Назначение медикамента пациенту',
            'edit_patient_medication' => 'Редактирование медикамента пациента',
            'list_patient_medication' => 'Список медикаментов пациента',

            // --- Procedures
            'create_patient_procedure' => 'Назначение процедуры пациенту',

            // --- Household Materials
            'list_household_materials' => 'Список хоз. материалов',
            'view_household_materials' => 'Просмотр хоз. материалов',
            'create_household_materials' => 'Создание хоз. материалов',
            'edit_household_materials' => 'Редактирование хоз. материалов',
            'delete_household_materials' => 'Удаление хоз. материалов',

            'view_cashier' => 'Просмотр кассы',
            'list_cashier' => 'Список кассы',

            'main_warehouse_remain' => 'Просмотр общего остатки склада',

            // offices
            'list_offices' => 'Список кабинетов',
            'create_offices' => 'Создание кабинет',
            'edit_offices' => 'Редактирование кабинет',
            'delete_offices' => 'Удаление кабинет',

            // list_floors

            'list_floors' => 'Список этажей',
            'view_floors' => 'Просмотр этажей',
            'create_floors' => 'Создание этаж',
            'edit_floors' => 'Редактирование этаж',
            'delete_floors' => 'Удаление этаж',
        ];
    }

    public function index(): View
    {
        $positions = StaffPosition::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.staff_positions.index', compact('positions'));
    }

    public function create(): View
    {
        $permissions = Permission::query()->get();
        $permissionsRu = $this->permissionsRu;

        // Группируем разрешения по категориям
        $groupedPermissions = $this->groupPermissions($permissions, $permissionsRu);

        return view('admin.staff_positions.create', compact('permissions', 'permissionsRu', 'groupedPermissions'));
    }

    public function edit(StaffPosition $position): View
    {
        $permissions = Permission::query()->get();
        $permissionsRu = $this->permissionsRu;

        // Группируем разрешения по категориям
        $groupedPermissions = $this->groupPermissions($permissions, $permissionsRu);

        return view('admin.staff_positions.edit', compact('position', 'permissions', 'permissionsRu', 'groupedPermissions'));
    }

    public function store(StaffPositionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('staffs.position.index')->with('success', 'Должность успешно добавлен!');
    }

    public function update(StaffPositionRequest $request, StaffPosition $position): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($position, (array)$validated);

        return redirect()->route('staffs.position.index')->with('success', 'Должность успешно обновлен!');
    }

    public function destroy(StaffPosition $position): RedirectResponse
    {
        if (collect([1, 2, 3, 4, 5, 6, 7])->contains($position->id)) {
            return redirect()->back()->withErrors('Удаление этой должности недоступно!');
        }

        $this->service->destroy($position);

        return redirect()->route('staffs.position.index')->with('success', 'Должность успешно удален!');
    }

    private function groupPermissions($permissions, $permissionsRu): array
    {
        $grouped = [];

        foreach ($permissions as $permission) {
            $name = $permission->name;
            $russianName = $permissionsRu[$name] ?? $name;

            // Определяем категорию на основе префикса разрешения
            $category = 'Общие';
            if (str_starts_with($name, 'view_')) {
                $category = 'Просмотр';
            } elseif (str_starts_with($name, 'create_')) {
                $category = 'Создание';
            } elseif (str_starts_with($name, 'edit_')) {
                $category = 'Редактирование';
            } elseif (str_starts_with($name, 'delete_')) {
                $category = 'Удаление';
            }

            $grouped[$category][] = [
                'id' => $permission->id,
                'name' => $name,
                'russian_name' => $russianName
            ];
        }

        return $grouped;
    }
}
