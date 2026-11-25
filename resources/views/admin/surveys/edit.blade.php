@extends('admin.layouts.app')

@section('title', 'Изменить мед. документ')

@section('header')
    <x-page-header title="Изменить мед. документ" :breadcrumbs="['Мед. документы' => route('surveys.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('surveys.update', $survey) }}">
            @csrf
            @method('PUT')
            @include('admin.surveys._form')
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    @vite('resources/js/pages/survey-editor.js')
@endsection
