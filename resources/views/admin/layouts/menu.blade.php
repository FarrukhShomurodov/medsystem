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
                    <div>Производители</div>
                </a>
            </li>
        @endcan

        {{-- Поставщики --}}
        @can('list_suppliers')
            <li class="menu-item {{ Request::is('dashboard/suppliers*') ? 'active' : '' }}">
                <a href="{{ route('suppliers.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-box"></i>
                    <div>Поставщики</div>
                </a>
            </li>
        @endcan

        {{-- Препараты --}}
        @can('list_medications')
            <li class="menu-item {{ Request::is('dashboard/medication/list*') ? 'active' : '' }}">
                <a href="{{ route('medications.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-capsule"></i>
                    <div>Препараты</div>
                </a>
            </li>
        @endcan

        {{-- Услуги --}}
        @can('list_services')
            <li class="menu-item {{ Request::is('dashboard/services/list*') ? 'active' : '' }}">
                <a href="{{ route('services.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-plus-medical"></i>
                    <div>Услуги</div>
                </a>
            </li>
        @endcan

        {{-- Оборудование --}}
        @can('list_equipments')
            <li class="menu-item {{ Request::is('dashboard/equipments*') ? 'active' : '' }}">
                <a href="{{ route('equipment.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-wrench"></i>
                    <div>Оборудование</div>
                </a>
            </li>
        @endcan

        {{-- Отделения --}}
        @can('list_departments')
            <li class="menu-item {{ Request::is('dashboard/departments*') ? 'active' : '' }}">
                <a href="{{ route('departments.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-buildings"></i>
                    <div>Отделения</div>
                </a>
            </li>
        @endcan

        {{-- Мед. документы --}}
        @can('list_surveys')
            <li class="menu-item {{ Request::is('dashboard/surveys*') ? 'active' : '' }}">
                <a href="{{ route('surveys.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-list-check"></i>
                    <div>Мед. документы</div>
                </a>
            </li>
        @endcan

        {{-- Персонал --}}
        @can('list_staffs')
            <li class="menu-item {{ Request::is('dashboard/staffs/list*') ? 'active' : '' }}">
                <a href="{{ route('staffs.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-user"></i>
                    <div>Персонал</div>
                </a>
            </li>
        @endcan

        {{-- Области --}}
        @can('list_regions')
            <li class="menu-item {{ Request::is('dashboard/regions*') ? 'active' : '' }}">
                <a href="{{ route('regions.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-map"></i>
                    <div>Области</div>
                </a>
            </li>
        @endcan

        {{-- Медицинские устройства --}}
        @can('list_devices')
            <li class="menu-item {{ Request::is('dashboard/devices*') ? 'active' : '' }}">
                <a href="{{ route('devices.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-chip"></i>
                    <div>Мед. устройства</div>
                </a>
            </li>
        @endcan

        {{-- Диагнозы --}}
        @can('list_diagnoses')
            <li class="menu-item {{ Request::is('dashboard/diagnoses*') ? 'active' : '' }}">
                <a href="{{ route('diagnoses.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-notepad"></i>
                    <div>Диагнозы</div>
                </a>
            </li>
        @endcan

        {{-- Пациенты --}}
        @can('list_patients')
            <li class="menu-item {{ Request::is('dashboard/patients*') ? 'active' : '' }}">
                <a href="{{ route('patients.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-group"></i>
                    <div>Пациенты</div>
                </a>
            </li>
        @endcan

        {{-- Касса --}}
        @can('list_cashier')
            <li class="menu-item {{ Request::is('dashboard/cashier*') ? 'active' : '' }}">
                <a href="{{ route('cashier.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-credit-card"></i>
                    <div>Касса</div>
                </a>
            </li>
        @endcan

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
        @can('list_receptions')
            <li class="menu-item {{ Request::is('dashboard/receptions/list*') ? 'active' : '' }}">
                <a href="{{ route('receptions.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-calendar"></i>
                    <div>Прием</div>
                </a>
            </li>
        @endcan

        {{-- Общий склад --}}
        @can('main_warehouse_remain')
            <li class="menu-item {{ Request::is('dashboard/main-warehouse*') ? 'active' : '' }}">
                <a href="{{ route('main.warehouse.remains') }}" class="menu-link">
                    <i class="menu-icon bx bx-store"></i>
                    <div>Общий склад</div>
                </a>
            </li>
        @endcan

        {{-- Склад --}}
        @can('list_warehouses')
            <li class="menu-item {{ Request::is('dashboard/warehouses/list*') ? 'active' : '' }}">
                <a href="{{ route('warehouses.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-store-alt"></i>
                    <div>Склад</div>
                </a>
            </li>
        @endcan

        {{-- Аптека --}}
        @can('list_pharmacies')
            <li class="menu-item {{ Request::is('dashboard/pharmacy*') ? 'active' : '' }}">
                <a href="{{ route('pharmacies.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-clinic"></i>
                    <div>Аптека</div>
                </a>
            </li>
        @endcan

        {{-- Расходные материалы --}}
        @can('list_supplies')
            <li class="menu-item {{ Request::is('dashboard/supplies*') ? 'active' : '' }}">
                <a href="{{ route('supplies.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-package"></i>
                    <div>Расходные материалы</div>
                </a>
            </li>
        @endcan

        {{-- Аллергии --}}
        @can('list_allegories')
            <li class="menu-item {{ Request::is('dashboard/allegories*') ? 'active' : '' }}">
                <a href="{{ route('allegories.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-book"></i>
                    <div>Аллергии</div>
                </a>
            </li>
        @endcan

        {{-- Хронические болезни --}}
        @can('list_diseases')
            <li class="menu-item {{ Request::is('dashboard/diseases*') ? 'active' : '' }}">
                <a href="{{ route('diseases.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-heart"></i>
                    <div>Хронические болезни</div>
                </a>
            </li>
        @endcan

        {{-- Хозяйственные материалы --}}
        @can('list_household_materials')
            <li class="menu-item {{ Request::is('dashboard/household-materials*') ? 'active' : '' }}">
                <a href="{{ route('household.materials.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-box"></i>
                    <div>Хоз. материалы</div>
                </a>
            </li>
        @endcan
    </ul>
</aside>
<div class="layout-overlay layout-menu-toggle"></div>
