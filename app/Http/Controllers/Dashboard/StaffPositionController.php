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
        $this->middleware(['permission:view_staff_positions'])->only('index');
        $this->middleware(['permission:create_staff_positions'])->only(['create', 'store']);
        $this->middleware(['permission:edit_staff_positions'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_staff_positions'])->only('destroy');

        $this->service = $service;

        $this->permissionsRu = ['view_dashboard' => 'Просмотр панели управления',

            'view_admins' => 'Просмотр администраторов',
            'create_admins' => 'Создание администраторов',
            'edit_admins' => 'Редактирование администраторов',
            'delete_admins' => 'Удаление администраторов',

            'view_specialties' => 'Просмотр специальностей',
            'create_specialties' => 'Создание специальностей',
            'edit_specialties' => 'Редактирование специальностей',
            'delete_specialties' => 'Удаление специальностей',

            'view_departments' => 'Просмотр отделений',
            'create_departments' => 'Создание отделений',
            'edit_departments' => 'Редактирование отделений',
            'delete_departments' => 'Удаление отделений',

            'view_department_types' => 'Просмотр типов отделений',
            'create_department_types' => 'Создание типов отделений',
            'edit_department_types' => 'Редактирование типов отделений',
            'delete_department_types' => 'Удаление типов отделений',

            'view_department_rooms' => 'Просмотр помещений отделений',
            'create_department_rooms' => 'Создание помещений отделений',
            'edit_department_rooms' => 'Редактирование помещений отделений',
            'delete_department_rooms' => 'Удаление помещений отделений',

            'view_equipments' => 'Просмотр оборудования',
            'create_equipments' => 'Создание оборудования',
            'edit_equipments' => 'Редактирование оборудования',
            'delete_equipments' => 'Удаление оборудования',

            'view_staffs' => 'Просмотр персонала',
            'create_staffs' => 'Добавление персонала',
            'edit_staffs' => 'Редактирование персонала',
            'delete_staffs' => 'Удаление персонала',

            'view_staff_positions' => 'Просмотр должностей персонала',
            'create_staff_positions' => 'Создание должностей персонала',
            'edit_staff_positions' => 'Редактирование должностей персонала',
            'delete_staff_positions' => 'Удаление должностей персонала',

            'view_supplies' => 'Просмотр расходников',
            'create_supplies' => 'Создание расходников',
            'edit_supplies' => 'Редактирование расходников',
            'delete_supplies' => 'Удаление расходников',

            'view_receptions' => 'Просмотр приемов',
            'create_receptions' => 'Создание приемов',
            'edit_receptions' => 'Редактирование приемов',
            'delete_receptions' => 'Удаление приемов',
            'view_reception_doctor_calendar' => 'Просмотр календаря врача',

            'view_suppliers' => 'Просмотр поставщиков',
            'create_suppliers' => 'Создание поставщиков',
            'edit_suppliers' => 'Редактирование поставщиков',
            'delete_suppliers' => 'Удаление поставщиков',

            'view_manufacturers' => 'Просмотр производителей',
            'create_manufacturers' => 'Создание производителей',
            'edit_manufacturers' => 'Редактирование производителей',
            'delete_manufacturers' => 'Удаление производителей',

            'view_warehouses' => 'Просмотр складов',
            'create_warehouses' => 'Создание складов',
            'edit_warehouses' => 'Редактирование складов',
            'delete_warehouses' => 'Удаление складов',

            'view_warehouse_types' => 'Просмотр типов складов',
            'create_warehouse_types' => 'Создание типов складов',
            'edit_warehouse_types' => 'Редактирование типов складов',
            'delete_warehouse_types' => 'Удаление типов складов',

            'view_warehouse_medications' => 'Просмотр медикаментов на складе',
            'view_warehouse_supplies' => 'Просмотр расходников на складе',
            'view_warehouse_medication_detail' => 'Просмотр деталей медикамента на складе',

            'view_pharmacies' => 'Просмотр аптек',
            'create_pharmacies' => 'Создание аптек',
            'edit_pharmacies' => 'Редактирование аптек',
            'delete_pharmacies' => 'Удаление аптек',

            'view_pharmacy_types' => 'Просмотр типов аптек',
            'create_pharmacy_types' => 'Создание типов аптек',
            'edit_pharmacy_types' => 'Редактирование типов аптек',
            'delete_pharmacy_types' => 'Удаление типов аптек',

            'view_pharmacy_medications' => 'Просмотр медикаментов в аптеках',

            'view_transfers' => 'Просмотр перемещений',
            'create_transfers' => 'Создание перемещений',
            'edit_transfers' => 'Редактирование перемещений',
            'delete_transfers' => 'Удаление перемещений',

            'view_transfer_medications' => 'Просмотр медикаментов в перемещении',

            'view_surveys' => 'Просмотр опросов',
            'create_surveys' => 'Создание опросов',
            'edit_surveys' => 'Редактирование опросов',
            'delete_surveys' => 'Удаление опросов',

            'create_survey_response' => 'Создание ответа на опрос',
            'store_survey_response' => 'Сохранение ответа на опрос',

            'view_analyzes' => 'Просмотр анализов',
            'create_analyzes' => 'Создание анализов',
            'edit_analyzes' => 'Редактирование анализов',
            'delete_analyzes' => 'Удаление анализов',

            'create_analyze_result' => 'Создание результата анализа',
            'store_analyze_result' => 'Сохранение результата анализа',
            'show_analyze_result' => 'Просмотр результата анализа',
            'edit_analyze_result' => 'Редактирование результата анализа',
            'update_analyze_result' => 'Обновление результата анализа',

            'view_medications' => 'Просмотр медикаментов',
            'create_medications' => 'Создание медикаментов',
            'edit_medications' => 'Редактирование медикаментов',
            'delete_medications' => 'Удаление медикаментов',

            'view_medication_groups' => 'Просмотр групп медикаментов',
            'create_medication_groups' => 'Создание групп медикаментов',
            'edit_medication_groups' => 'Редактирование групп медикаментов',
            'delete_medication_groups' => 'Удаление групп медикаментов',

            'view_services' => 'Просмотр услуг',
            'create_services' => 'Создание услуг',
            'edit_services' => 'Редактирование услуг',
            'delete_services' => 'Удаление услуг',

            'view_patient_services' => 'Просмотр услуг пациента',
            'create_patient_services' => 'Создание услуг пациента',
            'edit_patient_services' => 'Редактирование услуг пациента',
            'delete_patient_services' => 'Удаление услуг пациента',

            'view_service_categories' => 'Просмотр категорий услуг',
            'create_service_categories' => 'Создание категорий услуг',
            'edit_service_categories' => 'Редактирование категорий услуг',
            'delete_service_categories' => 'Удаление категорий услуг',

            'view_service_procedures' => 'Просмотр процедур',
            'create_service_procedures' => 'Создание процедур',
            'edit_service_procedures' => 'Редактирование процедур',
            'delete_service_procedures' => 'Удаление процедур',

            'view_service_methods' => 'Просмотр методов услуг',
            'create_service_methods' => 'Создание методов услуг',
            'edit_service_methods' => 'Редактирование методов услуг',
            'delete_service_methods' => 'Удаление методов услуг',

            'view_laboratories' => 'Просмотр лабораторий',
            'create_laboratories' => 'Создание лабораторий',
            'edit_laboratories' => 'Редактирование лабораторий',
            'delete_laboratories' => 'Удаление лабораторий',

            'view_analyze_methods' => 'Просмотр методов анализов',
            'create_analyze_methods' => 'Создание методов анализов',
            'edit_analyze_methods' => 'Редактирование методов анализов',
            'delete_analyze_methods' => 'Удаление методов анализов',

            'view_analyze_types' => 'Просмотр типов анализов',
            'create_analyze_types' => 'Создание типов анализов',
            'edit_analyze_types' => 'Редактирование типов анализов',
            'delete_analyze_types' => 'Удаление типов анализов',

            'view_regions' => 'Просмотр регионов',
            'create_regions' => 'Создание регионов',
            'edit_regions' => 'Редактирование регионов',
            'delete_regions' => 'Удаление регионов',

            'view_cities' => 'Просмотр городов',
            'create_cities' => 'Создание городов',
            'edit_cities' => 'Редактирование городов',
            'delete_cities' => 'Удаление городов',

            'view_allegories' => 'Просмотр аллергий',
            'create_allegories' => 'Создание аллергий',
            'edit_allegories' => 'Редактирование аллергий',
            'delete_allegories' => 'Удаление аллергий',

            'view_diseases' => 'Просмотр заболеваний',
            'create_diseases' => 'Создание заболеваний',
            'edit_diseases' => 'Редактирование заболеваний',
            'delete_diseases' => 'Удаление заболеваний',

            'view_devices' => 'Просмотр устройств',
            'create_devices' => 'Создание устройств',
            'edit_devices' => 'Редактирование устройств',
            'delete_devices' => 'Удаление устройств',

            'view_patients' => 'Просмотр пациентов',
            'create_patients' => 'Создание пациентов',
            'edit_patients' => 'Редактирование пациентов',
            'delete_patients' => 'Удаление пациентов',

            'edit_maps' => 'Редактирование карт',
            'update_maps' => 'Обновление карт',
            'delete_maps' => 'Удаление карт',

            'create_patient_map' => 'Создание карты пациента',
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

        return view('admin.staff_positions.create', compact('permissions', 'permissionsRu'));
    }

    public function store(StaffPositionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('staffs.position.index')->with('success', 'Должность успешно добавлен!');
    }

    public function edit(StaffPosition $position): View
    {
        $permissions = Permission::query()->get();
        $permissionsRu = $this->permissionsRu;

        return view('admin.staff_positions.edit', compact('position', 'permissions', 'permissionsRu'));
    }

    public function update(StaffPositionRequest $request, StaffPosition $position): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($position, (array)$validated);

        return redirect()->route('staffs.position.index')->with('success', 'Должность успешно обновлен!');
    }

    public function destroy(StaffPosition $position): RedirectResponse
    {
        if (collect([1, 2, 3, 4])->contains($position->id)) {
            return redirect()->back()->withErrors('Удаление этой должности недоступно!');
        }

        $this->service->destroy($position);

        return redirect()->route('staffs.position.index')->with('success', 'Должность успешно удален!');
    }
}
