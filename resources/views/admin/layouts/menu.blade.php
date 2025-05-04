<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <h5 style="margin: 0 !important;">
            <a href="{{ route('dashboard') }}" class="app-brand-link">
                M.S.
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
        @can('view_manufacturers')
            <li class="menu-item {{ Request::is('dashboard/manufacturers*') ? 'active' : '' }}">
                <a href="{{ route('manufacturers.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-buildings"></i>
                    <div data-i18n="Производители">Производители</div>
                </a>
            </li>
        @endcan

        @can('view_suppliers')
            <li class="menu-item {{ Request::is('dashboard/suppliers*') ? 'active' : '' }}">
                <a href="{{ route('suppliers.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-box"></i>
                    <div data-i18n="Поставщик">Поставщик</div>
                </a>
            </li>
        @endcan

        {{-- Препараты --}}
        @canany(['view_medications', 'view_medication_groups'])
            <li class="menu-item {{ Request::is('dashboard/medication/list*') || Request::is('dashboard/medication/groups*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-capsule"></i>
                    <div data-i18n="Препараты">Препараты</div>
                </a>
                <ul class="menu-sub">
                    @can('view_medications')
                        <li class="menu-item {{ Request::is('dashboard/medication/list*') ? 'active' : '' }}">
                            <a href="{{route('medications.index')}}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_medication_groups')
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
        @canany(['view_services', 'view_patient_services', 'view_service_procedures', 'view_service_methods', 'view_service_types', 'view_service_groups'])
            <li class="menu-item {{ Request::is('dashboard/services*')  ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-plus-medical"></i>
                    <div data-i18n="Услуги">Услуги</div>
                </a>
                <ul class="menu-sub">
                    @can('view_services')
                        <li class="menu-item {{Request::is('dashboard/services/list*') ? 'active' : '' }}">
                            <a href="{{route('services.index')}}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_patient_services')
                        <li class="menu-item {{ Request::is('dashboard/services/assign-patient*') ? 'active' : '' }}">
                            <a href="{{route('patient.services.index')}}" class="menu-link">
                                <div data-i18n="Назначеные">Назначеные</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_service_types')
                        <li class="menu-item {{ Request::is('dashboard/services/types*') ? 'active' : '' }}">
                            <a href="{{route('service.types.index')}}" class="menu-link">
                                <div data-i18n="Типы">Типы</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_service_groups')
                        <li class="menu-item {{ Request::is('dashboard/services/groups*') ? 'active' : '' }}">
                            <a href="{{route('service.groups.index')}}" class="menu-link">
                                <div data-i18n="Группы">Группы</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_service_procedures')
                        <li class="menu-item {{ Request::is('dashboard/services/procedures*') ? 'active' : '' }}">
                            <a href="{{route('procedures.index')}}" class="menu-link">
                                <div data-i18n="Процедуры">Процедуры</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_service_methods')
                        <li class="menu-item {{ Request::is('dashboard/services/methods*') ? 'active' : '' }}">
                            <a href="{{route('service.methods.index')}}" class="menu-link">
                                <div data-i18n="Методы">Методы</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @can('view_equipments')
            <li class="menu-item {{ Request::is('dashboard/equipments*') ? 'active' : '' }}">
                <a href="{{ route('equipment.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-first-aid"></i>
                    <div data-i18n="Оборудование">Оборудование</div>
                </a>
            </li>
        @endcan

        {{-- Отделение --}}
        @canany(['view_departments', 'view_department_rooms'])
            <li class="menu-item {{ Request::is('dashboard/departments*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-buildings"></i>
                    <div data-i18n="Отделение">Отделение</div>
                </a>
                <ul class="menu-sub">
                    @can('view_departments')
                        <li class="menu-item {{ Request::is('dashboard/departments') || Request::is('dashboard/departments/create') || Request::is('dashboard/departments/*/edit') ? 'active' : '' }}">
                            <a href="{{route('departments.index')}}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_department_rooms')
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
        @can('view_surveys')
            <li class="menu-item {{ Request::is('dashboard/surveys*') ? 'active' : '' }}">
                <a href="{{ route('surveys.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-list-check"></i>
                    <div data-i18n="Мед. документы">Мед. документы</div>
                </a>
            </li>
        @endcan

        {{-- Персонал --}}
        @canany(['view_staffs', 'view_staff_positions', 'view_specialties'])
            <li class="menu-item {{ Request::is('dashboard/staffs*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-user"></i>
                    <div data-i18n="Персонал">Персонал</div>
                </a>
                <ul class="menu-sub">
                    @can('view_staffs')
                        <li class="menu-item {{ Request::is('dashboard/staffs/list*') ? 'active' : '' }}">
                            <a href="{{ route('staffs.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_staff_positions')
                        <li class="menu-item {{ Request::is('dashboard/staffs/positions*') ? 'active' : '' }}">
                            <a href="{{ route('staffs.position.index') }}" class="menu-link">
                                <div data-i18n="Должности">Должности</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_specialties')
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
        @canany(['view_regions', 'view_cities'])
            <li class="menu-item {{ Request::is('dashboard/regions*') || Request::is('dashboard/cities*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-map"></i>
                    <div data-i18n="Области">Области</div>
                </a>
                <ul class="menu-sub">
                    @can('view_regions')
                        <li class="menu-item {{ Request::is('dashboard/regions*') ? 'active' : '' }}">
                            <a href="{{ route('regions.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_cities')
                        <li class="menu-item {{ Request::is('dashboard/cities*') ? 'active' : '' }}">
                            <a href="{{ route('cities.index') }}" class="menu-link">
                                <div data-i18n="Города">Города</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @can('view_devices')
            <li class="menu-item {{ Request::is('dashboard/devices*') ? 'active' : '' }}">
                <a href="{{ route('devices.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-chip"></i>
                    <div data-i18n="Медицинские устройства">Мед. устройства</div>
                </a>
            </li>
        @endcan

        @can('view_patients')
            <li class="menu-item {{ Request::is('dashboard/patients*') || Request::is('dashboard/maps*') || Request::is('dashboard/survey-response*') ? 'active' : '' }}">
                <a href="{{ route('patients.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-group"></i>
                    <div data-i18n="Пациенты">Пациенты</div>
                </a>
            </li>
        @endcan

        {{-- Прием --}}
        @canany(['view_receptions', 'view_appointment_receptions'])
            <li class="menu-item {{ Request::is('dashboard/receptions/list') || Request::is('dashboard/receptions-appointment') || Request::is('dashboard/receptions-doctor-calendar/*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-calendar"></i>
                    <div data-i18n="Прием">Прием</div>
                </a>
                <ul class="menu-sub">
                    @can('view_receptions')
                        <li class="menu-item {{ Request::is('dashboard/receptions/list') ? 'active' : '' }}">
                            <a href="{{ route('receptions.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_appointment_receptions')
                        <li class="menu-item {{ Request::is('dashboard/receptions-appointment') ? 'active' : '' }}">
                            <a href="{{ route('receptions.appointment') }}" class="menu-link">
                                <div data-i18n="Назначить">Назначить</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Анализы --}}
        @canany(['view_analyzes', 'view_laboratories', 'view_analyze_methods', 'view_analyze_types'])
            <li class="menu-item {{ Request::is('dashboard/analyzes*') || Request::is('dashboard/laboratories*') || Request::is('dashboard/methods*') || Request::is('dashboard/types*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-dna"></i>
                    <div data-i18n="Анализы">Анализы</div>
                </a>
                <ul class="menu-sub">
                    @can('view_analyzes')
                        <li class="menu-item {{ Request::is('dashboard/analyzes*') ? 'active' : '' }}">
                            <a href="{{ route('analyzes.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_laboratories')
                        <li class="menu-item {{ Request::is('dashboard/laboratories*') ? 'active' : '' }}">
                            <a href="{{ route('laboratories.index') }}" class="menu-link">
                                <div data-i18n="Лаборатории">Лаборатории</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_analyze_methods')
                        <li class="menu-item {{ Request::is('dashboard/methods*') ? 'active' : '' }}">
                            <a href="{{ route('methods.index') }}" class="menu-link">
                                <div data-i18n="Методы">Методы</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_analyze_types')
                        <li class="menu-item {{ Request::is('dashboard/types*') ? 'active' : '' }}">
                            <a href="{{ route('types.index') }}" class="menu-link">
                                <div data-i18n="Типы">Типы</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Склад --}}
        @canany(['view_warehouses', 'view_warehouse_medications', 'view_warehouse_supplies', 'view_warehouse_types'])
            <li class="menu-item {{ Request::is('dashboard/warehouses*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-store"></i>
                    <div data-i18n="Склад">Склад</div>
                </a>
                <ul class="menu-sub">
                    @can('view_warehouses')
                        <li class="menu-item {{ Request::is('dashboard/warehouses/list*') ? 'active' : '' }}">
                            <a href="{{ route('warehouses.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_warehouse_medications')
                        <li class="menu-item {{ Request::is('dashboard/warehouses/medications*') ? 'active' : '' }}">
                            <a href="{{ route('warehouses.medications') }}" class="menu-link">
                                <div data-i18n="Препараты">Препараты</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_warehouse_supplies')
                        <li class="menu-item {{ Request::is('dashboard/warehouses/supplies*') ? 'active' : '' }}">
                            <a href="{{ route('warehouses.supplies') }}" class="menu-link">
                                <div data-i18n="Расходные материалы">Расходные материалы</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_warehouse_types')
                        <li class="menu-item {{ Request::is('dashboard/warehouses/types*') ? 'active' : '' }}">
                            <a href="{{ route('warehouses.type.index') }}" class="menu-link">
                                <div data-i18n="Типы">Типы</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Аптека --}}
        @canany(['view_pharmacies', 'view_pharmacy_types', 'view_transfers'])
            <li class="menu-item {{ Request::is('dashboard/pharmacy*') || Request::is('dashboard/transfers*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon bx bx-clinic"></i>
                    <div data-i18n="Аптека">Аптека</div>
                </a>
                <ul class="menu-sub">
                    @can('view_pharmacies')
                        <li class="menu-item {{ Request::is('dashboard/pharmacy*') && !Request::is('dashboard/pharmacy/types*') ? 'active' : '' }}">
                            <a href="{{ route('pharmacies.index') }}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_pharmacy_types')
                        <li class="menu-item {{ Request::is('dashboard/pharmacy/types*') ? 'active' : '' }}">
                            <a href="{{ route('pharmacy.types.index') }}" class="menu-link">
                                <div data-i18n="Типы">Типы</div>
                            </a>
                        </li>
                    @endcan
                    @can('view_transfers')
                        <li class="menu-item {{ Request::is('dashboard/transfers*') ? 'active' : '' }}">
                            <a href="{{ route('transfers.index') }}" class="menu-link">
                                <div data-i18n="Перевод препаратов">Перевод препаратов</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @can('view_supplies')
            <li class="menu-item {{ Request::is('dashboard/supplies*') ? 'active' : '' }}">
                <a href="{{ route('supplies.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-package"></i>
                    <div data-i18n="Расходные материалы">Расходные материалы</div>
                </a>
            </li>
        @endcan

        @can('view_allegories')
            <li class="menu-item {{ Request::is('dashboard/allegories*') ? 'active' : '' }}">
                <a href="{{ route('allegories.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-book"></i>
                    <div data-i18n="Аллегрии">Аллегрии</div>
                </a>
            </li>
        @endcan

        @can('view_diseases')
            <li class="menu-item {{ Request::is('dashboard/diseases*') ? 'active' : '' }}">
                <a href="{{ route('diseases.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-heart"></i>
                    <div data-i18n="Хронические белезни">Хронические белезни</div>
                </a>
            </li>
        @endcan
    </ul>
</aside>
<div class="layout-overlay layout-menu-toggle"></div>
