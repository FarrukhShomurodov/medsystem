@extends('admin.layouts.app')

@section('title', 'Услуги')

@section('header')
    <x-page-header title="Услуги" pretitle="Справочники">
        @can('create_services')
            <x-slot:actions>
                <a href="{{ route('services.import.show') }}" class="btn"><i class="bx bx-import" aria-hidden="true"></i>Импорт</a>
                <a href="{{ route('services.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
            </x-slot:actions>
        @endcan
    </x-page-header>
@endsection

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th>Наименование</th>
                    <th class="text-end">Цена (с учётом НДС)</th>
                    <th class="text-end">Скидка</th>
                    <th>Группа</th>
                    <th>Статус</th>
                    <th>По записи</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td class="text-secondary">{{ $service->id }}</td>
                        <td>{{ $service->name }}</td>
                        <td class="text-end text-nowrap">{{ \App\Services\Billing\Amount::fromDecimal($service->price)->format() }}</td>
                        <td class="text-end">{{ $service->discount ?? 0 }}%</td>
                        <td>{{ $service->group->name }}</td>
                        <td>
                            @if ($service->is_active)
                                <span class="badge bg-success-lt">Активна</span>
                            @else
                                <span class="badge bg-danger-lt">Не активна</span>
                            @endif
                        </td>
                        <td>
                            @if ($service->is_appointment)
                                <span class="badge bg-success-lt">Активна</span>
                            @else
                                <span class="badge bg-danger-lt">Не активна</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                @can('edit_services')
                                    <a href="{{ route('services.edit', $service) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_services')
                                    <x-delete-button :action="route('services.destroy', $service)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-empty-state text="Добавьте услугу или импортируйте прайс-лист." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($services->hasPages())
            <div class="card-footer">{{ $services->links() }}</div>
        @endif
    </div>
@endsection
