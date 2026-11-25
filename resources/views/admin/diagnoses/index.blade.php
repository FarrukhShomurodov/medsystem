@extends('admin.layouts.app')

@section('title', 'Диагнозы')

@section('header')
    <x-page-header title="Диагнозы" pretitle="Справочники">
        @can('create_diagnoses')
            <x-slot:actions>
                <a href="{{ route('diagnoses.import.show') }}" class="btn"><i class="bx bx-import" aria-hidden="true"></i>Импорт</a>
                <a href="{{ route('diagnoses.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Код</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($diagnoses as $diagnosis)
                    <tr>
                        <td class="text-secondary">{{ $diagnosis->id }}</td>
                        <td>{{ $diagnosis->name }}</td>
                        <td class="text-nowrap">{{ $diagnosis->code }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_diagnoses')
                                    <a href="{{ route('diagnoses.edit', $diagnosis) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_diagnoses')
                                    <x-delete-button :action="route('diagnoses.destroy', $diagnosis)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state text="Добавьте диагноз или импортируйте справочник МКБ-10." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($diagnoses->hasPages())
            <div class="card-footer">{{ $diagnoses->links() }}</div>
        @endif
    </div>
@endsection
