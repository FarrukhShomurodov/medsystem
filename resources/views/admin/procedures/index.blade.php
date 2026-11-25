@extends('admin.layouts.app')

@section('title', 'Процедуры')

@section('header')
    <x-page-header title="Процедуры" pretitle="Услуги">
        @can('create_service_procedures')
            <x-slot:actions>
                <a href="{{ route('procedures.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th class="text-end">Стоимость</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($procedures as $procedure)
                    <tr>
                        <td class="text-secondary">{{ $procedure->id }}</td>
                        <td>{{ $procedure->name }}</td>
                        <td class="text-end text-nowrap">{{ \App\Services\Billing\Amount::fromDecimal($procedure->price)->format() }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_service_procedures')
                                    <a href="{{ route('procedures.edit', $procedure) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_service_procedures')
                                    <x-delete-button :action="route('procedures.destroy', $procedure)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state text="Добавьте первую процедуру." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($procedures->hasPages())
            <div class="card-footer">{{ $procedures->links() }}</div>
        @endif
    </div>
@endsection
