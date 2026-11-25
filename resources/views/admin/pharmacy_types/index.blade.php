@extends('admin.layouts.app')

@section('title', 'Типы аптек')

@section('header')
    <x-page-header title="Типы аптек" pretitle="Склад и аптека">
        <x-slot:actions>
            @can('create_pharmacy_types')
                <a href="{{ route('pharmacy.types.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                                @can('edit_pharmacy_types')
                                    <a href="{{ route('pharmacy.types.edit', $type->id) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_pharmacy_types')
                                    <x-delete-button :action="route('pharmacy.types.destroy', $type->id)"
                                                     :confirm="'Удалить тип «'.$type->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty-state text="Добавьте первый тип аптеки." icon="bx-category" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($types->hasPages())
            <div class="card-footer">{{ $types->links() }}</div>
        @endif
    </div>
@endsection
