@extends('admin.layouts.app')

@section('title', 'Кабинеты')

@section('header')
    <x-page-header title="Кабинеты" pretitle="Организация">
        @can('create_offices')
            <x-slot:actions>
                <a href="{{ route('offices.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Этаж</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($offices as $office)
                    <tr>
                        <td class="text-secondary">{{ $office->id }}</td>
                        <td>{{ $office->name }}</td>
                        <td class="text-nowrap">
                            @if ($office->floor)
                                @can('view_floors')
                                    <a href="{{ route('floors.show', $office->floor) }}">{{ $office->floor->name }}</a>
                                @else
                                    {{ $office->floor->name }}
                                @endcan
                            @else
                                <span class="text-secondary">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                @can('edit_offices')
                                    <a href="{{ route('offices.edit', $office) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_offices')
                                    <x-delete-button :action="route('offices.destroy', $office)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state text="Добавьте первый кабинет." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($offices->hasPages())
            <div class="card-footer">{{ $offices->links() }}</div>
        @endif
    </div>
@endsection
