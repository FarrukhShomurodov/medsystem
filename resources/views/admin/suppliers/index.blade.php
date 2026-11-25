@extends('admin.layouts.app')

@section('title', 'Поставщики')

@section('header')
    <x-page-header title="Поставщики" pretitle="Склад и аптека">
        <x-slot:actions>
            @can('create_suppliers')
                <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Номер телефона</th>
                    <th>Производители</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td class="text-secondary">{{ $supplier->id }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td class="text-nowrap">{{ $supplier->phone }}</td>
                        <td>{{ $supplier->manufacturers->pluck('name')->implode(', ') }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_suppliers')
                                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_suppliers')
                                    <x-delete-button :action="route('suppliers.destroy', $supplier->id)"
                                                     :confirm="'Удалить поставщика «'.$supplier->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"><x-empty-state text="Добавьте первого поставщика." icon="bx-car" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($suppliers->hasPages())
            <div class="card-footer">{{ $suppliers->links() }}</div>
        @endif
    </div>
@endsection
