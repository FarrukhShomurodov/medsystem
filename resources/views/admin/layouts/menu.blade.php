<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <h5 style="margin: 0 !important;">
            <a href="{{ route('dashboard') }}" class="app-brand-link">
                MED ERA
            </a>
        </h5>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx menu-toggle-icon d-none d-xl-block fs-4 align-middle"></i>
            <i class="bx bx-x d-block d-xl-none bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>

    <ul class="menu-inner py-1">
        {{-- Производители --}}
        @can('list_manufacturers')
            <li class="menu-item {{ Request::is('dashboard/manufacturers*') ? 'active' : '' }}">
                <a href="{{ route('manufacturers.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-buildings"></i>
                    <div data-i18n="Производители">Производители</div>
                </a>
            </li>
        @endcan

        @can('list_suppliers')
            <li class="menu-item {{ Request::is('dashboard/suppliers*') ? 'active' : '' }}">
                <a href="{{ route('suppliers.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-box"></i>
                    <div data-i18n="Поставщик">Поставщик</div>
                </a>
            </li>
        @endcan

        {{-- Препараты --}}
        @canany(['list_medications', 'list_medication_groups'])
            <li class="menu-item {{ Request::is('dashboard/medication/list*') || Request::is('dashboard/medication/groups*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-capsule"></i>
                    <div data-i18n="Препараты">Препараты</div>
                </a>
                <ul class="menu-sub">
                    @can('list_medications')
                        <li class="menu-item {{ Request::is('dashboard/medication/list*') ? 'active' : '' }}">
                            <a href="{{route('medications.index')}}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_medication_groups')
                        <li class="menu-item {{ Request::is('dashboard/medication/groups*') ? 'active' : '' }}">
                            <a href="{{route('medication.groups.index')}}" class="menu-link">
                                <div data-i18n="Группы">Группы</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Услуги --}}
        @canany(['list_services', 'list_patient_services', 'list_service_procedures', 'list_service_methods', 'list_service_types', 'list_service_groups'])
            <li class="menu-item {{ Request::is('dashboard/services*')  ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-plus-medical"></i>
                    <div data-i18n="Услуги">Услуги</div>
                </a>
                <ul class="menu-sub">
                    @can('list_services')
                        <li class="menu-item {{Request::is('dashboard/services/list*') ? 'active' : '' }}">
                            <a href="{{route('services.index')}}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_patient_services')
                        <li class="menu-item {{ Request::is('dashboard/services/assign-patient*') ? 'active' : '' }}">
                            <a href="{{route('patient.services.index')}}" class="menu-link">
                                <div data-i18n="Назначеные">Назначеные</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_service_types')
                        <li class="menu-item {{ Request::is('dashboard/services/types*') ? 'active' : '' }}">
                            <a href="{{route('service.types.index')}}" class="menu-link">
                                <div data-i18n="Типы">Типы</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_service_groups')
                        <li class="menu-item {{ Request::is('dashboard/services/groups*') ? 'active' : '' }}">
                            <a href="{{route('service.groups.index')}}" class="menu-link">
                                <div data-i18n="Группы">Группы</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_service_procedures')
                        <li class="menu-item {{ Request::is('dashboard/services/procedures*') ? 'active' : '' }}">
                            <a href="{{route('procedures.index')}}" class="menu-link">
                                <div data-i18n="Процедуры">Процедуры</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_service_methods')
                        <li class="menu-item {{ Request::is('dashboard/services/methods*') ? 'active' : '' }}">
                            <a href="{{route('service.methods.index')}}" class="menu-link">
                                <div data-i18n="Методы">Методы</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['list_equipments', 'list_equipment_types'])
            <li class="menu-item {{ Request::is('dashboard/equipments*') || Request::is('dashboard/equipment-types*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-wrench"></i>
                    <div data-i18n="Оборудование">Оборудование</div>
                </a>
                <ul class="menu-sub">
                    @can('list_departments')
                        <li class="menu-item {{ Request::is('dashboard/equipments') ? 'active' : '' }}">
                            <a href="{{route('equipment.index')}}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_equipment_types')
                        <li class="menu-item {{ Request::is('dashboard/equipment-types*') ? 'active' : '' }}">
                            <a href="{{route('equipment.types.index')}}" class="menu-link">
                                <div data-i18n="Типы">Типы</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Отделение --}}
        @canany(['list_departments', 'list_department_rooms'])
            <li class="menu-item {{ Request::is('dashboard/departments*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-buildings"></i>
                    <div data-i18n="Отделение">Отделение</div>
                </a>
                <ul class="menu-sub">
                    @can('list_departments')
                        <li class="menu-item {{ Request::is('dashboard/departments') || Request::is('dashboard/departments/create') || Request::is('dashboard/departments/*/edit') ? 'active' : '' }}">
                            <a href="{{route('departments.index')}}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_department_rooms')
                        <li class="menu-item {{ Request::is('dashboard/departments/rooms*') ? 'active' : '' }}">
                            <a href="{{route('departments.room.index')}}" class="menu-link">
                                <div data-i18n="Палаты">Палаты</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Опросники --}}
        @can('list_surveys')
            <li class="menu-item {{ Request::is('dashboard/surveys*') ? 'active' : '' }}">
                <a href="{{ route('surveys.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-list-check"></i>
                    <div data-i18n="Мед. документы">Мед. документы</div>
                </a>
            </li>
        @endcan

        {{-- Персонал --}}
        @canany(['list_staffs', 'list_staff_positions', 'list_specialties'])
            <li class="menu-item {{ Request::is('dashboard/staffs*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-user"></i>
                    <div data-i18n="Персонал">Персонал</div>
                </a>
                <ul class="menu-sub">
                    @can('list_staffs')
                        <li class="menu-item {{ Request::is('dashboard/staffs/list*') ? 'active' : '' }}">
                            <a href="{{ route('staffs.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_staff_positions')
                        <li class="menu-item {{ Request::is('dashboard/staffs/positions*') ? 'active' : '' }}">
                            <a href="{{ route('staffs.position.index') }}" class="menu-link">
                                <div data-i18n="Должности">Должности</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_specialties')
                        <li class="menu-item {{ Request::is('dashboard/staffs/specialties*') ? 'active' : '' }}">
                            <a href="{{ route('specialties.index') }}" class="menu-link">
                                <div data-i18n="Специальности">Специальности</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Области --}}
        @canany(['list_regions', 'list_cities'])
            <li class="menu-item {{ Request::is('dashboard/regions*') || Request::is('dashboard/cities*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-map"></i>
                    <div data-i18n="Области">Области</div>
                </a>
                <ul class="menu-sub">
                    @can('list_regions')
                        <li class="menu-item {{ Request::is('dashboard/regions*') ? 'active' : '' }}">
                            <a href="{{ route('regions.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_cities')
                        <li class="menu-item {{ Request::is('dashboard/cities*') ? 'active' : '' }}">
                            <a href="{{ route('cities.index') }}" class="menu-link">
                                <div data-i18n="Города">Города</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @can('list_devices')
            <li class="menu-item {{ Request::is('dashboard/devices*') ? 'active' : '' }}">
                <a href="{{ route('devices.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-chip"></i>
                    <div data-i18n="Медицинские устройства">Мед. устройства</div>
                </a>
            </li>
        @endcan

        @can('list_diagnoses')
            <li class="menu-item {{ Request::is('dashboard/diagnoses*') ? 'active' : '' }}">
                <a href="{{ route('diagnoses.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-buildings"></i>
                    <div data-i18n="Производители">Диагнозы</div>
                </a>
            </li>
        @endcan


        @can('list_patients')
            <li class="menu-item {{ Request::is('dashboard/patients*') || Request::is('dashboard/maps*') || Request::is('dashboard/survey-response*') ? 'active' : '' }}">
                <a href="{{ route('patients.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-group"></i>
                    <div data-i18n="Пациенты">Пациенты</div>
                </a>
            </li>
        @endcan

        @can('list_cashier')
            <li class="menu-item {{ Request::is('dashboard/cashier*') ? 'active' : '' }}">
                <a href="{{ route('cashier.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-group"></i>
                    <div data-i18n="Касса">Касса</div>
                </a>
            </li>
        @endcan

        @can('sister_window_show')
            <li class="menu-item {{ Request::is('dashboard/sister-window*')  ? 'active' : '' }}">
                <a href="{{ route('sister.window.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-clinic"></i>
                    <div data-i18n="Сестринское окно">Сестринское окно</div>
                </a>
            </li>
        @endcan

        {{-- Прием --}}
        @canany(['list_receptions', 'list_appointment_receptions'])
            <li class="menu-item {{ Request::is('dashboard/receptions/list') || Request::is('dashboard/receptions-appointment') || Request::is('dashboard/receptions-doctor-calendar/*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-calendar"></i>
                    <div data-i18n="Прием">Прием</div>
                </a>
                <ul class="menu-sub">
                    @can('list_receptions')
                        <li class="menu-item {{ Request::is('dashboard/receptions/list') ? 'active' : '' }}">
                            <a href="{{ route('receptions.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_appointment_receptions')
                        <li class="menu-item {{ Request::is('dashboard/receptions-appointment') ? 'active' : '' }}">
                            <a href="{{ route('receptions.appointment') }}" class="menu-link">
                                <div data-i18n="Назначить">Назначить</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany


        {{-- Общий склад --}}
        <li class="menu-item {{ Request::is('dashboard/main-warehouse*') || Request::is('dashboard/supply-requests/orders') ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-store"></i>
                <div data-i18n="Общий склад">Общий склад</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('dashboard/main-warehouse*') ? 'active' : '' }}">
                    <a href="{{ route('main.warehouse.remains') }}" class="menu-link">
                        <div data-i18n="Остатки">Остатки</div>
                    </a>
                </li>

                <li class="menu-item {{ Request::is('dashboard/supply-requests/orders') ? 'active' : '' }}">
                    <a href="{{ route('supply-requests.index') }}" class="menu-link">
                        <div data-i18n="Переходы">Переходы</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Склад --}}
        @canany(['list_warehouses', 'list_warehouse_medications', 'list_warehouse_supplies', 'list_warehouse_types'])
            <li class="menu-item {{ Request::is('dashboard/warehouses*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-store"></i>
                    <div data-i18n="Склад">Склад</div>
                </a>
                <ul class="menu-sub">
                    @can('list_warehouses')
                        <li class="menu-item {{ Request::is('dashboard/warehouses/list*') ? 'active' : '' }}">
                            <a href="{{ route('warehouses.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan

                    <li class="menu-item {{ Request::is('dashboard/warehouses/remains-show') || Request::is('dashboard/warehouses/order-show') ? 'active' : '' }}">
                        <a href="{{ route('warehouse.remains') }}" class="menu-link">
                            <div data-i18n="Остатки">Остатки</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcanany

        {{-- Аптека --}}
        @canany(['list_pharmacies', 'list_pharmacy_types', 'list_transfers'])
            <li class="menu-item {{ Request::is('dashboard/pharmacy*') || Request::is('dashboard/transfers*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-clinic"></i>
                    <div data-i18n="Аптека">Аптека</div>
                </a>
                <ul class="menu-sub">
                    @can('list_pharmacies')
                        <li class="menu-item {{ Request::is('dashboard/pharmacy*') && !Request::is('dashboard/pharmacy/types*') ? 'active' : '' }}">
                            <a href="{{ route('pharmacies.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_pharmacy_types')
                        <li class="menu-item {{ Request::is('dashboard/pharmacy/types*') ? 'active' : '' }}">
                            <a href="{{ route('pharmacy.types.index') }}" class="menu-link">
                                <div data-i18n="Типы">Типы</div>
                            </a>
                        </li>
                    @endcan
                    @can('list_transfers')
                        <li class="menu-item {{ Request::is('dashboard/transfers*') ? 'active' : '' }}">
                            <a href="{{ route('transfers.index') }}" class="menu-link">
                                <div data-i18n="Перевод препаратов">Перевод препаратов</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @can('list_supplies')
            <li class="menu-item {{ Request::is('dashboard/supplies*') ? 'active' : '' }}">
                <a href="{{ route('supplies.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-package"></i>
                    <div data-i18n="Расходные материалы">Расходные материалы</div>
                </a>
            </li>
        @endcan

        @can('list_allegories')
            <li class="menu-item {{ Request::is('dashboard/allegories*') ? 'active' : '' }}">
                <a href="{{ route('allegories.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-book"></i>
                    <div data-i18n="Аллегрии">Аллегрии</div>
                </a>
            </li>
        @endcan

        @can('list_diseases')
            <li class="menu-item {{ Request::is('dashboard/diseases*') ? 'active' : '' }}">
                <a href="{{ route('diseases.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-heart"></i>
                    <div data-i18n="Хронические белезни">Хронические белезни</div>
                </a>
            </li>
        @endcan


        @can('list_household_materials')
            <li class="menu-item {{ Request::is('dashboard/household-materials*') ? 'active' : '' }}">
                <a href="{{ route('household.materials.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-box"></i>
                    <div data-i18n="Хозяйственные материалы">Хозяйственные материалы</div>
                </a>
            </li>
        @endcan
    </ul>
</aside>
<div class="layout-overlay layout-menu-toggle"></div>
