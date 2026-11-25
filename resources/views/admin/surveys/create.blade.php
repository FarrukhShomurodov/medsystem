@extends('admin.layouts.app')

@section('title', 'Создать мед. документ')

@section('header')
    <x-page-header title="Создать мед. документ" :breadcrumbs="['Мед. документы' => route('surveys.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('surveys.store') }}">
            @csrf
            @include('admin.surveys._form', ['survey' => null])
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    @vite('resources/js/pages/survey-editor.js')
@endsection
