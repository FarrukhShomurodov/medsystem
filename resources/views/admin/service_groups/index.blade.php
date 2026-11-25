@extends('admin.layouts.app')

@section('title', 'Группы услуг')

@section('header')
    <x-page-header title="Группы услуг" pretitle="Услуги">
        @can('create_service_groups')
            <x-slot:actions>
                <a href="{{ route('service.groups.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Подгруппы</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($groups as $group)
                    <tr>
                        <td class="text-secondary">{{ $group->id }}</td>
                        <td>{{ $group->name }}</td>
                        <td class="text-secondary">{{ $group->subGroups->pluck('name')->implode(', ') }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_service_groups')
                                    <a href="{{ route('service.groups.edit', $group) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_service_groups')
                                    <x-delete-button :action="route('service.groups.destroy', $group)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state text="Добавьте первую группу услуг." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($groups->hasPages())
            <div class="card-footer">{{ $groups->links() }}</div>
        @endif
    </div>
@endsection
