@extends('admin.layouts.app')

@section('title', 'Хронические болезни')

@section('header')
    <x-page-header title="Хронические болезни" pretitle="Справочники">
        @can('create_diseases')
            <x-slot:actions>
                <a href="{{ route('diseases.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                @forelse ($diseases as $disease)
                    <tr>
                        <td class="text-secondary">{{ $disease->id }}</td>
                        <td>{{ $disease->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_diseases')
                                    <a href="{{ route('diseases.edit', $disease) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_diseases')
                                    <x-delete-button :action="route('diseases.destroy', $disease)" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-empty-state text="Добавьте первую хроническую болезнь." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($diseases->hasPages())
            <div class="card-footer">{{ $diseases->links() }}</div>
        @endif
    </div>
@endsection
