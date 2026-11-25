@extends('admin.layouts.app')

@php
    $patientName = $map->patient->full_name;
@endphp

@section('title', 'Опросники')

@section('header')
    <x-page-header title="Опросники" :breadcrumbs="[
        'Пациенты' => auth()->user()?->can('list_patients') ? route('patients.index') : null,
        $patientName => auth()->user()?->can('view_patients') ? route('patients.show', $map->patient_id) : null,
    ]" />
@endsection

@section('content')
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Заполненные опросники</h2>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                        <tr>
                            <th class="w-1">ID</th>
                            <th>Опросник</th>
                            <th class="w-1"><span class="visually-hidden">Действия</span></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($map->surveyResponses as $response)
                            <tr>
                                <td class="text-secondary">{{ $response->id }}</td>
                                <td>{{ $response->survey->title }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm view-response"
                                            data-url="{{ route('api.survey-responses.show', $response->id) }}"
                                            data-bs-toggle="modal" data-bs-target="#responseModal">
                                        Просмотр
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-empty-state title="Опросники ещё не заполнялись" icon="bx-list-check" /></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <form action="{{ route('response.survey.store') }}" method="POST" id="survey-form">
                    @csrf
                    <input type="hidden" name="examination_map_id" value="{{ $map->id }}">

                    <div class="card-header">
                        <h2 class="card-title">Заполнить опросник</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required" for="survey-select">Опросник</label>
                            <select id="survey-select" name="survey_id" required
                                    class="form-select select2 @error('survey_id') is-invalid @enderror"
                                    data-questions-url="{{ route('api.surveys.questions', ['survey' => '__survey__']) }}">
                                <option value="" disabled selected>Выберите опросник</option>
                                @foreach ($surveys as $survey)
                                    <option value="{{ $survey->id }}" @selected($survey->id == old('survey_id'))>{{ $survey->title }}</option>
                                @endforeach
                            </select>
                            @error('survey_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div id="questions-container"
                             data-choice-type="{{ \App\Enums\QuestionType::Choice->value }}"
                             data-boolean-type="{{ \App\Enums\QuestionType::Boolean->value }}"></div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save" aria-hidden="true"></i>Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('admin.maps.partials.response-modal', ['title' => 'Ответы опросника'])
@endsection

@section('scripts')
    @vite('resources/js/pages/map-surveys.js')
@endsection
