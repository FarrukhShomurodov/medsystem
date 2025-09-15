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
        @can('view_manufacturers')
            @can('list_manufacturers')
                <li class="menu-item {{ Request::is('dashboard/manufacturers*') ? 'active' : '' }}">
                    <a href="{{ route('manufacturers.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-buildings"></i>
                        <div data-i18n="Производители">Производители</div>
                        <div>Производители</div>
                    </a>
                </li>
            @endcan

            @can('view_suppliers')
                {{-- Поставщики --}}
                @can('list_suppliers')
                    <li class="menu-item {{ Request::is('dashboard/suppliers*') ? 'active' : '' }}">
                        <a href="{{ route('suppliers.index') }}" class="menu-link">
                            <i class="menu-icon bx bx-box"></i>
                            <div data-i18n="Поставщик">Поставщик</div>
                            <div>Поставщики</div>
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
                @can('list_medications')
                    <li class="menu-item {{ Request::is('dashboard/medication/list*') ? 'active' : '' }}">
                        <a href="{{ route('medications.index') }}" class="menu-link">
                            <i class="menu-icon bx bx-capsule"></i>
                            <div>Препараты</div>
                        </a>
                    </li>
                @endcan

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

                @can('list_services')
                    <li class="menu-item {{ Request::is('dashboard/services/list*') ? 'active' : '' }}">
                        <a href="{{ route('services.index') }}" class="menu-link">
                            <i class="menu-icon bx bx-plus-medical"></i>
                            <div>Услуги</div>
                        </a>
                    </li>
                @endcan

                @canany(['view_equipments', 'view_equipment_types'])
                    <li class="menu-item {{ Request::is('dashboard/equipments*') || Request::is('dashboard/equipment-types*') ? 'open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            {{-- Оборудование --}}
                            @can('list_equipments')
                                <li class="menu-item {{ Request::is('dashboard/equipments*') ? 'active' : '' }}">
                                    <a href="{{ route('equipment.index') }}" class="menu-link">
                                        <i class="menu-icon bx bx-wrench"></i>
                                        <div data-i18n="Оборудование">Оборудование</div>
                                        <div>Оборудование</div>
                                    </a>
                                    <ul class="menu-sub">
                                        @can('view_departments')
                                            <li class="menu-item {{ Request::is('dashboard/equipments') ? 'active' : '' }}">
                                                <a href="{{route('equipment.index')}}" class="menu-link">
                                                    <div data-i18n="Список">Список</div>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('view_equipment_types')
                                            <li class="menu-item {{ Request::is('dashboard/equipment-types*') ? 'active' : '' }}">
                                                <a href="{{route('equipment.types.index')}}" class="menu-link">
                                                    <div data-i18n="Типы">Типы</div>
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                        @endcanany
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
                {{-- Отделения --}}
                @can('list_departments')
                    <li class="menu-item {{ Request::is('dashboard/departments*') ? 'active' : '' }}">
                        <a href="{{ route('departments.index') }}" class="menu-link">
                            <i class="menu-icon bx bx-buildings"></i>
                            <div>Отделения</div>
                        </a>
                    </li>
                @endcan

                {{-- Опросники --}}
                @can('view_surveys')
                    {{-- Мед. документы --}}
                    @can('list_surveys')
                        <li class="menu-item {{ Request::is('dashboard/surveys*') ? 'active' : '' }}">
                            <a href="{{ route('surveys.index') }}" class="menu-link">
                                <i class="menu-icon bx bx-list-check"></i>
                                <div data-i18n="Мед. документы">Мед. документы</div>
                                <div>Мед. документы</div>
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

                    @can('list_staffs')
                        <li class="menu-item {{ Request::is('dashboard/staffs/list*') ? 'active' : '' }}">
                            <a href="{{ route('staffs.index') }}" class="menu-link">
                                <i class="menu-icon bx bx-user"></i>
                                <div>Персонал</div>
                            </a>
                        </li>
                    @endcan

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

                    @can('list_regions')
                        <li class="menu-item {{ Request::is('dashboard/regions*') ? 'active' : '' }}">
                            <a href="{{ route('regions.index') }}" class="menu-link">
                                <i class="menu-icon bx bx-map"></i>
                                <div>Области</div>
                            </a>
                        </li>
                    @endcan

                    @can('view_devices')
                        {{-- Медицинские устройства --}}
                        @can('list_devices')
                            <li class="menu-item {{ Request::is('dashboard/devices*') ? 'active' : '' }}">
                                <a href="{{ route('devices.index') }}" class="menu-link">
                                    <i class="menu-icon bx bx-chip"></i>
                                    <div data-i18n="Медицинские устройства">Мед. устройства</div>
                                    <div>Мед. устройства</div>
                                </a>
                            </li>
                        @endcan

                        @can('view_diagnoses')
                            {{-- Диагнозы --}}
                            @can('list_diagnoses')
                                <li class="menu-item {{ Request::is('dashboard/diagnoses*') ? 'active' : '' }}">
                                    <a href="{{ route('diagnoses.index') }}" class="menu-link">
                                        <i class="menu-icon bx bx-buildings"></i>
                                        <div data-i18n="Производители">Диагнозы</div>
                                        <i class="menu-icon bx bx-notepad"></i>
                                        <div>Диагнозы</div>
                                    </a>
                                </li>
                            @endcan


                            @can('view_patients')
                                <li class="menu-item {{ Request::is('dashboard/patients*') || Request::is('dashboard/maps*') || Request::is('dashboard/survey-response*') ? 'active' : '' }}">
                                {{-- Пациенты --}}
                                @can('list_patients')
                                    <li class="menu-item {{ Request::is('dashboard/patients*') ? 'active' : '' }}">
                                        <a href="{{ route('patients.index') }}" class="menu-link">
                                            <i class="menu-icon bx bx-group"></i>
                                            <div data-i18n="Пациенты">Пациенты</div>
                                            <div>Пациенты</div>
                                        </a>
                                    </li>
                                @endcan

                                {{--        @can('view_cashier')--}}
                                {{-- Касса --}}
                                @can('list_cashier')
                                    <li class="menu-item {{ Request::is('dashboard/cashier*') ? 'active' : '' }}">
                                        <a href="{{ route('cashier.index') }}" class="menu-link">
                                            <i class="menu-icon bx bx-group"></i>
                                            <div data-i18n="Касса">Касса</div>
                                            <i class="menu-icon bx bx-credit-card"></i>
                                            <div>Касса</div>
                                        </a>
                                    </li>
                                    {{--        @endcan--}}
                                @endcan

                                <li class="menu-item {{ Request::is('dashboard/sister-window*')  ? 'active' : '' }}">
                                    <a href="{{ route('sister.window.index') }}" class="menu-link">
                                        <i class="menu-icon bx bx-clinic"></i>
                                        <div data-i18n="Сестринское окно">Сестринское окно</div>
                                    </a>
                                </li>
                                {{-- Сестринское окно --}}
                                @can('sister_window_show')
                                    <li class="menu-item {{ Request::is('dashboard/sister-window*') ? 'active' : '' }}">
                                        <a href="{{ route('sister.window.index') }}" class="menu-link">
                                            <i class="menu-icon bx bx-clinic"></i>
                                            <div>Сестринское окно</div>
                                        </a>
                                    </li>
                                @endcan

                                {{-- Прием --}}
                                @canany(['view_receptions', 'view_appointment_receptions'])
                                    <li class="menu-item {{ Request::is('dashboard/receptions/list') || Request::is('dashboard/receptions-appointment') || Request::is('dashboard/receptions-doctor-calendar/*') ? 'open' : '' }}">
                                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                                            @can('list_receptions')
                                                <li class="menu-item {{ Request::is('dashboard/receptions/list*') ? 'active' : '' }}">
                                                    <a href="{{ route('receptions.index') }}" class="menu-link">
                                                        <i class="menu-icon bx bx-calendar"></i>
                                                        <div data-i18n="Прием">Прием</div>
                                                        <div>Прием</div>
                                                    </a>
                                                    <ul class="menu-sub">
                                                        @can('view_receptions')
                                                            <li class="menu-item {{ Request::is('dashboard/receptions/list') ? 'active' : '' }}">
                                                                <a href="{{ route('receptions.index') }}"
                                                                   class="menu-link">
                                                                    <div data-i18n="Список">Список</div>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                        @can('view_appointment_receptions')
                                                            <li class="menu-item {{ Request::is('dashboard/receptions-appointment') ? 'active' : '' }}">
                                                                <a href="{{ route('receptions.appointment') }}"
                                                                   class="menu-link">
                                                                    <div data-i18n="Назначить">Назначить</div>
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </li>
                                        @endcanany
                                    </li>
                                @endcan

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

                                @can('main_warehouse_remain')
                                    <li class="menu-item {{ Request::is('dashboard/main-warehouse*') ? 'active' : '' }}">
                                        <a href="{{ route('main.warehouse.remains') }}" class="menu-link">
                                            <i class="menu-icon bx bx-store"></i>
                                            <div>Общий склад</div>
                                        </a>
                                    </li>
                                @endcan

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
                                            @can('list_warehouses')
                                                <li class="menu-item {{ Request::is('dashboard/warehouses/list*') ? 'active' : '' }}">
                                                    <a href="{{ route('warehouses.index') }}" class="menu-link">
                                                        <i class="menu-icon bx bx-store-alt"></i>
                                                        <div>Склад</div>
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

                                @can('list_pharmacies')
                                    <li class="menu-item {{ Request::is('dashboard/pharmacy*') ? 'active' : '' }}">
                                        <a href="{{ route('pharmacies.index') }}" class="menu-link">
                                            <i class="menu-icon bx bx-clinic"></i>
                                            <div>Аптека</div>
                                        </a>
                                    </li>
                                @endcan

                                @can('view_supplies')
                                    {{-- Расходные материалы --}}
                                    @can('list_supplies')
                                        <li class="menu-item {{ Request::is('dashboard/supplies*') ? 'active' : '' }}">
                                            <a href="{{ route('supplies.index') }}" class="menu-link">
                                                <i class="menu-icon bx bx-package"></i>
                                                <div data-i18n="Расходные материалы">Расходные материалы</div>
                                                <div>Расходные материалы</div>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view_allegories')
                                        {{-- Аллергии --}}
                                        @can('list_allegories')
                                            <li class="menu-item {{ Request::is('dashboard/allegories*') ? 'active' : '' }}">
                                                <a href="{{ route('allegories.index') }}" class="menu-link">
                                                    <i class="menu-icon bx bx-book"></i>
                                                    <div data-i18n="Аллегрии">Аллегрии</div>
                                                    <div>Аллергии</div>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('view_diseases')
                                            {{-- Хронические болезни --}}
                                            @can('list_diseases')
                                                <li class="menu-item {{ Request::is('dashboard/diseases*') ? 'active' : '' }}">
                                                    <a href="{{ route('diseases.index') }}" class="menu-link">
                                                        <i class="menu-icon bx bx-heart"></i>
                                                        <div data-i18n="Хронические белезни">Хронические белезни</div>
                                                        <div>Хронические болезни</div>
                                                    </a>
                                                </li>
                                            @endcan


                                            {{-- Хозяйственные материалы --}}
                                            @can('list_household_materials')
                                                <li class="menu-item {{ Request::is('dashboard/household-materials*') ? 'active' : '' }}">
                                                    <a href="{{ route('household.materials.index') }}"
                                                       class="menu-link">
                                                        <i class="menu-icon bx bx-box"></i>
                                                        <div data-i18n="Хозяйственные материалы">Хозяйственные
                                                            материалы
                                                        </div>
                                                        <div>Хоз. материалы</div>
                                                    </a>
                                                </li>
                                            @endcan
    </ul>
</aside>
<div class="layout-overlay layout-menu-toggle"></div>

