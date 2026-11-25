@extends('admin.layouts.app')

@section('title', 'Мед. документы')

@section('header')
    <x-page-header title="Мед. документы" pretitle="Справочники">
        @can('create_surveys')
            <x-slot:actions>
                <a href="{{ route('surveys.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Тип</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($surveys as $survey)
                    <tr>
                        <td class="text-secondary">{{ $survey->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if ($survey->color)
                                    <span class="survey-swatch" title="Цвет фона {{ $survey->color }}">@include('admin.surveys._swatch', ['color' => $survey->color])</span>
                                @endif
                                @can('view_surveys')
                                    <a href="{{ route('surveys.show', $survey) }}">{{ $survey->title }}</a>
                                @else
                                    {{ $survey->title }}
                                @endcan
                            </div>
                        </td>
                        <td>{{ $survey->type->name }}</td>
                        <td>
                            <div class="table-actions">
                                @can('edit_surveys')
                                    <a href="{{ route('surveys.edit', $survey) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                @can('delete_surveys')
                                    <x-delete-button :action="route('surveys.destroy', $survey)" confirm="Удалить мед. документ со всеми вопросами? Действие нельзя отменить." />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-empty-state text="Добавьте первый мед. документ." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($surveys->hasPages())
            <div class="card-footer">{{ $surveys->links() }}</div>
        @endif
    </div>
@endsection
