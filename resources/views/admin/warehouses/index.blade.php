@extends('admin.layouts.app')

@section('title', 'Склады')

@section('header')
    <x-page-header title="Склады" pretitle="Склад и аптека">
        <x-slot:actions>
            @can('create_warehouses')
                <a href="{{ route('warehouses.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
            @endcan
        </x-slot:actions>
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
                    <th>Тип</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($warehouses as $warehouse)
                    <tr>
                        <td class="text-secondary">{{ $warehouse->id }}</td>
                        <td>
                            @can('view_warehouses')
                                <a href="{{ route('warehouses.show', $warehouse->id) }}">{{ $warehouse->name }}</a>
                            @else
                                {{ $warehouse->name }}
                            @endcan
                        </td>
                        <td>{{ $warehouse->type?->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_warehouses')
                                    <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_warehouses')
                                    <x-delete-button :action="route('warehouses.destroy', $warehouse->id)"
                                                     :confirm="'Удалить склад «'.$warehouse->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"><x-empty-state text="Добавьте первый склад." icon="bx-archive" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($warehouses->hasPages())
            <div class="card-footer">{{ $warehouses->links() }}</div>
        @endif
    </div>
@endsection
