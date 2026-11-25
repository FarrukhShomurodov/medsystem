@extends('admin.layouts.app')

@section('title', 'Создать отделение')

@section('header')
    <x-page-header title="Создать отделение" :breadcrumbs="['Отделения' => route('departments.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('departments.store') }}">
            @csrf
            <div class="card-body">
                @include('admin.departments._form', ['department' => null])
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
