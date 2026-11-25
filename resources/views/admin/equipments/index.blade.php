@extends('admin.layouts.app')

@section('title', 'Оборудование')

@section('header')
    <x-page-header title="Оборудование" pretitle="Справочники">
        @can('create_equipments')
            <x-slot:actions>
                <a href="{{ route('equipment.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Статус</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($equipments as $equipment)
                    <tr>
                        <td class="text-secondary">{{ $equipment->id }}</td>
                        <td>{{ $equipment->name }}</td>
                        <td>{{ $equipment->status?->label() }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_equipments')
                                    <a href="{{ route('equipment.edit', $equipment) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_equipments')
                                    <x-delete-button :action="route('equipment.destroy', $equipment)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state text="Добавьте первое оборудование." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($equipments->hasPages())
            <div class="card-footer">{{ $equipments->links() }}</div>
        @endif
    </div>
@endsection
