@extends('admin.layouts.app')

@section('title', 'Должности')

@section('header')
    <x-page-header title="Должности" pretitle="Персонал">
        @can('create_staff_positions')
            <x-slot:actions>
                <a href="{{ route('staffs.position.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse($positions as $position)
                    <tr>
                        <td class="text-secondary">{{ $position->id }}</td>
                        <td>{{ $position->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_staff_positions')
                                    <a href="{{ route('staffs.position.edit', $position) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                {{-- The positions the system relies on (doctor, nurse, registrar ...) cannot be deleted. --}}
                                @unless($position->isSeeded())
                                    @can('delete_staff_positions')
                                        <x-delete-button :action="route('staffs.position.destroy', $position)"
                                                         :confirm="'Удалить должность «'.$position->name.'»? Действие нельзя отменить.'" />
                                    @endcan
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty-state text="Добавьте первую должность." /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($positions->hasPages())
            <div class="card-footer">{{ $positions->links() }}</div>
        @endif
    </div>
@endsection
