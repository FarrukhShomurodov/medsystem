@extends('admin.layouts.app')

@section('title', 'Специальности')

@section('header')
    <x-page-header title="Специальности" pretitle="Персонал">
        @can('create_specialties')
            <x-slot:actions>
                <a href="{{ route('specialties.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse($specialties as $specialty)
                    <tr>
                        <td class="text-secondary">{{ $specialty->id }}</td>
                        <td>{{ $specialty->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_specialties')
                                    <a href="{{ route('specialties.edit', $specialty) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_specialties')
                                    <x-delete-button :action="route('specialties.destroy', $specialty)"
                                                     :confirm="'Удалить специальность «'.$specialty->name.'»? Действие нельзя отменить.'" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty-state text="Добавьте первую специальность." /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($specialties->hasPages())
            <div class="card-footer">{{ $specialties->links() }}</div>
        @endif
    </div>
@endsection
