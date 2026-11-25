@extends('admin.layouts.app')

@section('title', 'Области')

@section('header')
    <x-page-header title="Области" pretitle="Организация">
        @can('create_regions')
            <x-slot:actions>
                <a href="{{ route('regions.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse ($regions as $region)
                    <tr>
                        <td class="text-secondary">{{ $region->id }}</td>
                        <td>{{ $region->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_regions')
                                    <a href="{{ route('regions.edit', $region) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_regions')
                                    <x-delete-button :action="route('regions.destroy', $region)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-empty-state text="Добавьте первую область." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($regions->hasPages())
            <div class="card-footer">{{ $regions->links() }}</div>
        @endif
    </div>
@endsection
