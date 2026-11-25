@extends('admin.layouts.app')

@section('title', 'Изменить диагноз')

@section('header')
    <x-page-header title="Изменить диагноз" :breadcrumbs="['Диагнозы' => route('diagnoses.index')]" />
@endsection

@section('content')
    @php
        $linkedSurveys = array_map('intval', (array) old('surveys', $diagnosis->surveys->pluck('id')->all()));
    @endphp

    <div class="card">
        <form method="POST" action="{{ route('diagnoses.update', $diagnosis) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.diagnoses._form')

                <div class="mt-3">
                    <label class="form-label" for="surveys">Мед. документы</label>
                    <select id="surveys" name="surveys[]" class="form-select select2 @if ($errors->has('surveys') || $errors->has('surveys.*')) is-invalid @endif"
                            data-placeholder="Выберите документы" multiple>
                        @foreach ($surveys as $survey)
                            <option value="{{ $survey->id }}" @selected(in_array($survey->id, $linkedSurveys, true))>{{ $survey->title }}</option>
                        @endforeach
                    </select>
                    @error('surveys')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('surveys.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
