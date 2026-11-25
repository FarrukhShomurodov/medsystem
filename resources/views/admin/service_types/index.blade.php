@extends('admin.layouts.app')

@section('title', 'Типы услуг')

@section('header')
    <x-page-header title="Типы услуг" pretitle="Услуги">
        @can('create_service_types')
            <x-slot:actions>
                <a href="{{ route('service.types.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse ($types as $type)
                    <tr>
                        <td class="text-secondary">{{ $type->id }}</td>
                        <td>{{ $type->name }}</td>
                        <td>
                            {{-- The seeded types (1-21) are the ones the code relies on: they are not edited or deleted. --}}
                            @unless ($type->isSystem())
                                <div class="table-actions">
                                    @can('edit_service_types')
                                        <a href="{{ route('service.types.edit', $type) }}" class="btn btn-sm">Изменить</a>
                                    @endcan
                                    @can('delete_service_types')
                                        <x-delete-button :action="route('service.types.destroy', $type)" />
                                    @endcan
                                </div>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-empty-state text="Добавьте первый тип услуги." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($types->hasPages())
            <div class="card-footer">{{ $types->links() }}</div>
        @endif
    </div>
@endsection
