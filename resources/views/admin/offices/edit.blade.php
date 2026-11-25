@extends('admin.layouts.app')

@section('title', 'Изменить кабинет')

@section('header')
    <x-page-header title="Изменить кабинет" :breadcrumbs="['Кабинеты' => route('offices.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('offices.update', $office) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.offices._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
