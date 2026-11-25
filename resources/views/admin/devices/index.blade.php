@extends('admin.layouts.app')

@section('title', 'Медицинские устройства')

@section('header')
    <x-page-header title="Медицинские устройства" pretitle="Справочники">
        <x-slot:actions>
            @can('create_devices')
                <a href="{{ route('devices.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse ($devices as $device)
                    <tr>
                        <td class="text-secondary">{{ $device->id }}</td>
                        <td>{{ $device->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_devices')
                                    <a href="{{ route('devices.edit', $device->id) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_devices')
                                    <x-delete-button :action="route('devices.destroy', $device->id)"
                                                     :confirm="'Удалить устройство «'.$device->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty-state text="Добавьте первое медицинское устройство." icon="bx-chip" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($devices->hasPages())
            <div class="card-footer">{{ $devices->links() }}</div>
        @endif
    </div>
@endsection
