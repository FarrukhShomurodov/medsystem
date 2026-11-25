@extends('admin.layouts.app')

@section('title', 'Города')

@section('header')
    <x-page-header title="Города" pretitle="Организация">
        @can('create_cities')
            <x-slot:actions>
                <a href="{{ route('cities.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Область</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($cities as $city)
                    <tr>
                        <td class="text-secondary">{{ $city->id }}</td>
                        <td>{{ $city->name }}</td>
                        <td>{{ $city->region->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_cities')
                                    <a href="{{ route('cities.edit', $city) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_cities')
                                    <x-delete-button :action="route('cities.destroy', $city)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state text="Добавьте первый город." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($cities->hasPages())
            <div class="card-footer">{{ $cities->links() }}</div>
        @endif
    </div>
@endsection
