@extends('admin.layouts.app')

@section('title', 'Аллергии')

@section('header')
    <x-page-header title="Аллергии" pretitle="Справочники">
        @can('create_allegories')
            <x-slot:actions>
                <a href="{{ route('allegories.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse ($allegories as $allegory)
                    <tr>
                        <td class="text-secondary">{{ $allegory->id }}</td>
                        <td>{{ $allegory->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_allegories')
                                    <a href="{{ route('allegories.edit', $allegory) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_allegories')
                                    <x-delete-button :action="route('allegories.destroy', $allegory)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-empty-state text="Добавьте первую аллергию." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($allegories->hasPages())
            <div class="card-footer">{{ $allegories->links() }}</div>
        @endif
    </div>
@endsection
