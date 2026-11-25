@extends('admin.layouts.app')

@section('title', 'Расходные материалы')

@section('header')
    <x-page-header title="Расходные материалы" pretitle="Склад и аптека">
        <x-slot:actions>
            @can('create_supplies')
                <a href="{{ route('supplies.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse ($supplies as $supply)
                    <tr>
                        <td class="text-secondary">{{ $supply->id }}</td>
                        <td>{{ $supply->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_supplies')
                                    <a href="{{ route('supplies.edit', $supply->id) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_supplies')
                                    <x-delete-button :action="route('supplies.destroy', $supply->id)"
                                                     :confirm="'Удалить материал «'.$supply->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty-state text="Добавьте первый расходный материал." icon="bx-package" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($supplies->hasPages())
            <div class="card-footer">{{ $supplies->links() }}</div>
        @endif
    </div>
@endsection
