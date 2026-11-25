@extends('admin.layouts.app')

@section('title', 'Создать диагноз')

@section('header')
    <x-page-header title="Создать диагноз" :breadcrumbs="['Диагнозы' => route('diagnoses.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('diagnoses.store') }}">
            @csrf
            <div class="card-body">
                @include('admin.diagnoses._form', ['diagnosis' => null])
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
