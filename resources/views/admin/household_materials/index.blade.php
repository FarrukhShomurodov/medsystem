@extends('admin.layouts.app')

@section('title', 'Хозяйственные материалы')

@section('header')
    <x-page-header title="Хозяйственные материалы" pretitle="Склад и аптека">
        <x-slot:actions>
            @can('create_household_materials')
                <a href="{{ route('household.materials.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse ($materials as $material)
                    <tr>
                        <td class="text-secondary">{{ $material->id }}</td>
                        <td>{{ $material->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_household_materials')
                                    <a href="{{ route('household.materials.edit', $material->id) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_household_materials')
                                    <x-delete-button :action="route('household.materials.destroy', $material->id)"
                                                     :confirm="'Удалить «'.$material->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty-state text="Добавьте первый хозяйственный материал." icon="bx-spray-can" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($materials->hasPages())
            <div class="card-footer">{{ $materials->links() }}</div>
        @endif
    </div>
@endsection
