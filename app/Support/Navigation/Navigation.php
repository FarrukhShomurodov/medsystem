<?php

namespace App\Support\Navigation;

/**
 * The dashboard sidebar, the one place its entries are defined (rendered by <x-sidebar />).
 *
 * Every link names the permission of its route, the same one the route's middleware checks, so an
 * entry is shown exactly to the accounts that may open its page. NavigationTest checks every route
 * and permission below against the router; MenuTest checks the rendered sidebar per permission.
 * The dashboard home is reached through the brand link, not listed here.
 */
final class Navigation
{
    /**
     * @return list<NavSection>
     */
    public static function sections(): array
    {
        return [
            new NavSection('Работа с пациентами', [
                NavItem::link('Пациенты', 'patients.index', 'list_patients', icon: 'bx-group', active: [
                    'patients.*', 'maps.*', 'map.survey', 'patient.map.create', 'patient.medication.*', 'documents.*', 'registrar.*',
                ]),
                NavItem::group('Приём', 'bx-calendar', [
                    NavItem::link('Список', 'receptions.index', 'list_receptions'),
                    NavItem::link('Назначить', 'receptions.appointment', 'view_appointment_receptions', [
                        'receptions.appointment', 'receptions.doctor.calendar',
                    ]),
                ]),
                NavItem::link('Касса', 'cashier.index', 'view_cashier', ['cashier.*'], 'bx-wallet'),
                NavItem::link('Сестринское окно', 'sister.window.index', 'sister_window_show', ['sister.window.*'], 'bx-first-aid'),
            ]),

            new NavSection('Склад и аптека', [
                NavItem::group('Общий склад', 'bx-store', [
                    NavItem::link('Остатки', 'main.warehouse.remains', 'main_warehouse_remain', ['main.warehouse.*']),
                    NavItem::link('Заказы отделений', 'supply-requests.index', 'main_warehouse_remain'),
                ]),
                NavItem::group('Склад', 'bx-archive', [
                    NavItem::link('Список', 'warehouses.index', 'list_warehouses', [
                        'warehouses.index', 'warehouses.create', 'warehouses.show', 'warehouses.edit',
                    ]),
                    NavItem::link('Остатки', 'warehouse.remains', 'view_warehouse_remains', [
                        'warehouse.remains', 'warehouse.order', 'warehouse.medication.show', 'warehouses.medications', 'warehouses.supplies',
                    ]),
                    NavItem::link('Типы', 'warehouses.type.index', 'list_warehouse_types', ['warehouses.type.*']),
                ]),
                NavItem::group('Аптека', 'bx-capsule', [
                    NavItem::link('Список', 'pharmacies.index', 'list_pharmacies', ['pharmacies.*', 'pharmacy.medications']),
                    NavItem::link('Типы', 'pharmacy.types.index', 'list_pharmacy_types', ['pharmacy.types.*']),
                    NavItem::link('Перевод препаратов', 'transfers.index', 'list_transfers', ['transfers.*', 'transfer.medications']),
                ]),
                NavItem::link('Расходные материалы', 'supplies.index', 'list_supplies', ['supplies.*'], 'bx-package'),
                NavItem::link('Хозяйственные материалы', 'household.materials.index', 'list_household_materials', ['household.materials.*'], 'bx-spray-can'),
                NavItem::link('Поставщики', 'suppliers.index', 'list_suppliers', ['suppliers.*'], 'bx-car'),
                NavItem::link('Производители', 'manufacturers.index', 'list_manufacturers', ['manufacturers.*'], 'bx-buildings'),
            ]),

            new NavSection('Справочники', [
                NavItem::group('Препараты', 'bx-plus-medical', [
                    NavItem::link('Список', 'medications.index', 'list_medications', ['medications.*', 'dangerous.combination.import.*']),
                    NavItem::link('Группы', 'medication.groups.index', 'list_medication_groups', ['medication.groups.*']),
                ]),
                NavItem::group('Услуги', 'bx-health', [
                    NavItem::link('Список', 'services.index', 'list_services', ['services.*']),
                    NavItem::link('Назначенные', 'patient.services.index', 'list_patient_services', ['patient.services.*']),
                    NavItem::link('Типы', 'service.types.index', 'list_service_types', ['service.types.*']),
                    NavItem::link('Группы', 'service.groups.index', 'list_service_groups', ['service.groups.*']),
                    NavItem::link('Процедуры', 'procedures.index', 'list_service_procedures', ['procedures.*']),
                    NavItem::link('Методы', 'service.methods.index', 'list_service_methods', ['service.methods.*']),
                ]),
                NavItem::link('Диагнозы', 'diagnoses.index', 'list_diagnoses', ['diagnoses.*'], 'bx-notepad'),
                NavItem::link('Мед. документы', 'surveys.index', 'list_surveys', ['surveys.*'], 'bx-list-check'),
                NavItem::link('Аллергии', 'allegories.index', 'list_allegories', ['allegories.*'], 'bx-error-alt'),
                NavItem::link('Хронические болезни', 'diseases.index', 'list_diseases', ['diseases.*'], 'bx-heart'),
                NavItem::link('Мед. устройства', 'devices.index', 'list_devices', ['devices.*'], 'bx-chip'),
                NavItem::group('Оборудование', 'bx-wrench', [
                    NavItem::link('Список', 'equipment.index', 'list_equipments', ['equipment.index', 'equipment.create', 'equipment.edit']),
                    NavItem::link('Типы', 'equipment.types.index', 'list_equipment_types', ['equipment.types.*']),
                ]),
            ]),

            new NavSection('Организация', [
                NavItem::group('Отделения', 'bx-building-house', [
                    NavItem::link('Список', 'departments.index', 'list_departments', [
                        'departments.index', 'departments.create', 'departments.edit',
                    ]),
                    NavItem::link('Палаты', 'departments.room.index', 'list_department_rooms', ['departments.room.*']),
                    NavItem::link('Кабинеты', 'offices.index', 'list_offices', ['offices.*']),
                    NavItem::link('Этажи', 'floors.index', 'list_floors', ['floors.*']),
                ]),
                NavItem::group('Персонал', 'bx-user', [
                    NavItem::link('Список', 'staffs.index', 'list_staffs', ['staffs.index', 'staffs.create', 'staffs.show', 'staffs.edit']),
                    NavItem::link('Должности', 'staffs.position.index', 'list_staff_positions', ['staffs.position.*']),
                    NavItem::link('Специальности', 'specialties.index', 'list_specialties', ['specialties.*']),
                ]),
                NavItem::group('Области', 'bx-map', [
                    NavItem::link('Список', 'regions.index', 'list_regions', ['regions.*']),
                    NavItem::link('Города', 'cities.index', 'list_cities', ['cities.*']),
                ]),
                NavItem::link('Пользователи панели', 'admins.index', 'list_admins', ['admins.*'], 'bx-shield-quarter'),
            ]),
        ];
    }

    /**
     * Every link of the sidebar, in its order.
     *
     * @return list<NavItem>
     */
    public static function links(): array
    {
        $links = [];

        foreach (self::sections() as $section) {
            foreach ($section->items as $item) {
                array_push($links, ...$item->links());
            }
        }

        return $links;
    }

    /**
     * The sidebar as one account sees it: the links whose permission it holds, the groups left
     * with at least one link, the sections left with at least one entry.
     *
     * @param  callable(string): bool  $allows  whether the account holds the permission
     * @return list<NavSection>
     */
    public static function visibleTo(callable $allows): array
    {
        $sections = [];

        foreach (self::sections() as $section) {
            $items = [];

            foreach ($section->items as $item) {
                if (! $item->isGroup()) {
                    if ($allows((string) $item->permission)) {
                        $items[] = $item;
                    }

                    continue;
                }

                $children = array_values(array_filter($item->children, fn (NavItem $child): bool => $allows((string) $child->permission)));

                if ($children !== []) {
                    $items[] = NavItem::group($item->label, (string) $item->icon, $children);
                }
            }

            if ($items !== []) {
                $sections[] = new NavSection($section->title, $items);
            }
        }

        return $sections;
    }
}
