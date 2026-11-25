@extends('admin.layouts.app')

@section('title', 'Методы')

@section('header')
    <x-page-header title="Методы" pretitle="Услуги">
        @can('create_service_methods')
            <x-slot:actions>
                <a href="{{ route('service.methods.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse ($methods as $method)
                    <tr>
                        <td class="text-secondary">{{ $method->id }}</td>
                        <td>{{ $method->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_service_methods')
                                    <a href="{{ route('service.methods.edit', $method) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_service_methods')
                                    <x-delete-button :action="route('service.methods.destroy', $method)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-empty-state text="Добавьте первый метод." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($methods->hasPages())
            <div class="card-footer">{{ $methods->links() }}</div>
        @endif
    </div>
@endsection
