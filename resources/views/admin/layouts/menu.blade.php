<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <h5 style="margin: 0 !important;">
            <a href="{{ route('dashboard') }}" class="app-brand-link">
                Medical system
            </a>
        </h5>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx menu-toggle-icon d-none d-xl-block fs-4 align-middle"></i>
            <i class="bx bx-x d-block d-xl-none bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>

    <ul class="menu-inner py-1 ps ps--active-y">
        {{--        <li class="menu-item {{ Request::is('dashboard/admins*') ? 'active' : '' }}">--}}
        {{--            <a href="{{ route('admins.index') }}" class="menu-link">--}}
        {{--                <i class="menu-icon bx bx-shield"></i>--}}
        {{--                <div data-i18n="Пользователи панели">Пользователи панели</div>--}}
        {{--            </a>--}}
        {{--        </li>--}}

        {{-- Производители --}}
        <li class="menu-item {{ Request::is('dashboard/manufacturers*') ? 'active' : '' }}">
            <a href="{{ route('manufacturers.index') }}" class="menu-link">
                <i class="menu-icon bx bx-buildings"></i>
                <div data-i18n="Производители">Производители</div>
            </a>
        </li>


        {{-- Поставшик --}}
        <li class="menu-item {{ Request::is('dashboard/suppliers*') ? 'active' : '' }}">
            <a href="{{ route('suppliers.index') }}" class="menu-link">
                <i class="menu-icon bx bx-box"></i>
                <div data-i18n="Поставшик">Поставшик</div>
            </a>
        </li>

        {{-- Препараты --}}
        <li class="menu-item {{ Request::is('dashboard/medication/list*') || Request::is('dashboard/medication/groups*')  ? 'open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-capsule"></i>
                <div data-i18n="Препараты">Препараты</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('dashboard/medication/list*') ? 'active' : '' }}">
                    <a href="{{route('medications.index')}}" class="menu-link">
                        <div data-i18n="Список">Список</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/medication/groups*') ? 'active' : '' }}">
                    <a href="{{route('medication.groups.index')}}" class="menu-link">
                        <div data-i18n="Группы">Группы</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Услуги --}}
        <li class="menu-item {{ Request::is('dashboard/services*') ||  Request::is('dashboard/services/categories*')
            || Request::is('dashboard/services/procedures*')
            || Request::is('dashboard/services/methods*') || Request::is('dashboard/services/equipments*')
            || Request::is('dashboard/services/staff-specialties*') || Request::is('dashboard/services/staffs*')
            || Request::is('dashboard/services/assign-patient*') ? 'open' : '' }}"
            style="">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-plus-medical"></i>
                <div data-i18n="Услуги">Услуги</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{Request::is('dashboard/services') || Request::is('dashboard/services/create') || Request::is('dashboard/services/edit')? 'active' : '' }}">
                    <a href="{{route('services.index')}}" class="menu-link">
                        <div data-i18n="Список">Список</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/services/assign-patient*') ? 'active' : '' }}">
                    <a href="{{route('patient.services.index')}}" class="menu-link">
                        <div data-i18n="Назначеные">Назначеные</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/services/categories*') ? 'active' : '' }}">
                    <a href="{{route('categories.index')}}" class="menu-link">
                        <div data-i18n="Категории">Категории</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/services/procedures*') ? 'active' : '' }}">
                    <a href="{{route('procedures.index')}}" class="menu-link">
                        <div data-i18n="Процедуры">Процедуры</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/services/methods*') ? 'active' : '' }}">
                    <a href="{{route('service.methods.index')}}" class="menu-link">
                        <div data-i18n="Методы">Методы</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('dashboard/equipments*') ? 'active' : '' }}">
            <a href="{{ route('equipment.index') }}" class="menu-link">
                <i class="menu-icon bx bx-first-aid"></i>
                <div data-i18n="Оборудование">Оборудование</div>
            </a>
        </li>

        {{-- Отделение --}}
        <li class="menu-item {{ Request::is('dashboard/departments') || Request::is('dashboard/departments/create') || Request::is('dashboard/departments/edit/*')
            || Request::is('dashboard/departments/services*')
            || Request::is('dashboard/departments/types*')  ? 'open' : '' }}" style="">

            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-buildings"></i>
                <div data-i18n="Отделение">Отделение</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('dashboard/departments') || Request::is('dashboard/departments/create') || Request::is('dashboard/departments/edit/*') ? 'active' : '' }}">
                    <a href="{{route('departments.index')}}" class="menu-link">
                        <div data-i18n="Список">Список</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/departments/types*') ? 'active' : '' }}">
                    <a href="{{route('departments.type.index')}}" class="menu-link">
                        <div data-i18n="Типы">Типы</div>
                    </a>
                </li>
            </ul>
        </li>


        {{-- Персонал --}}
        <li class="menu-item {{ Request::is('dashboard/staffs')
            || Request::is('dashboard/staffs/create')
            || Request::is('dashboard/staffs/edit/*')
            || Request::is('dashboard/staffs/positions*')
            || Request::is('dashboard/specialties*')? 'open' : '' }}" style="">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon  bx bx-user"></i>
                <div data-i18n="Персонал">Персонал</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('dashboard/staffs') || Request::is('dashboard/staffs/create') || Request::is('dashboard/staff/edit/*') ? 'active' : '' }}">
                    <a href="{{route('staffs.index')}}" class="menu-link">
                        <div data-i18n="Список">Список</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/staffs/positions*') ? 'active' : '' }}">
                    <a href="{{route('staffs.position.index')}}" class="menu-link">
                        <div data-i18n="Должность">Должность</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/specialties*') ? 'active' : '' }}">
                    <a href="{{route('specialties.index')}}" class="menu-link">
                        <div data-i18n="Специальность">Специальность</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Области --}}
        <li class="menu-item {{ Request::is('dashboard/regions*') || Request::is('dashboard/cities*')  ? 'open' : '' }}"
            style="">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-map"></i>
                <div data-i18n="Области">Области</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('dashboard/regions*') ? 'active' : '' }}">
                    <a href="{{route('regions.index')}}" class="menu-link">
                        <div data-i18n="Список">Список</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/cities*') ? 'active' : '' }}">
                    <a href="{{route('cities.index')}}" class="menu-link">
                        <div data-i18n="Города">Города</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('dashboard/devices*') ? 'active' : '' }}">
            <a href="{{ route('devices.index') }}" class="menu-link">
                <i class="menu-icon bx bx-chip"></i>
                <div data-i18n="Медицинские устройства ">Мед. устройства</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is('dashboard/patients*') ? 'active' : '' }}">
            <a href="{{ route('patients.index') }}" class="menu-link">
                <i class="menu-icon bx bx-group"></i>
                <div data-i18n="Пациенты">Пациенты</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is('dashboard/maps*') ? 'active' : '' }}">
            <a href="{{ route('maps.index') }}" class="menu-link">
                <i class="menu-icon bx bx-health"></i>
                <div data-i18n="Карта обследования ">Карта обследования</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is('dashboard/analyzes*') || Request::is('dashboard/laboratories*') || Request::is('dashboard/methods*') || Request::is('dashboard/types*') || Request::is('dashboard/analyze-result*') || Request::is('dashboard/analyze-result-show*') || Request::is('dashboard/analyze-result-edit*')  ? 'open' : '' }}"
            style="">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-dna"></i>
                <div data-i18n="Анализы">Анализы</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('dashboard/analyzes*') ? 'active' : '' }}">
                    <a href="{{route('analyzes.index')}}" class="menu-link">
                        <div data-i18n="Список">Список</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/laboratories*') ? 'active' : '' }}">
                    <a href="{{route('laboratories.index')}}" class="menu-link">
                        <div data-i18n="Лаборатории">Лаборатории</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/methods*') ? 'active' : '' }}">
                    <a href="{{route('methods.index')}}" class="menu-link">
                        <div data-i18n="Методы">Методы</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/types*') ? 'active' : '' }}">
                    <a href="{{route('types.index')}}" class="menu-link">
                        <div data-i18n="Типы">Типы</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('dashboard/warehouses/types*')
            ||Request::is('dashboard/warehouses/medications*')
            || Request::is('dashboard/warehouses/list*')
            || Request::is('dashboard/warehouses/medication-show/*') ? 'open' : '' }}"
            style="">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-store"></i>
                <div data-i18n="Склад">Склад</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('dashboard/warehouses/list*') ? 'active' : '' }}">
                    <a href="{{route('warehouses.index')}}" class="menu-link">
                        <div data-i18n="Список">Список</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/warehouses/medications*') || Request::is('dashboard/warehouses/medication-show/*') ? 'active' : '' }}">
                    <a href="{{ route('warehouses.medications' )}}" class="menu-link">
                        <div data-i18n="Препараты">Препараты</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/warehouses/types*') ? 'active' : '' }}">
                    <a href="{{route('warehouses.type.index')}}" class="menu-link">
                        <div data-i18n="Типы">Типы</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('dashboard/pharmacy/types*')
            || Request::is('dashboard/pharmacy') || Request::is('dashboard/pharmacy/create') || Request::is('dashboard/pharmacy/*/edit')
            ||  Request::is('dashboard/transfers*')
            ||  Request::is('dashboard/transfer-medications/*')
            ||  Request::is('dashboard/pharmacy/medications/*') ? 'open' : '' }}"
            style="">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-clinic"></i>
                <div data-i18n="Аптека">Аптека</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('dashboard/pharmacy') || Request::is('dashboard/pharmacy/create') || Request::is('dashboard/pharmacy/*/edit') || Request::is('dashboard/pharmacy/medications/*') ? 'active' : '' }}">
                    <a href="{{route('pharmacies.index')}}" class="menu-link">
                        <div data-i18n="Список">Список</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/pharmacy/types*') ? 'active' : '' }}">
                    <a href="{{route('pharmacy.types.index')}}" class="menu-link">
                        <div data-i18n="Типы">Типы</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/transfers*') ||  Request::is('dashboard/transfer-medications/*') ? 'active' : '' }}">
                    <a href="{{route('transfers.index')}}" class="menu-link">
                        <div data-i18n="Перевод препаратов">Перевод препаратов</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('dashboard/supplies*') ? 'active' : '' }}">
            <a href="{{ route('supplies.index') }}" class="menu-link">
                <i class="menu-icon bx bx-package"></i>
                <div data-i18n="Расходные материалы">Расходные материалы</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is('dashboard/allegories*') ? 'active' : '' }}">
            <a href="{{ route('allegories.index') }}" class="menu-link">
                <i class="menu-icon bx bx-book"></i>
                <div data-i18n="Аллегрии">Аллегрии</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is('dashboard/diseases*') ? 'active' : '' }}">
            <a href="{{ route('diseases.index') }}" class="menu-link">
                <i class="menu-icon bx bx-heart"></i>
                <div data-i18n="Хронические белезни">Хронические белезни</div>
            </a>
        </li>
    </ul>
</aside>

<div class="layout-overlay layout-menu-toggle"></div>
