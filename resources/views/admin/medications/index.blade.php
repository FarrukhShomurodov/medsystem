@extends('admin.layouts.app')

@php($filtered = request()->filled('active_substance_name') || request()->filled('group_id') || request()->filled('manufacturer_id'))

@section('title', 'Препараты')

@section('header')
    <x-page-header title="Препараты" pretitle="Справочники">
        <x-slot:actions>
            <button type="button" @class(['btn', 'collapsed' => ! $filtered]) data-bs-toggle="collapse" data-bs-target="#filters"
                    aria-expanded="{{ $filtered ? 'true' : 'false' }}" aria-controls="filters">
                <i class="bx bx-filter-alt" aria-hidden="true"></i>Фильтры
            </button>
            @can('create_medications')
                <a href="{{ route('dangerous.combination.import.show') }}" class="btn"><i class="bx bx-import" aria-hidden="true"></i>Импорт</a>
                <a href="{{ route('medications.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
            @endcan
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="card">
        <div id="filters" @class(['collapse', 'show' => $filtered])>
            <form method="GET" action="{{ route('medications.index') }}" class="card-body border-bottom">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="active_substance_name" class="form-label">Активное вещество</label>
                        <input type="text" name="active_substance_name" id="active_substance_name" class="form-control"
                               value="{{ request('active_substance_name') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="group">Группа</label>
                        <select id="group" name="group_id" class="form-select select2">
                            <option value="">Все группы</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" @selected($group->id == request('group_id'))>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="manufacturer">Производитель</label>
                        <select id="manufacturer" name="manufacturer_id" class="form-select select2">
                            <option value="">Все производители</option>
                            @foreach ($manufacturers as $manufacturer)
                                <option value="{{ $manufacturer->id }}" @selected($manufacturer->id == request('manufacturer_id'))>{{ $manufacturer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Применить</button>
                        @if ($filtered)
                            <a href="{{ route('medications.index') }}" class="btn">Сбросить</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th>Название</th>
                    <th>Форма выпуска</th>
                    <th>Дозировка</th>
                    <th>Группа</th>
                    <th>Производитель</th>
                    <th>Регистрационный номер</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($medications as $medication)
                    <tr>
                        <td class="text-secondary">{{ $medication->id }}</td>
                        <td>
                            @can('view_medications')
                                <a href="{{ route('medications.show', $medication->id) }}">{{ $medication->name }}</a>
                            @else
                                {{ $medication->name }}
                            @endcan
                        </td>
                        <td>{{ $medication->release_form }}</td>
                        <td>{{ $medication->dosage }}</td>
                        <td>{{ $medication->group?->name }}</td>
                        <td>{{ $medication->manufacturer?->name }}</td>
                        <td>{{ $medication->registration_number }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_medications')
                                    <a href="{{ route('medications.edit', $medication->id) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_medications')
                                    <x-delete-button :action="route('medications.destroy', $medication->id)"
                                                     :confirm="'Удалить препарат «'.$medication->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            @if ($filtered)
                                <x-empty-state title="Ничего не найдено" text="Измените или сбросьте фильтры." icon="bx-search" />
                            @else
                                <x-empty-state text="Добавьте первый препарат." icon="bx-capsule" />
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($medications->hasPages())
            <div class="card-footer">{{ $medications->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
