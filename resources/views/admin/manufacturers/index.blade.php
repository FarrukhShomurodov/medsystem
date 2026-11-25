@extends('admin.layouts.app')

@section('title', 'Производители')

@section('header')
    <x-page-header title="Производители" pretitle="Склад и аптека">
        <x-slot:actions>
            @can('create_manufacturers')
                <a href="{{ route('manufacturers.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Страна производства</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($manufacturers as $manufacturer)
                    <tr>
                        <td class="text-secondary">{{ $manufacturer->id }}</td>
                        <td>{{ $manufacturer->name }}</td>
                        <td>{{ $manufacturer->country_of_manufacture }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_manufacturers')
                                    <a href="{{ route('manufacturers.edit', $manufacturer->id) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_manufacturers')
                                    <x-delete-button :action="route('manufacturers.destroy', $manufacturer->id)"
                                                     :confirm="'Удалить производителя «'.$manufacturer->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"><x-empty-state text="Добавьте первого производителя." icon="bx-buildings" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($manufacturers->hasPages())
            <div class="card-footer">{{ $manufacturers->links() }}</div>
        @endif
    </div>
@endsection
