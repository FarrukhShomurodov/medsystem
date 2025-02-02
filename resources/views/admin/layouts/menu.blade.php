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
        <li class="menu-item {{ Request::is('dashboard/admins*') ? 'active' : '' }}">
            <a href="{{ route('admins.index') }}" class="menu-link">
                <i class="menu-icon bx bx-shield"></i>
                <div data-i18n="Пользователи панели">Пользователи панели</div>
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

        <li class="menu-item {{ Request::is('dashboard/analyzes*') || Request::is('dashboard/laboratories*') || Request::is('dashboard/methods*') || Request::is('dashboard/equipments*') || Request::is('dashboard/types*')  ? 'open' : '' }}"
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
                <li class="menu-item {{ Request::is('dashboard/equipments*') ? 'active' : '' }}">
                    <a href="{{route('equipments.index')}}" class="menu-link">
                        <div data-i18n="Оборудование">Оборудование</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/types*') ? 'active' : '' }}">
                    <a href="{{route('types.index')}}" class="menu-link">
                        <div data-i18n="Типы">Типы</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Услуги --}}
        <li class="menu-item {{ Request::is('dashboard/services*') ||  Request::is('dashboard/services/categories*')
            || Request::is('dashboard/medications*') || Request::is('dashboard/services/procedures*')
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
                <li class="menu-item {{ Request::is('dashboard/medications*') ? 'active' : '' }}">
                    <a href="{{route('medications.index')}}" class="menu-link">
                        <div data-i18n="Препараты">Препараты</div>
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
                <li class="menu-item {{ Request::is('dashboard/services/equipments*') ? 'active' : '' }}">
                    <a href="{{route('service.equipments.index')}}" class="menu-link">
                        <div data-i18n="Оборудование">Оборудование</div>
                    </a>
                </li>

                <li class="menu-item {{ Request::is('dashboard/services/staffs*') || Request::is('dashboard/services/staff-specialties*')  ? 'open' : '' }}"
                    style="">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <div data-i18n="Персоналы">Персонал</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item {{ Request::is('dashboard/services/staffs*') ? 'active' : '' }}">
                            <a href="{{route('service.staffs.index')}}" class="menu-link">
                                <div data-i18n="Список">Список</div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is('dashboard/services/staff-specialties*') ? 'active' : '' }}">
                            <a href="{{route('service.staff.specialties.index')}}" class="menu-link">
                                <div data-i18n="Специальности">Специальности</div>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </li>

        <li class="menu-item {{ Request::is('dashboard/doctors*') || Request::is('dashboard/specialties*')  ? 'open' : '' }}"
            style="">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon  bx bx-user"></i>
                <div data-i18n="Врачи">Врачи</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('dashboard/doctors*') ? 'active' : '' }}">
                    <a href="{{route('doctors.index')}}" class="menu-link">
                        <div data-i18n="Список">Список</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('dashboard/specialties*') ? 'active' : '' }}">
                    <a href="{{route('specialties.index')}}" class="menu-link">
                        <div data-i18n="Специальности">Специальности</div>
                    </a>
                </li>
            </ul>
        </li>

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

        <li class="menu-item {{ Request::is('dashboard/institutions*') ? 'active' : '' }}">
            <a href="{{ route('institutions.index') }}" class="menu-link">
                <i class="menu-icon bx bx-buildings"></i>
                <div data-i18n="Заведение">Заведение</div>
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

        <li class="menu-item {{ Request::is('dashboard/devices*') ? 'active' : '' }}">
            <a href="{{ route('devices.index') }}" class="menu-link">
                <i class="menu-icon bx bx-chip"></i>
                <div data-i18n="Медицинские устройства ">Мед. устройства</div>
            </a>
        </li>
    </ul>
</aside>

<div class="layout-overlay layout-menu-toggle"></div>
