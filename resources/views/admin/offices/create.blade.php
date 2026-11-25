@extends('admin.layouts.app')

@section('title', 'Создать кабинет')

@section('header')
    <x-page-header title="Создать кабинет" :breadcrumbs="['Кабинеты' => route('offices.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('offices.store') }}">
            @csrf
            <div class="card-body">
                @include('admin.offices._form', ['office' => null])
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
