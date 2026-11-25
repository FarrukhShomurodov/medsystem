@extends('admin.layouts.app')

@section('title', 'Отделения')

@section('header')
    <x-page-header title="Отделения" pretitle="Организация">
        @can('create_departments')
            <x-slot:actions>
                <a href="{{ route('departments.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Название</th>
                    <th>Этаж</th>
                    <th>Склад</th>
                    <th>Кол-во палат</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($departments as $department)
                    <tr>
                        <td class="text-secondary">{{ $department->id }}</td>
                        <td>{{ $department->name }}</td>
                        <td class="text-nowrap">
                            @if ($department->floor)
                                @can('view_floors')
                                    <a href="{{ route('floors.show', $department->floor) }}">{{ $department->floor->name }}</a>
                                @else
                                    {{ $department->floor->name }}
                                @endcan
                            @else
                                <span class="text-secondary">—</span>
                            @endif
                        </td>
                        <td>{{ $department->warehouse->name ?? '—' }}</td>
                        <td>{{ $department->rooms_count }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_departments')
                                    <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_departments')
                                    <x-delete-button :action="route('departments.destroy', $department)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state text="Добавьте первое отделение." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($departments->hasPages())
            <div class="card-footer">{{ $departments->links() }}</div>
        @endif
    </div>
@endsection
