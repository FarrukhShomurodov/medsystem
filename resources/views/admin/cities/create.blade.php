@extends('admin.layouts.app')

@section('title', 'Создать город')

@section('header')
    <x-page-header title="Создать город" :breadcrumbs="['Города' => route('cities.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('cities.store') }}">
            @csrf
            <div class="card-body">
                @include('admin.cities._form', ['city' => null])
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
