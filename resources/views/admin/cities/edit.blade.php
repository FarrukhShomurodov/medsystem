@extends('admin.layouts.app')

@section('title', 'Изменить город')

@section('header')
    <x-page-header title="Изменить город" :breadcrumbs="['Города' => route('cities.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('cities.update', $city) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.cities._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
