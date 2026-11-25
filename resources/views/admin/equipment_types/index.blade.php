@extends('admin.layouts.app')

@section('title', 'Типы оборудования')

@section('header')
    <x-page-header title="Типы оборудования" pretitle="Оборудование">
        @can('create_equipment_types')
            <x-slot:actions>
                <a href="{{ route('equipment.types.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($types as $type)
                    <tr>
                        <td class="text-secondary">{{ $type->id }}</td>
                        <td>{{ $type->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_equipment_types')
                                    <a href="{{ route('equipment.types.edit', $type) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_equipment_types')
                                    <x-delete-button :action="route('equipment.types.destroy', $type)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-empty-state text="Добавьте первый тип оборудования." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($types->hasPages())
            <div class="card-footer">{{ $types->links() }}</div>
        @endif
    </div>
@endsection
