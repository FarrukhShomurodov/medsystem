@extends('admin.layouts.app')

@section('title', 'Аптеки')

@section('header')
    <x-page-header title="Аптеки" pretitle="Склад и аптека">
        <x-slot:actions>
            @can('create_pharmacies')
                <a href="{{ route('pharmacies.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Препараты</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($pharmacies as $pharmacy)
                    <tr>
                        <td class="text-secondary">{{ $pharmacy->id }}</td>
                        <td>{{ $pharmacy->name }}</td>
                        <td>{{ $pharmacy->type?->name }}</td>
                        <td>
                            @can('view_pharmacy_medications')
                                <a href="{{ route('pharmacy.medications', $pharmacy->id) }}" class="btn btn-sm">
                                    <i class="bx bx-show" aria-hidden="true"></i>Смотреть
                                </a>
                            @endcan
                        </td>
                        <td>
                            <div class="table-actions">
                                @can('edit_pharmacies')
                                    <a href="{{ route('pharmacies.edit', $pharmacy->id) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_pharmacies')
                                    <x-delete-button :action="route('pharmacies.destroy', $pharmacy->id)"
                                                     :confirm="'Удалить аптеку «'.$pharmacy->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"><x-empty-state text="Добавьте первую аптеку." icon="bx-capsule" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($pharmacies->hasPages())
            <div class="card-footer">{{ $pharmacies->links() }}</div>
        @endif
    </div>
@endsection
