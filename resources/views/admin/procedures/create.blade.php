@extends('admin.layouts.app')

@section('title', 'Создать процедуру')

@section('header')
    <x-page-header title="Создать процедуру" :breadcrumbs="['Процедуры' => route('procedures.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('procedures.store') }}">
            @csrf
            <div class="card-body">
                @include('admin.procedures._form', ['procedure' => null])
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
