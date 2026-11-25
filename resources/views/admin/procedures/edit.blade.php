@extends('admin.layouts.app')

@section('title', 'Изменить процедуру')

@section('header')
    <x-page-header title="Изменить процедуру" :breadcrumbs="['Процедуры' => route('procedures.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('procedures.update', $procedure) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.procedures._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
