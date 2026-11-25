@extends('admin.layouts.app')

@section('title', 'Палаты')

@section('header')
    <x-page-header title="Палаты" pretitle="Организация">
        @can('create_department_rooms')
            <x-slot:actions>
                <a href="{{ route('departments.room.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Номер палаты</th>
                    <th>Кол-во коек</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rooms as $room)
                    <tr>
                        <td class="text-secondary">{{ $room->id }}</td>
                        <td>{{ $room->room_number }}</td>
                        <td>{{ $room->beds_count }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_department_rooms')
                                    <a href="{{ route('departments.room.edit', $room) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_department_rooms')
                                    <x-delete-button :action="route('departments.room.destroy', $room)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state text="Добавьте первую палату." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($rooms->hasPages())
            <div class="card-footer">{{ $rooms->links() }}</div>
        @endif
    </div>
@endsection
