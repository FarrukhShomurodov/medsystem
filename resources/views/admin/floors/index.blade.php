@extends('admin.layouts.app')

@section('title', 'Этажи')

@section('header')
    <x-page-header title="Этажи" pretitle="Организация">
        @can('create_floors')
            <x-slot:actions>
                <a href="{{ route('floors.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($floors as $floor)
                    <tr>
                        <td class="text-secondary">{{ $floor->id }}</td>
                        <td>
                            @can('view_floors')
                                <a href="{{ route('floors.show', $floor) }}">{{ $floor->name }}</a>
                            @else
                                {{ $floor->name }}
                            @endcan
                        </td>
                        <td>
                            <div class="table-actions">
                                @can('edit_floors')
                                    <a href="{{ route('floors.edit', $floor) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_floors')
                                    <x-delete-button :action="route('floors.destroy', $floor)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-empty-state text="Добавьте первый этаж." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($floors->hasPages())
            <div class="card-footer">{{ $floors->links() }}</div>
        @endif
    </div>
@endsection
