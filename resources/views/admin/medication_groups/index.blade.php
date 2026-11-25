@extends('admin.layouts.app')

@section('title', 'Группы препаратов')

@section('header')
    <x-page-header title="Группы препаратов" pretitle="Справочники">
        <x-slot:actions>
            @can('create_medication_groups')
                <a href="{{ route('medication.groups.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse ($groups as $group)
                    <tr>
                        <td class="text-secondary">{{ $group->id }}</td>
                        <td>{{ $group->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_medication_groups')
                                    <a href="{{ route('medication.groups.edit', $group->id) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_medication_groups')
                                    <x-delete-button :action="route('medication.groups.destroy', $group->id)"
                                                     :confirm="'Удалить группу «'.$group->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty-state text="Добавьте первую фармакологическую группу." icon="bx-category" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($groups->hasPages())
            <div class="card-footer">{{ $groups->links() }}</div>
        @endif
    </div>
@endsection
